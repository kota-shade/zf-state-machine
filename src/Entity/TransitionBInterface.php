<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 16.09.16
 * Time: 11:32
 */

namespace KotaShade\StateMachine\Entity;

interface TransitionBInterface
{
    /**
     * @return string
     */
    public function getCondition();
    /**
     * @return mixed
     */
    public function getDst();

    /**
     * @return string
     */
    public function getPreFunctor();

    /**
     * @return mixed
     */
    public function getTransitionA();

    /**
     * @return int
     */
    public function getWeight();

    /**
     * @return string
     */
    public function getPostFunctor();
} 