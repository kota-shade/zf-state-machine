<?php
namespace Test\StateMachine\TicketCar\Validator;

// use Test\Entity\Rbacpermission;
use Test\StateMachine\Validator as RootValidatorNS;
use Test\StateMachine\TicketCar\Validator as TPValidatorNS;

/**
 * Class EditChain
 * @package Test\StateMachine\TicketCar\Validator
 */
class EditChain extends RootValidatorNS\BaseChain
{
    protected $validatorSpec = [
        RootValidatorNS\NotDel::class => [],
//        RootValidatorNS\IsAuthenticated::class => [],
//        RootValidatorNS\IsGranted::class => [
//            'grants' => [Rbacpermission::REGISTRY_EDIT]
//        ]
    ];
}
