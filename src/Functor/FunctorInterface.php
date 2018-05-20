<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 16.09.16
 * Time: 16:41
 */

namespace KotaShade\StateMachine\Functor;

/**
 * Interface FunctorInterface
 * @package KotaShade\StateMachine\Functor
 */
interface FunctorInterface
{
    const PREFUNCTOR = 'prefunctor';
    const POSTFUNCTOR = 'postfunctor';

    /**
     * @param object $objE
     * @param array $data
     * @param string $action
     * @param string $functorType
     * @return mixed
     */
    public function __invoke($objE, $action, $functorType, array &$data = []);
} 