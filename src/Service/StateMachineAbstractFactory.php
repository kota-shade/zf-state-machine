<?php
namespace KotaShade\StateMachine\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Doctrine\ORM\EntityManager;
use KotaShade\StateMachine\Functor as FunctorNS;

class StateMachineAbstractFactory implements AbstractFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (is_subclass_of($requestedName, StateMachine::class)) {
            return true;
        }
        return false;
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine');
        $validatorPM = $container->get('ValidatorManager');
        /** @var FunctorNS\FunctorPluginManagerInterface $functorPM */
        $functorPM = $container->get(FunctorNS\FunctorPluginManagerInterface::class);

        return new $requestedName(
            $em,
            $validatorPM,
            $functorPM
        );
    }
}
