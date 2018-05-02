<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 16.09.16
 * Time: 11:32
 */

namespace KotaShade\StateMachine\Entity;

use Doctrine\Common\Collections\ArrayCollection;

interface TransitionAInterface
{
    /**
     * @return mixed
     */
    public function getId();
    /**
     * @return mixed
     */
    public function getSrc();

    /**
     * @return mixed
     */
    public function getAction();

    /**
     * @return string|null
     */
    public function getCondition();

    /**
     * @return ArrayCollection
     */
    public function getTransitionsB();
} 