<?php

declare(strict_types=1);

namespace CssLint;

/**
 * @phpstan-import-type Errors from \CssLint\Linter
 * @package CssLint
 */
class Cli
{
    private const SCRIPT_NAME = 'php-css-lint';

    private const RETURN_CODE_ERROR = 1;

    private const RETURN_CODE_SUCCESS = 0;

    /**
     * Entrypoint of the cli, will execute the linter according to the given arguments
     * @param string[] $arguments arguments to be parsed (@see $_SERVER['argv'])
     * @return int the return code related to the execution of the linter
     **/
    public function run(array $arguments): int
    {
        $cliArgs = $this->parseArguments($arguments);
        if ($cliArgs->filePathOrCssString === null || $cliArgs->filePathOrCssString === '' || $cliArgs->filePathOrCssString === '0') {
            $this->printUsage();
            return self::RETURN_CODE_SUCCESS;
        }

        $properties = new \CssLint\Properties();
        if ($cliArgs->options !== null && $cliArgs->options !== '' && $cliArgs->options !== '0') {
            $options = json_decode($cliArgs->options, true);

            if (json_last_error() !== 0) {
                $errorMessage = json_last_error_msg();
                $this->printError('Unable to parse option argument: ' . $errorMessage);
                return self::RETURN_CODE_ERROR;
            }

            if (!$options) {
                $this->printError('Unable to parse empty option argument');
                return self::RETURN_CODE_ERROR;
            }

            if (!is_array($options)) {
                $this->printError('Unable to parse option argument: must be a json object');
                return self::RETURN_CODE_ERROR;
            }

            $properties->setOptions($options);
        }

        $cssLinter = new \CssLint\Linter($properties);

        $filePathOrCssString = $cliArgs->filePathOrCssString;
        if (!file_exists($filePathOrCssString)) {
            return $this->lintString($cssLinter, $filePathOrCssString);
        }

        $filePath = $filePathOrCssString;
        if (!is_readable($filePath)) {
            $this->printError('File "' . $filePath . '" is not readable');
            return self::RETURN_CODE_ERROR;
        }

        return $this->lintFile($cssLinter, $filePath);
    }

    /**
     * Retrieve the parsed Cli arguments from given arguments array
     * @param string[] $arguments arguments to be parsed (@see $_SERVER['argv'])
     * @return \CssLint\CliArgs an instance of Cli arguments object containing parsed arguments
     */
    private function parseArguments(array $arguments): \CssLint\CliArgs
    {
        return new \CssLint\CliArgs($arguments);
    }

    /**
     * Display usage of the cli
     */
    private function printUsage(): void
    {
        $this->printLine('Usage:' . PHP_EOL .
            '------' . PHP_EOL .
            PHP_EOL .
            '  ' . self::SCRIPT_NAME . " [--options='{ }'] css_file_or_string_to_lint" . PHP_EOL .
            PHP_EOL .
            'Arguments:' . PHP_EOL .
            '----------' . PHP_EOL .
            PHP_EOL .
            '  --options' . PHP_EOL .
            '    Options (optional), must be a json object:' . PHP_EOL .
            '     * "allowedIndentationChars" => [" "] or ["\t"]: will override the current property' . PHP_EOL .
            '     * "constructors": { "property" => bool }: will merge with the current property' . PHP_EOL .
            '     * "standards": { "property" => bool }: will merge with the current property' . PHP_EOL .
            '     * "nonStandards": { "property" => bool }: will merge with the current property' . PHP_EOL .
            '    Example: --options=\'{ "constructors": {"o" : false}, "allowedIndentationChars": ["\t"] }\'' .
            PHP_EOL .
            PHP_EOL .
            '  css_file_or_string_to_lint' . PHP_EOL .
            '    The CSS file path (absolute or relative) or a CSS string to be linted' . PHP_EOL .
            '    Example:' . PHP_EOL .
            '      ./path/to/css_file_path_to_lint.css' . PHP_EOL .
            '      ".test { color: red; }"' . PHP_EOL .
            PHP_EOL .
            'Examples:' . PHP_EOL .
            '---------' . PHP_EOL .
            PHP_EOL .
            '  Lint a CSS file:' . PHP_EOL .
            '    ' . self::SCRIPT_NAME . ' ./path/to/css_file_path_to_lint.css' . PHP_EOL . PHP_EOL .
            '  Lint a CSS string:' . PHP_EOL .
            '    ' . self::SCRIPT_NAME . ' ".test { color: red; }"' . PHP_EOL .  PHP_EOL .
            '  Lint with only tabulation as indentation:' . PHP_EOL .
            '    ' . self::SCRIPT_NAME .
            ' --options=\'{ "allowedIndentationChars": ["\t"] }\' ".test { color: red; }"' . PHP_EOL .
            PHP_EOL . PHP_EOL);
    }

    /**
     * Performs lint on a given file path
     * @param \CssLint\Linter $cssLinter the instance of the linter
     * @param string $filePath the path of the file to be linted
     * @return int the return code related to the execution of the linter
     */
    private function lintFile(\CssLint\Linter $cssLinter, string $filePath): int
    {
        $this->printLine('# Lint CSS file "' . $filePath . '"...');

        if ($cssLinter->lintFile($filePath)) {
            $this->printLine("\033[32m => CSS file \"" . $filePath . "\" is valid\033[0m" . PHP_EOL);
            return self::RETURN_CODE_SUCCESS;
        }

        $this->printLine("\033[31m => CSS file \"" . $filePath . "\" is not valid:\033[0m" . PHP_EOL);
        $this->displayLinterErrors($cssLinter->getErrors());
        return self::RETURN_CODE_ERROR;
    }


    /**
     * Performs lint on a given string
     * @param \CssLint\Linter $cssLinter the instance of the linter
     * @param string $stringValue the CSS string to be linted
     * @return int the return code related to the execution of the linter
     */
    private function lintString(\CssLint\Linter $cssLinter, string $stringValue): int
    {
        $this->printLine('# Lint CSS string...');

        if ($cssLinter->lintString($stringValue)) {
            $this->printLine("\033[32m => CSS string is valid\033[0m" . PHP_EOL);
            return self::RETURN_CODE_SUCCESS;
        }

        $this->printLine("\033[31m => CSS string is not valid:\033[0m" . PHP_EOL);
        $this->displayLinterErrors($cssLinter->getErrors());
        return self::RETURN_CODE_ERROR;
    }

    /**
     * Display an error message
     * @param string $error the message to be displayed
     */
    private function printError(string $error): void
    {
        $this->printLine("\033[31m/!\ Error: " . $error . "\033[0m" . PHP_EOL);
    }

    /**
     * Display the errors returned by the linter
     * @param Errors $errors the generated errors to be displayed
     */
    private function displayLinterErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $this->printLine("\033[31m    - " . $error . "\033[0m");
        }

        $this->printLine("");
    }

    /**
     * Display the given message in a new line
     * @param string $message the message to be displayed
     */
    private function printLine(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
