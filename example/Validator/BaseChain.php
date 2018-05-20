<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 18.10.16
 * Time: 10:47
 */
namespace Test\StateMachine\Validator;

use Zend\Validator\ValidatorChain;
use Zend\Validator\ValidatorPluginManager;
use Zend\Validator\ValidatorPluginManagerAwareInterface;

class BaseChain extends ValidatorChain implements ValidatorPluginManagerAwareInterface
{
    protected $validatorSpec = [];
    private $initStatus = false;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ленивая инициализация
     */
    public function init()
    {
        if ($this->initStatus == false) {
            foreach ($this->validatorSpec as $name => $options) {
                $this->attachByName($name, $options, true);
            }
            $this->initStatus = true;
        }
    }

    /**
     * @param mixed $value
     * @param null $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->init();
        return parent::isValid($value, $context);
    }

    /**
     * Set validator plugin manager
     *
     * @param ValidatorPluginManager $pluginManager
     */
    public function setValidatorPluginManager(ValidatorPluginManager $pluginManager)
    {
        $this->setPluginManager($pluginManager);
    }

    /**
     * Get validator plugin manager
     *
     * @return ValidatorPluginManager
     */
    public function getValidatorPluginManager()
    {
        return $this->getPluginManager();
    }
} 