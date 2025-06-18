<?php

declare(strict_types=1);

namespace CssLint;

/**
 * @package CssLint
 * @phpstan-type Arguments string[]
 */
class CliArgs
{
    public ?string $input = null;

    public ?string $options = null;

    /**
     * Array of formatter specifications with their output destinations
     * Format: ['plain' => null, 'gitlab-ci' => '/path/to/report.json']
     * @var array<string, string|null>
     */
    public array $formatters = [];

    /**
     * Constructor
     * @param Arguments $arguments arguments to be parsed (@see $_SERVER['argv'])
     *              Accepts "-o", "--options" '{}'
     *              Accepts a string as last argument, a file path or a string containing CSS
     */
    public function __construct(array $arguments)
    {
        if ($arguments === [] || count($arguments) === 1) {
            return;
        }

        array_shift($arguments);

        $this->input = array_pop($arguments);

        if ($arguments !== []) {
            $this->parseArguments($arguments);
        }
    }

    /**
     * @param Arguments $arguments array of arguments to be parsed (@see $_SERVER['argv'])
     */
    private function parseArguments(array $arguments): void
    {
        foreach ($arguments as $argument) {
            // --foo --bar=baz
            if (str_starts_with((string) $argument, '--')) {
                $equalPosition = strpos((string) $argument, '=');

                if ($equalPosition !== false) {
                    $key = substr((string) $argument, 2, $equalPosition - 2);
                    $value = substr((string) $argument, $equalPosition + 1);

                    if ($key === 'options') {
                        $this->options = $value;
                    } elseif ($key === 'formatter') {
                        $this->parseFormatterSpec($value);
                    }
                }
            }
        }
    }

    /**
     * Parse a formatter specification like "plain" or "gitlab-ci:/path/to/file.json"
     */
    private function parseFormatterSpec(string $formatterSpec): void
    {
        $colonPosition = strpos($formatterSpec, ':');

        if ($colonPosition !== false) {
            // Format: formatter:path
            $formatterName = substr($formatterSpec, 0, $colonPosition);
            $outputPath = substr($formatterSpec, $colonPosition + 1);
            $this->formatters[$formatterName] = $outputPath;
        } else {
            // Format: formatter (stdout only)
            $this->formatters[$formatterSpec] = null;
        }
    }
}
