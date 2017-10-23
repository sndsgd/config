<?php

namespace sndsgd\config\command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends \Symfony\Component\Console\Command\Command
{
    const NAME = "generate";

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::NAME);
        $this->setDescription("Generate configuration from YAML files");

        $this->addOption(
            "search-dir",
            "s",
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            "Directories to search for YAML files"
        );

        $this->addOption(
            "output-php",
            "",
            InputOption::VALUE_OPTIONAL,
            "The absolute path to the resulting PHP file"
        );

        $this->addOption(
            "output-json",
            "",
            InputOption::VALUE_OPTIONAL,
            "The absolute path to the resulting JSON file"
        );

        $this->addOption(
            "output-yaml",
            "",
            InputOption::VALUE_OPTIONAL,
            "The absolute path to the resulting YAML file"
        );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directories = $this->getDirectories($input, $output);
        $outputFiles = $this->getOutputFiles($input, $output);
        if (!$directories || !$outputFiles) {
            return 1;
        }

        # the constructor throws exceptions if the search directories are invalid
        # technically they should be handled by `getDirectories`, but using a
        # try/catch here for safety
        try {
            $generator = new \sndsgd\config\Generator(...$directories);
        } catch (\Exception $ex) {
            $output->writeln("<error> Error </error> Failed to initialize");
            return 1;
        }

        foreach ($outputFiles as $type => $path) {
            try {
                $generator->write($type, $path);
            } catch (\sndsgd\form\ValidationException $ex) {
                $output->writeln("<error> Error </error> Validation Failed");
                foreach ($ex->getErrors() as $error) {
                    $output->writeln($error->getMessage());
                }
                return 1;
            }

        }

        return 0;
    }

    private function getDirectories(
        InputInterface $input,
        OutputInterface $output
    ): array
    {
        $errors = [];
        $directories = $input->getOption("search-dir");
        if (empty($directories)) {
            $output->writeln("<error> Error </error> No search directories provided");
            return [];
        }

        foreach ($directories as $path) {
            $dir = \sndsgd\Fs::dir($path);
            if (!$dir->test(\sndsgd\Fs::EXISTS | \sndsgd\Fs::READABLE)) {
                $errors[] = $dir->getError();
            }
        }

        $errorCount = count($errors);
        if ($errorCount) {
            $template = "The following search %s %s invalid:";
            if ($errorCount === 1) {
                $message = sprintf($template, "directory", "is");
            } else {
                $message = sprintf($template, "directories", "are");
            }

            $output->writeln("<error> Error </error> $message");
            foreach ($errors as $type => $error) {
                $output->writeln("  - [$type] $error");
            }

            return [];
        }

        return $directories;
    }

    private function getOutputFiles(
        InputInterface $input,
        OutputInterface $output
    ): array
    {
        $types = array_filter([
            'php' => $input->getOption("output-php"),
            'json' => $input->getOption("output-json"),
            'yaml' => $input->getOption("output-yaml"),
        ]);

        # if neither output file was defined, throw an error
        if (empty($types)) {
            $output->writeln("<error> Error </error> No output files provided");
            return [];
        }

        $errors = [];
        foreach ($types as $type => $testPath) {
            $file = \sndsgd\Fs::file($testPath);
            if (!$file->canWrite()) {
                $errors[$type] = $file->getError();
            }
        }

        $errorCount = count($errors);
        if ($errorCount) {
            $template = "The following output %s %s invalid:";
            if ($errorCount === 1) {
                $message = sprintf($template, "path", "is");
            } else {
                $message = sprintf($template, "paths", "are");
            }

            $output->writeln("<error> Error </error> $message");
            foreach ($errors as $type => $error) {
                $output->writeln("  - [$type] $error");
            }

            return [];
        }

        return $types;
    }
}
