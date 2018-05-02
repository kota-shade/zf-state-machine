<?php
return [
    'doctrine' => [
        'driver' => [
            'KotaShadeStateMachineEntity' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/Entity',
            ],
            'orm_default' => array(
                'drivers' => array(
                    'KotaShade\StateMachine\Entity' => 'KotaShadeStateMachineEntity'
                ),
            ),
        ]
    ],
    KotaShade\StateMachine\Functor\FunctorProviderInterface::CONFIG_KEY => [

    ],

];
