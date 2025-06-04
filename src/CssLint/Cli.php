<?php

declare(strict_types=1);

namespace CssLint;

use Generator;
use RuntimeException;
use Throwable;

/**
 * @phpstan-import-type Errors from \CssLint\Linter
 * @phpstan-import-type LintConfigurationOptions from \CssLint\LintConfiguration
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
        if ($cliArgs->input === null || $cliArgs->input === '' || $cliArgs->input === '0') {
            $this->printUsage();
            return self::RETURN_CODE_SUCCESS;
        }

        try {
            $properties = $this->getLintConfigurationFromOptions($cliArgs->options);

            $cssLinter = new Linter($properties);

            return $this->lintInput($cssLinter, $cliArgs->input);
        } catch (Throwable $throwable) {
            $this->printError($throwable->getMessage());
            return self::RETURN_CODE_ERROR;
        }
    }

    /**
     * Display usage of the cli
     */
    private function printUsage(): void
    {
        $this->printLine('Usage:' . PHP_EOL .
            '------' . PHP_EOL .
            PHP_EOL .
            '  ' . self::SCRIPT_NAME . " [--options='{ }'] input_to_lint" . PHP_EOL .
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
            '  input_to_lint' . PHP_EOL .
            '    The CSS file path (absolute or relative)' . PHP_EOL .
            '    a glob pattern of file(s) to be linted' . PHP_EOL .
            '    or a CSS string to be linted' . PHP_EOL .
            '    Example:' . PHP_EOL .
            '      "./path/to/css_file_path_to_lint.css"' . PHP_EOL .
            '      "./path/to/css_file_path_to_lint/*.css"' . PHP_EOL .
            '      ".test { color: red; }"' . PHP_EOL .
            PHP_EOL .
            'Examples:' . PHP_EOL .
            '---------' . PHP_EOL .
            PHP_EOL .
            '  Lint a CSS file:' . PHP_EOL .
            '    ' . self::SCRIPT_NAME . ' "./path/to/css_file_path_to_lint.css"' . PHP_EOL . PHP_EOL .
            '  Lint a CSS string:' . PHP_EOL .
            '    ' . self::SCRIPT_NAME . ' ".test { color: red; }"' . PHP_EOL . PHP_EOL .
            '  Lint with only tabulation as indentation:' . PHP_EOL .
            '    ' . self::SCRIPT_NAME .
            ' --options=\'{ "allowedIndentationChars": ["\t"] }\' ".test { color: red; }"' . PHP_EOL .
            PHP_EOL . PHP_EOL);
    }

    /**
     * Retrieve the parsed Cli arguments from given arguments array
     * @param string[] $arguments arguments to be parsed (@see $_SERVER['argv'])
     * @return CliArgs an instance of Cli arguments object containing parsed arguments
     */
    private function parseArguments(array $arguments): CliArgs
    {
        return new CliArgs($arguments);
    }

    /**
     * Retrieve the properties from the given options
     * @param string $options the options to be parsed
     */
    private function getLintConfigurationFromOptions(?string $options): LintConfiguration
    {
        $lintConfiguration = new LintConfiguration();
        if ($options === null || $options === '' || $options === '0') {
            return $lintConfiguration;
        }

        $options = json_decode($options, true);

        if (json_last_error() !== 0) {
            $errorMessage = json_last_error_msg();
            throw new RuntimeException('Unable to parse option argument: ' . $errorMessage);
        }

        $this->assertOptionsAreLintConfiguration($options);

        $lintConfiguration->setOptions($options);

        return $lintConfiguration;
    }

    /**
     * @param mixed $options
     * @phpstan-assert LintConfigurationOptions $options
     */
    private function assertOptionsAreLintConfiguration(mixed $options): void
    {
        if (!$options) {
            throw new RuntimeException('Unable to parse empty option argument');
        }

        if (!is_array($options)) {
            throw new RuntimeException('Unable to parse option argument: must be a json object');
        }

        $allowedKeys = [
            'allowedIndentationChars',
            'constructors',
            'standards',
            'nonStandards',
        ];

        foreach ($options as $key => $value) {
            if (!in_array($key, $allowedKeys)) {
                throw new RuntimeException(sprintf('Invalid option key: "%s"', $key));
            }
        }

        // Assert that the allowedIndentationChars is an array of strings
        foreach ($allowedKeys as $key) {
            if (isset($options[$key]) && !is_array($options[$key])) {
                throw new RuntimeException(sprintf('Option "%s" must be an array', $key));
            }
        }
    }

    private function lintInput(Linter $cssLinter, string $input): int
    {
        if (file_exists($input)) {
            return $this->lintFile($cssLinter, $input);
        }

        if ($this->isGlobPattern($input)) {
            return $this->lintGlob($input);
        }

        return $this->lintString($cssLinter, $input);
    }

    /**
     * Checks if a given string is a glob pattern.
     *
     * A glob pattern typically includes wildcard characters:
     * - '*' matches any sequence of characters.
     * - '?' matches any single character.
     * - '[]' matches any one character in the specified set.
     *
     * Optionally, if using the GLOB_BRACE flag, brace patterns like {foo,bar} are also valid.
     *
     * @param string $pattern The string to evaluate.
     * @return bool True if the string is a glob pattern, false otherwise.
     */
    private function isGlobPattern(string $pattern): bool
    {
        // Must be one line, no unscaped spaces
        if (preg_match('/\s/', $pattern)) {
            return false;
        }

        // Check for basic wildcard characters.
        if (str_contains($pattern, '*') || str_contains($pattern, '?') || str_contains($pattern, '[')) {
            return true;
        }

        // Optionally check for brace patterns, used with GLOB_BRACE.
        return str_contains($pattern, '{') || str_contains($pattern, '}');
    }

    private function lintGlob(string $glob): int
    {
        $cssLinter = new Linter();
        $files = glob($glob);
        if ($files === [] || $files === false) {
            $this->printError('No files found for glob "' . $glob . '"');
            return self::RETURN_CODE_ERROR;
        }

        $returnCode = self::RETURN_CODE_SUCCESS;
        foreach ($files as $file) {
            $returnCode = max($returnCode, $this->lintFile($cssLinter, $file));
        }

        return $returnCode;
    }

    /**
     * Performs lint on a given file path
     * @param Linter $cssLinter the instance of the linter
     * @param string $filePath the path of the file to be linted
     * @return int the return code related to the execution of the linter
     */
    private function lintFile(Linter $cssLinter, string $filePath): int
    {
        $source = "CSS file \"" . $filePath . "\"";
        $this->printLine('# Lint ' . $source . '...');

        if (!is_readable($filePath)) {
            $this->printError('File "' . $filePath . '" is not readable');
            return self::RETURN_CODE_ERROR;
        }

        $errors = $cssLinter->lintFile($filePath);
        return $this->printLinterErrors($source, $errors);
    }


    /**
     * Performs lint on a given string
     * @param Linter $cssLinter the instance of the linter
     * @param string $stringValue the CSS string to be linted
     * @return int the return code related to the execution of the linter
     */
    private function lintString(Linter $cssLinter, string $stringValue): int
    {
        $source = 'CSS string';
        $this->printLine('# Lint ' . $source . '...');
        $errors = $cssLinter->lintString($stringValue);
        return $this->printLinterErrors($source, $errors);
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
     * @param Generator<LintError> $errors the generated errors to be displayed
     * @return int the return code related to the execution of the linter
     */
    private function printLinterErrors(string $source, Generator $errors): int
    {
        $hasErrors = false;
        foreach ($errors as $error) {
            if ($hasErrors === false) {
                $this->printLine("\033[31m => " . $source . " is not valid:\033[0m" . PHP_EOL);
                $hasErrors = true;
            }
            $this->printLine("\033[31m    - " . $error . "\033[0m");
        }

        if ($hasErrors) {
            $this->printLine("");
            return self::RETURN_CODE_ERROR;
        }

        $this->printLine("\033[32m => " . $source . " is valid\033[0m" . PHP_EOL);
        return self::RETURN_CODE_SUCCESS;
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
