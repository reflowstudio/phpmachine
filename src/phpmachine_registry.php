<?php

namespace phpmachine_registry;

use ArrayObject,
    RuntimeException;

function get($index) {
    return PHPMachine_Registry::getInstance()->get($index);
}

function set($index, $value) {
    PHPMachine_Registry::getInstance()->set($index, $value);
}

function exists($index) {
    return PHPMachine_Registry::getInstance()->isRegistered($index);
}

function remove($index) {
    PHPMachine_Registry::getInstance()->remove($index);
}

function clear() {
    return PHPMachine_Registry::getInstance()->clear();
}

function all() {
    return PHPMachine_Registry::getInstance()->getArrayCopy();
}

class PHPMachine_Registry extends ArrayObject
{
    /**
     * Class name of the singleton registry object.
     * @var string
     */
    private static $_registryClassName = 'phpmachine_registry\\PHPMachine_Registry';

    /**
     * Registry object provides storage for shared objects.
     * @var PHPMachine_Registry
     */
    private static $_registry = null;

    /**
     * Retrieves the default registry instance.
     *
     * @return PHPMachine_Registry
     */
    public static function getInstance()
    {
        if (self::$_registry === null) {
            self::init();
        }

        return self::$_registry;
    }

    /**
     * Set the default registry instance to a specified instance.
     *
     * @param PHPMachine_Registry $registry An object instance of type PHPMachine_Registry,
     * or a subclass.
     * @return void
     * @throws RuntimeException if registry is already initialized.
     */
    public static function setInstance(PHPMachine_Registry $registry)
    {
        if (self::$_registry !== null) {
            throw new RuntimeException('Registry is already initialized');
        }

        self::$_registryClassName = get_class($registry);
        self::$_registry = $registry;
    }

    /**
     * Initialize the default registry instance.
     *
     * @return void
     */
    protected static function init()
    {
        self::setInstance(new self::$_registryClassName());
    }

    /**
     * getter method, basically same as offsetGet().
     *
     * This method can be called from an object of type phpmachine_registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index - get the value associated with $index
     * @return mixed
     * @throws RuntimeException if no entry is registerd for $index.
     */
    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            throw new RuntimeException("No entry is registered for key '$index'");
        }

        return $instance->offsetGet($index);
    }

    /**
     * setter method, basically same as offsetSet().
     *
     * This method can be called from an object of type PHPMachine_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index The location in the ArrayObject in which to store
     *   the value.
     * @param mixed $value The object to store in the ArrayObject.
     * @return void
     */
    public static function set($index, $value)
    {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    /**
     * setter method, basically same as offsetUnset().
     *
     * This method can be called from an object of type PHPMachine_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index The location in the ArrayObject in which to store
     *   the value.
     * @return void
     */
    public static function remove($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            throw new RuntimeException("No entry is registered for key '$index'");
        }

        $instance->offsetUnset($index);
    }

    /**
     * setter method, basically same as exchangeArray(array()).
     *
     * This method can be called from an object of type PHPMachine_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @return array
     */
    public static function clear()
    {
        $instance = self::getInstance();
        return $instance->exchangeArray(array());
    }

    /**
     * Returns TRUE if the $index is a named value in the registry,
     * or FALSE if $index was not found in the registry.
     *
     * @param  string $index
     * @return boolean
     */
    public static function isRegistered($index)
    {
        if (self::$_registry === null) {
            return false;
        }
        return self::$_registry->offsetExists($index);
    }

    /**
     * Constructs a parent ArrayObject with default
     * ARRAY_AS_PROPS to allow acces as an object
     *
     * @param array $array data array
     * @param integer $flags ArrayObject flags
     */
    public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS)
    {
        parent::__construct($array, $flags);
    }
}
