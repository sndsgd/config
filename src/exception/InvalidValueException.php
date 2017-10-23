<?php

namespace sndsgd\config\exception;

class InvalidValueException extends \Exception
{
    public function __construct(
        string $key,
        string $message,
        int $code = 0,
        \Exception $previous = null
    ) {
        $message = "invalid config value for '$key'; $message";
        parent::__construct($message, $code, $previous);
    }
}
