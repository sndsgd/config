<?php

class FakeConfig
{
    public static function createObjectConstructor(array $values)
    {
        $validKeys = ["foo", "bar"];
        $unknownKeys = array_diff($validKeys, array_keys($values));
        if ($unknownKeys) {
            throw new Exception(sprintf(
                "unknown keys [%s]",
                implode(",", $unknownKeys)
            ));
        }

        return new \sndsgd\config\ObjectConstructor(__CLASS__, [
            $values["foo"] ?? "",
            $values["bar"] ?? "",
        ]);
    }

    protected $foo;
    protected $bar;

    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function __toString()
    {
        return "{$this->foo} {$this->bar}";
    }
}
