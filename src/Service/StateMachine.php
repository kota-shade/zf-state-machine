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
use Doctrine\Common\Collections\Criteria;

/**
 * Class StateMachine
 * @package KotaShade\StateMachine\Service
 */
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

    /*
     * \Doctrine\ORM\Mapping\ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $stateRepository = null;

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
        $loopGuard = $this->getLoopGuard($objE);

        if (($transitionE = $this->getActionTransition($objE, $action)) == null) {
            throw new ExceptionNS\ActionNotExistsForState($objE, $action);
        }
        $conditionName = $transitionE->getCondition();
        if ($this->checkActionCondition($errors, $conditionName, $objE, $data) == false) {
            throw new ExceptionNS\ActionNotAllowed($objE, $action, $errors);
        }

        if (($transitionBE = $this->getTransitionB($transitionE, $objE, $data)) == null) {
            //empty action without changing state of $objE entity (for example 'view' action)
            return $data;
        }

        $this->doFunctor($transitionBE->getPreFunctor(), $objE, $action, FunctorNS\FunctorInterface::PREFUNCTOR, $data);
        $this->setObjectState($objE, $transitionBE->getDst());
        $this->doFunctor($transitionBE->getPostFunctor(), $objE, $action, FunctorNS\FunctorInterface::POSTFUNCTOR, $data);

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

    /**
     * get array of actions which exists for this state (without condition checking)
     * @param $state
     * @return array
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \ReflectionException
     */
    public function getActionsForState($state)
    {
        $stateE = $this->getStateByIdent($state);
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
     * get the existent actions for entity (conditions are checked)
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

        $ret = [];
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
     * @return TransitionAInterface|null
     */
    protected function getActionTransition($objE, $action)
    {
        $stateE = $this->getObjectState($objE);
        /** @var object $actionE */
        if(($actionE = $this->getActionEntity($action)) == null) {
            return null;
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
     * @return ArrayCollection
     */
    protected function getTransitionBList(TransitionAInterface $transitionE)
    {
        $sort = new Criteria(null, ['weight' => Criteria::DESC]);
        /** @var ArrayCollection $ret */
        $ret = $transitionE->getTransitionsB()->matching($sort);
        return $ret;
    }

    /**
     * @param TransitionAInterface $transitionE
     * @param $objE
     * @param $data
     * @return TransitionBInterface|null
     */
    protected function getTransitionB(TransitionAInterface $transitionE, $objE, $data)
    {
        /** @var ArrayCollection $list */
        $list = $this->getTransitionBList($transitionE);
        if ($list->count() == 0) {
            return null;
        }
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
     * @param string $action
     * @param string $functorType
     * @param array &$data
     */
    protected function doFunctor($functorName, $objE, $action, $functorType, array &$data)
    {
        if ($functorName == '') {
            return;
        }
        if (($functor = $this->getFunctor($functorName)) == null) {
            return;
        }
        $functor($objE, $action, $functorType, $data);
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

    /**
     * @return \Doctrine\ORM\Mapping\ClassMetadataFactory
     */
    protected function getMetadataFactory() {
        if ($this->metadataFactory == null) {
            $this->metadataFactory = $this->em->getMetadataFactory();
        }
        return $this->metadataFactory;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \ReflectionException
     */
    protected function getStateRepository()
    {
        if ($this->stateRepository === null) {
            $repo = $this->getTransitionARepository();
            $entityName = $repo->getClassName();
            $mdf = $this->getMetadataFactory();
            $metadata = $mdf->getMetadataFor($entityName);
            $stateClassName = $metadata->getAssociationTargetClass('src');
            $this->stateRepository = $this->em->getRepository($stateClassName);
        }

        return $this->stateRepository;
    }

    /**
     * @param $state
     * @return null|object
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \ReflectionException
     */
    protected function getStateByIdent($state)
    {
        $stateRepo = $this->getStateRepository();
        $stateE = $stateRepo->find($state);
        return $stateE;
    }

    /**
     * Return guard to privent loop in state machine. The guard check loop in it's constructor
     * and set pair obj+state into stack. Dectructor pops pair from stack
     * @param $objE
     * @return CallStack
     */
    protected function getLoopGuard($objE)
    {
        $loopGuard = new CallStack($objE, $this->getObjectState($objE));
        return $loopGuard;
    }

}