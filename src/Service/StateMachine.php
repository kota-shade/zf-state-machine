<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 02.05.18
 * Time: 20:52
 */
namespace KotaShade\StateMachine\Service;

use KotaShade\StateMachine\Exception as ExceptionNS;
use KotaShade\StateMachine\Entity\TransitionAInterface;
use KotaShade\StateMachine\Entity\TransitionBInterface;
use Zend\Validator\ValidatorInterface;
use Zend\Validator\ValidatorPluginManager;
use KotaShade\StateMachine\Functor as FunctorNS;
use Doctrine\ORM\EntityManager as EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

abstract class StateMachine
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var ValidatorPluginManager
     */
    protected $validatorPM;

    /**
     * @var FunctorNS\FunctorPluginManager
     */
    protected $functorPM;

    /**
     * get repository of transition A-table
     * @return \Doctrine\ORM\EntityRepository
     */
    abstract protected function getTransitionARepository();

    /**
     * get action entity by action identifier
     * @param string $action
     * @return mixed
     */
    abstract protected function getActionEntity($action);

    public function __construct(
        EntityManager $em,
        ValidatorPluginManager $validatorPM,
        FunctorNS\FunctorPluginManager $functorPM
    ) {
        $this->em = $em;
        $this->validatorPM = $validatorPM;
        $this->functorPM = $functorPM;
    }

    /**
     * do state-machine action and change $objE entity state
     * @param object $objE
     * @param string $action
     * @param array $data  extra data
     * @return array
     * @throws ExceptionNS\StateMachineException
     */
    public function doAction($objE, $action, array $data = [])
    {
        if (($transitionE = $this->getActionTransition($objE, $action)) == null) {
            throw new ExceptionNS\ActionNotExistsForState($objE, $action);
        }
        $conditionName = $transitionE->getCondition();
        if ($this->checkActionCondition($errors, $conditionName, $objE, $data=[]) == false) {
            throw new ExceptionNS\ActionNotAllowed($objE, $action, $errors);
        }

        if (($transitionBE = $this->getTransitionB($transitionE, $objE, $data)) == null) {
            //empty action without changing state of $objE entity (for example 'view' action)
            return $data;
        }

        $this->doFunctor($transitionBE->getPreFunctor(), $objE, $data);
        $this->setObjectState($objE, $transitionBE->getDst());
        $this->doFunctor($transitionBE->getPostFunctor(), $objE, $data);

        return $data;
    }

    /**
     * check if $objE has action in the current state (check all validators for this action)
     * @param object $objE
     * @param string $action
     * @param array $data
     * @return bool
     */
    public function hasAction($objE, $action, $data=[])
    {
        if (($transitionE = $this->getActionTransition($objE, $action)) == null) {
            return false;
        }
        $conditionName = $transitionE->getCondition();
        if ($conditionName == null) {
            return true;
        }
        if ($this->checkActionCondition($errors, $conditionName, $objE, $data) == false) {
            return false;
        }
        return true;
    }
    //======================
    /**
     * возвращает список доступных действий для заданного состояния
     * @param $state
     * @return array
     * FIXME нет способа получить по имени состояния ентити состояния, т.к. нет репозитория состояний.
     */
    public function getActionsForState($state)
    {
        $repo = $this->getTransitionARepository();
        /** @var ArrayCollection $res */
        $transitionList = $repo->findBy([
            'src' => $stateE->getId()
        ]);

        $ret = [];

        /** @var TransitionAInterface $transitionE */
        foreach ($transitionList as $transitionE) {
            $actionE = $transitionE->getAction();
            $ret[$actionE->getId()] = $actionE;
        }
        return $ret;
    }

    /**
     * возвращает список доступных действий для данной ентити (проверяются условия доступности действия)
     * @param object $objE
     * @param array $data
     * @return array
     */
    public function getActions($objE, $data=[])
    {
        $stateE = $this->getObjectState($objE);
        $repo = $this->getTransitionARepository();
        /** @var ArrayCollection $res */
        $transitionList = $repo->findBy([
            'src' => $stateE->getId()
        ]);

        /** @var TransitionAInterface $transitionE */
        foreach($transitionList as $transitionE) {
            $condition = $transitionE->getCondition();
            if ($this->checkActionCondition( $messages, $condition, $objE, $data)) {
                $actionE = $transitionE->getAction();
                $ret[$actionE->getId()] = $actionE;
            }
        }
        return $ret;
    }
    //=============================

    /**
     * get transition by action name if the transition exists for the object in current state without checking conditions
     * @param $objE
     * @param $action
     * @return TransitionAInterface
     */
    protected function getActionTransition($objE, $action)
    {
        $stateE = $this->getObjectState($objE);
        /** @var object $actionE */
        if(($actionE = $this->getActionEntity($action)) == null) {
            throw new ExceptionNS\ActionNotExists($action);
        }

        $transitionE = $this->getTransitionAForState($stateE, $actionE);
        return $transitionE;
    }

    /**
     * @param $stateE
     * @param $actionE
     * @return null|TransitionAInterface
     */
    protected function getTransitionAForState($stateE, $actionE)
    {
        $repo = $this->getTransitionARepository();
        /** @var TransitionAInterface $res */
        $res = $repo->findOneBy([
            'src' => $stateE->getId(),
            'action' => $actionE->getId()
        ]);
        return $res;
    }

    /**
     * @param TransitionAInterface $transitionE
     * @return array|\Traversable
     */
    protected function getTransitionBList(TransitionAInterface $transitionE)
    {
        /** @var ArrayCollection $ret */
        $ret = $transitionE->getTransitionsB();
        return $ret->toArray();
    }

    /**
     * @param TransitionAInterface $transitionE
     * @param $objE
     * @param $data
     * @return TransitionBInterface|null
     */
    protected function getTransitionB(TransitionAInterface $transitionE, $objE, $data)
    {
        $list = $this->getTransitionBList($transitionE);
        if (count($list) == 0) {
            return null;
        }

        usort($list, array($this, 'cmpWeight'));
        /** @var TransitionBInterface $transitionBE */
        foreach($list as $transitionBE) {
            $conditionName = $transitionBE->getCondition();
            if($this->checkActionCondition($errors, $conditionName, $objE, $data)) {
                return $transitionBE;
            }
        }

        throw new ExceptionNS\InvalidTransition($transitionE);
    }

    /**
     * DESC sort order for array
     * @param TransitionBInterface $trA
     * @param TransitionBInterface $trB
     * @return int
     */
    protected function cmpWeight(TransitionBInterface $trA, TransitionBInterface $trB)
    {
        if ($trA->getWeight() == null) {
            return 1;
        }
        elseif($trB->getWeight() == null) {
            return -1;
        }
        else {
            return ($trA->getWeight() < $trB->getWeight()) ? 1: -1;
        }
    }

    /**
     * validate, get validator by $conditionName
     * @param $validatorMessages - validation error messages
     * @param string $conditionName - validator name
     * @param object $objE - entity with state
     * @param array $data external data
     * @return bool
     */
    protected function checkActionCondition(&$validatorMessages, $conditionName, $objE, $data=[])
    {
        if ($conditionName == '') {
            return true;
        }

        /** @var ValidatorInterface $validator */
        $validator = $this->getCondition($conditionName);
        if (($validator->isValid($objE, $data)) == false) {
            $validatorMessages = $validator->getMessages();
            return false;
        }

        return true;
    }

    /**
     * @param $conditionName
     * @return ValidatorInterface
     */
    protected function getCondition($conditionName)
    {
        /** @var ValidatorInterface $validator */
        $validator = $this->validatorPM->get($conditionName);
        return $validator;
    }

    /**
     * @param string $functorName
     * @param object $objE
     * @param array &$data
     */
    protected function doFunctor($functorName, $objE, array &$data)
    {
        if ($functorName == '') {
            return;
        }
        if (($functor = $this->getFunctor($functorName)) == null) {
            return;
        }
        $functor($objE, $data);
    }

    /**
     * @param string $functorName
     * @return FunctorNS\FunctorInterface
     */
    protected function getFunctor($functorName)
    {
        /** @var FunctorNS\FunctorInterface $functor */
        $functor = $this->functorPM->get($functorName);
        return $functor;
    }

    /**
     * @param object $objE
     * @return mixed
     */
    protected function getObjectState($objE)
    {
        return $objE->getState();
    }

    /**
     * @param $objE
     * @param $stateE
     * @return $this
     */
    protected function setObjectState($objE, $stateE)
    {
        $objE->setState($stateE);
        return $this;
    }
}