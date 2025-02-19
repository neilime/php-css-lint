<?php

declare(strict_types=1);

namespace CssLint;

/**
 * @package CssLint
 * @phpstan-type Arguments string[]
 * @phpstan-type ParsedArguments array<string, string>
 */
class CliArgs
{
    public ?string $filePathOrCssString = null;

    public ?string $options = null;

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

        $this->filePathOrCssString = array_pop($arguments);

        if ($arguments !== []) {
            $parsedArguments = $this->parseArguments($arguments);

            if (!empty($parsedArguments['options'])) {
                $this->options = $parsedArguments['options'];
            }
        }
    }

    /**
     * @param Arguments $arguments array of arguments to be parsed (@see $_SERVER['argv'])
     * @return ParsedArguments an associative array of key=>value arguments
     */
    private function parseArguments(array $arguments): array
    {
        $aParsedArguments = [];

        foreach ($arguments as $argument) {
            // --foo --bar=baz
            if (str_starts_with((string) $argument, '--')) {
                $equalPosition = strpos((string) $argument, '=');

                // --bar=baz
                if ($equalPosition !== false) {
                    $key = substr((string) $argument, 2, $equalPosition - 2);
                    $value = substr((string) $argument, $equalPosition + 1);
                    $aParsedArguments[$key] = $value;
                }
            }
        }

        return $aParsedArguments;
    }
}
