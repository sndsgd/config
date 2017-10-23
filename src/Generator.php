<?php

namespace sndsgd\config;

class Generator
{
    /**
     * The serialization types this generator can create
     *
     * @var array<string>
     */
    const VALID_OUTPUT_TYPES = [
        "php",
        "json",
        "yaml",
    ];

    /**
     * The directories to search for config yaml files
     *
     * @var array<string>
     */
    protected $searchDirectories;

    /**
     * Config value definitions
     *
     * @var array<string,mixed>
     */
    protected $rawValues;

    /**
     * Config object definitions
     *
     * @var array<string,array>
     */
    protected $createDefinitions;

    public function __construct(string ...$directories)
    {
        # we check the directories prior to searching
        # see `sndsgd\fs\locator\LocatorAbstract::getIterator()`
        $this->searchDirectories = $directories;
    }

    /**
     * Write the config values into a particular format in a given path
     *
     * @param string $type The file type
     * @param string $path The absolute path the resulting file
     * @return void
     * @throws \InvalidArgumentException If an invalid type is provided
     * @throws \RuntimeException If the file write operation fails
     */
    public function write(string $type, string $path)
    {
        $this->generateValues();

        $values = [
            $this->rawValues,
            $this->createDefinitions,
        ];

        switch ($type) {
            case "php":
                $values = \sndsgd\Arr::export($values, true);
                $contents = sprintf("<?php\n\nreturn %s;\n", $values);
                break;
            case "json":
                $contents = json_encode($values, \sndsgd\Json::HUMAN);
                break;
            case "yaml":
                $contents = yaml_emit($values);
                break;
            default:
                throw new \InvalidArgumentException(
                    "unknown output type '$type'"
                );
        }

        $file = \sndsgd\Fs::file($path);
        if (!$file->write($contents)) {
            throw new \InvalidArgumentException(
                "failed to write config; ".$file->getError()
            );
        }
    }

    /**
     * Find files, parse their contents, and generate the value arrays
     *
     * @return array<string,mixed>
     */
    protected function generateValues(): array
    {
        if ($this->rawValues !== null) {
            return $this->rawValues;
        }

        $this->rawValues = [];
        $this->createDefinitions = [];

        foreach ($this->findFiles() as $file) {
            $groupName = $file->getName();

            $decodedData = $this->parseYamlFile($file);
            foreach ($decodedData as $key => $value) {

                # filter all keys that begin with an underscore
                # this allows for excluding aliases from results
                if ($key[0] === "_") {
                    continue;
                }

                $valueKey = "$groupName.$key";

                # if the current value is an array and has a validate property
                # assume the validate property is a callabk
                if (is_array($value) && isset($value["_validate"])) {
                    $handler = $value["_validate"];
                    if (
                        !is_callable($handler) &&
                        !\sndsgd\Func::exists($handler)
                    ) {
                        throw new \LogicException(
                            "invalid value for '$valueKey._validate' in '$file'"
                        );
                    }
                    unset($value["_validate"]);
                    $value = call_user_func($handler, $value);
                }

                if ($value instanceof ObjectInterface) {
                    $this->createDefinitions[$valueKey] = [
                        $value->getCreateCallable(),
                        $value->getCreateArguments(),
                    ];
                } else {
                    $this->rawValues[$valueKey] = $value;
                }
            }
        }

        return $this->rawValues;
    }

    /**
     * Search the directories for yaml files
     *
     * @return array<\sndsgd\fs\entity\FileEntity>
     */
    protected function findFiles(): array
    {
        # create a function for filtering all yaml files
        $filter = function(\sndsgd\fs\entity\EntityInterface $entity): bool {
            return (
                $entity->isFile() &&
                ($entity->hasExtension("yml") || $entity->hasExtension("yaml"))
            );
        };

        $locator = new \sndsgd\fs\locator\GenericLocator($filter);
        foreach ($this->searchDirectories as $directory) {
            $locator->searchDir($directory);
        }

        return $locator->getEntities();
    }

    /**
     * Load and parse the contents of a yaml file
     *
     * @param \sndsgd\fs\entity\FileEntity $file
     * @return array
     */
    protected function parseYamlFile(
        \sndsgd\fs\entity\FileEntity $file
    ): array
    {
        $yaml = $file->read();
        if (!$yaml) {
            throw new \InvalidArgumentException($file->getError());
        }

        # set a custom error handler to escalate warnings to exceptions
        set_error_handler(__CLASS__."::handleYamlParseError");
        $data = yaml_parse($yaml);
        restore_error_handler();

        return $data;
    }

    /**
     * Handle warnings/errors the occur while parsing yaml
     *
     * @param int $code The error code
     * @param string $message The warning/error message
     * @param string $file The filename that the error was raised in
     * @param int $line The line number the error was raised at
     * @param array $context The defined symbols that were present
     * @return void
     * @throws \Exception
     */
    public static function handleYamlParseError(
        int $code,
        string $message,
        string $file,
        int $line,
        array $context
    )
    {
        $path = (string) $context["file"] ?? "unknown file";
        throw new \Exception("$message in '$path'");
    }
}
