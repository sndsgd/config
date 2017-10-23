<?php

namespace sndsgd\config;

/**
 * An interface that allows for object definitions to be used in configurations
 *
 * The results of `toArray()` are used by `Config::createObject()` to create
 * an object when the value is requested
 */
interface ObjectInterface
{
    /**
     * Retrieve the callable that is used to create an instance of the object
     *
     * @return callable
     */
    public function getCreateCallable(): callable;

    /**
     * Retrieve the arguments that are used to create an instance of the object
     *
     * @return array
     */
    public function getCreateArguments(): array;
}
