<?php

namespace sndsgd\config\exception;

class InvalidValueExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException sndsgd\config\exception\InvalidValueException
     * @expectedExceptionMessage invalid config value for 'qwe'; asd
     */
    public function testConstructor()
    {
        throw new InvalidValueException("qwe", "asd");
    }
}
