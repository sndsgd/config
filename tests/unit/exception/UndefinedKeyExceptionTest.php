<?php

namespace sndsgd\config\exception;

class UndefinedKeyExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException sndsgd\config\exception\UndefinedKeyException
     * @expectedExceptionMessage unknown config key; 'test' is not defined
     */
    public function testConstructor()
    {
        throw new UndefinedKeyException("test");
    }
}
