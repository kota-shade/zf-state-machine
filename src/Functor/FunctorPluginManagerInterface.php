<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 16.09.16
 * Time: 16:32
 */
namespace KotaShade\StateMachine\Functor;

use Zend\ServiceManager\Exception as SMExceptionNS;

interface FunctorPluginManagerInterface
{

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param  string $name
     * @param  array  $options
     *
     * @return FunctorInterface
     *
     * @throws SMExceptionNS\ServiceNotFoundException
     * @throws SMExceptionNS\ServiceNotCreatedException
     * @throws SMExceptionNS\InvalidServiceException
     */
    public function get($name, array $options = null);


    /**
     * Determine if an instance exists.
     *
     * @param  string|array  $name  An array argument accepts exactly two values.
     *                              Example: array('canonicalName', 'requestName')
     * @return bool
     */
    public function has($name);

} 