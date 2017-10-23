<?php

namespace sndsgd\config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideGet
     */
    public function testGet(
        array $rawValues,
        $key,
        $default,
        $expect
    )
    {
        $config = new Config($rawValues, []);
        $this->assertSame($expect, $config->get($key, $default));
    }

    public function provideGet(): array
    {
        $values = [
            "one" => 1,
            "two" => [1, 2, 3],
            "a.b.c" => "value",
        ];

        return [
            [$values, "one", null, 1],
            [$values, "two", null, [1, 2, 3]],
            [$values, "a.b.c", null, "value"],
            [$values, "nope", 123, 123],
        ];
    }

    public function testCreateValue()
    {
        $createDefinitions = [
            "foobar" => [
                "\\sndsgd\\config\\ObjectConstructor::create",
                [\FakeConfig::class, ["foo", "bar"]],
            ],
        ];

        $config = new Config([], $createDefinitions);

        $reflection = new \ReflectionClass($config);
        $rawValuesProperty = $reflection->getProperty("rawValues");
        $rawValuesProperty->setAccessible(true);
        $rawValues = $rawValuesProperty->getValue($config);

        $this->assertFalse(isset($rawValues["foobar"]));
        $result = $config->get("foobar");
        $this->assertInstanceOf(\FakeConfig::class, $result);

        $rawValues = $rawValuesProperty->getValue($config);
        $this->assertTrue(isset($rawValues["foobar"]));
        $this->assertSame($rawValues["foobar"], $result);
    }
}
