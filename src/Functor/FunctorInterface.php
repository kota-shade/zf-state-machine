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
    /**
     * @param object $objE
     * @param array $data
     * @return mixed
     */
    public function __invoke($objE, array &$data = []);
} 