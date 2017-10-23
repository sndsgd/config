<?php

namespace sndsgd\config\exception;

class UndefinedKeyException extends \Exception
{
    public function __construct(
        string $key,
        int $code = 0,
        \Exception $previous = null
    ) {
        $message = "unknown config key; '$key' is not defined";
        parent::__construct($message, $code, $previous);
    }
}
