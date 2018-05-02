<?php
namespace KotaShade\StateMachine\Functor;

/**
 * Interface FunctorProviderInterface
 * @package KotaShade\StateMachine\Functor
 */
interface FunctorProviderInterface {

    const CONFIG_KEY = 'state_machine_functors';

    public function getFunctorProviderConfig();
} 