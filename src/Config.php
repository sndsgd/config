<?php

namespace sndsgd\config;

/**
 * A container for a collection of configuration values
 */
class Config
{
    /**
     * A map of raw configuration values
     *
     * @var array<string,mixed>
     */
    protected $rawValues;

    /**
     * A map of defintions that are used to create configuration values
     *
     * @var array<string,array>
     */
    protected $createDefinitions;

    public function __construct(array $rawValues, array $createDefinitions)
    {
        $this->rawValues = $rawValues;
        $this->createDefinitions = $createDefinitions;
    }

    /**
     * Retrieve a config value
     *
     * @param string $key The key of the config value to retrieve
     * @param mixed $default The value to return if the key does not exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (isset($this->rawValues[$key])) {
            return $this->rawValues[$key];
        }

        # if the key is in the definitions array we need to create an object
        # cache what we create in the values array for easy/early access
        if (isset($this->createDefinitions[$key])) {
            $this->rawValues[$key] = $this->createValue($key);
            unset($this->createDefinitions[$key]);
            return $this->rawValues[$key];
        }

        return $default;
    }

    /**
     * Create an object using values for a given key
     *
     * @param string $key The config key to create an object for
     * @return object
     */
    protected function createValue(string $key)
    {
        $definition = &$this->createDefinitions[$key];
        $callable = $definition[0];
        $arguments = $definition[1];
        return $callable(...$arguments);
    }
}
