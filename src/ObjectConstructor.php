<?php

namespace sndsgd\config;

/**
 * A config object for generating constructable object definitions
 *
 * Used to generate config values that when retrieved will result in an object
 * that is constructed using the config values as constructor arguments
 */
class ObjectConstructor implements ObjectInterface
{
    /**
     * Create an instance of an object using a generated definition
     *
     * @param string $class The name of the class to create
     * @param array $arguments The arguments to provide to the constructor
     * @return object An instance of the class
     */
    public static function create(string $class, array $arguments)
    {
        return new $class(...$arguments);
    }

    public function __construct(string $class, array $arguments)
    {
        $this->setClass($class);
        $this->setArguments($arguments);
    }

    private function setClass(string $class)
    {
        $this->class = $class;
    }

    private function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function getCreateCallable(): callable
    {
        return __CLASS__."::create";
    }

    public function getCreateArguments(): array
    {
        return [$this->class, $this->arguments];
    }
}
