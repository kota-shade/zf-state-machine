<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 13.05.18
 * Time: 19:46
 */

namespace KotaShade\StateMachine\Service;

/**
 * For control looping during the work of the state machine
 * Class StackItem
 * @package KotaShade\StateMachine\Service
 */
class StackItem {
    protected $objE;
    protected $state;

    public function __construct($objE, $state)
    {
        $this->objE = $objE;
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getObjE()
    {
        return $this->objE;
    }

    /**
     * @param mixed $objE
     */
    public function setObjE($objE)
    {
        $this->objE = $objE;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}
