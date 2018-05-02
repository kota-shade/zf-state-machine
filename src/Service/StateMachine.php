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

abstract class StateMachine
{
    /**
     * @var ValidatorPluginManager
     */
    protected $validatorPM;

    public function __construct(
        EntityManager $em,
        ValidatorPluginManager $validatorPM,
        FunctorNS\FunctorPluginManager $functorPM
    )
    {
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
        if ($this->checkActionCondition($errors, $conditionName, $objE, $data=[]) == false) {
            return false;
        }
        return true;
    }

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
     * @return TransitionAInterface
     */
    abstract protected function getTransitionAForState($stateE, $actionE);
    abstract protected function getObjectState($objE);
    abstract protected function setObjectState($objE, $stateE);
    abstract protected function getActionEntity($action);

    /**
     * @param TransitionAInterface $transitionE
     * @return array|\Traversable
     */
    abstract protected function getTransitionBList(TransitionAInterface $transitionE);

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
    public function cmpWeight(TransitionBInterface $trA, TransitionBInterface $trB)
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
     * выполняет валидацию по условию $condition
     * @param $validatorMessages
     * @param string $conditionName
     * @param $objE
     * @param array $data
     * @return bool
     */
    protected function checkActionCondition(&$validatorMessages, $conditionName, $objE, $data=[])
    {
        if ($conditionName == '') {
            return true;
        }

        /** @var ValidatorInterface $validator */
        $validator = $this->getCondition($conditionName);
        if (($validator->validate($objE, $data)) == false) {
            $validatorMessages = $validator->getMessages();
            return false;
        }

        return true;
    }

    protected function getCondition($conditionName)
    {
        if (($realName = Yii::getAlias('@'.$conditionName)) == false) {
            $realName = $conditionName;
        }

        $cond = Yii::createObject($realName);
        return $cond;
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
     * @return FunctorNS\FunctorInterface object
     */
    protected function getFunctor($functorName)
    {
        if (($realName = Yii::getAlias('@'.$functorName)) == false) {
            $realName = $functorName;
        }
        /** @var FunctorNS\FunctorInterface $functor */
        $functor = Yii::createObject($realName);
        return $functor;
    }
}