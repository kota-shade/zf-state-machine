<?php
use KotaShade\StateMachine as StateMachineNS;

return [
    'aliases' => [
        StateMachineNS\Functor\FunctorPluginManager::class => StateMachineNS\Functor\FunctorPluginManagerInterface::class
    ],
    'factories' => [
        StateMachineNS\Functor\FunctorPluginManagerInterface::class => function ($container) {
            $pm = new StateMachineNS\Functor\FunctorPluginManager($container);
            return $pm;
        }
    ],
    'abstract_factories' => [
        StateMachineNS\Service\StateMachineAbstractFactory::class
    ],
    'invokables' => [
    ],
    'shared' => [],
];
