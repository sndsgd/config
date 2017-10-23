<?php

namespace sndsgd\config;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $searchDir;
    protected $resultDir;

    public function setup()
    {
        $this->searchDir = realpath(__DIR__."/../resources");
        $this->resultDir = realpath(__DIR__."/../resources/results");
    }

    public function testWrite()
    {
        $generator = new Generator($this->searchDir);
        foreach (Generator::VALID_OUTPUT_TYPES as $type) {
            $generator->write($type, "{$this->resultDir}/output.$type");
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage unknown output type 'nope'
     */
    public function testWriteTypeException()
    {
        $generator = new Generator($this->searchDir);
        $generator->write("nope", "{$this->resultDir}/output.nope");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegexp /^failed to write config;/
     */
    public function testWriteInvalidFileException()
    {
        $generator = new Generator($this->searchDir);
        $generator->write("php", $this->resultDir);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegexo /^failed to read file;/
     */
    public function testParseYamlFileException()
    {
        $generator = new Generator($this->searchDir);

        $reflection = new \ReflectionClass($generator);
        $method = $reflection->getMethod("parseYamlFile");
        $method->setAccessible(true);

        $method->invoke($generator, \sndsgd\Fs::file(__DIR__));
    }
}
