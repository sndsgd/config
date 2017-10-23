<?php

/**
 * An example of an object that can be used in a config
 */
class DatabaseConfig
{
    public static function createObjectConstructor(array $values)
    {
        return new \sndsgd\config\ObjectConstructor(__CLASS__, [
            $values["host"] ?? "",
            $values["port"] ?? "",
            $values["db"] ?? "",
            $values["user"] ?? "",
            $values["password"] ?? "",
        ]);
    }

    protected $host;
    protected $port;
    protected $db;
    protected $user;
    protected $password;

    public function __construct(
        string $host,
        int $port,
        string $db,
        string $user,
        string $password
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->db = $db;
        $this->user = $user;
        $this->password = $password;
    }
}
