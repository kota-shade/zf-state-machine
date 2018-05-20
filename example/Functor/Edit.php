<?php
namespace Test\StateMachine\TicketCar\Functor;

use Test\Entity\PassTicketCar;
use KotaShade\StateMachine\Functor\FunctorInterface;
/**
 * Class Remove
 * @package Test\StateMachine\TicketCar\Functor
 */
class Edit implements FunctorInterface
{
    /**
     * @param PassTicketCar $objE
     * @param array $data
     * @param string $action
     * @param string $functorType
     * @return mixed
     */
    public function __invoke($objE, $action, $functorType, array &$data = [])
    {
        //do something
        //you can call the other state-machine
        return null;
    }

}