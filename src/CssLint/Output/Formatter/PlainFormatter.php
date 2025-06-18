<?php

declare(strict_types=1);

namespace CssLint\Output\Formatter;

use CssLint\LintError;
use Throwable;

/**
 * Plain text formatter implementation.
 */
class PlainFormatter implements FormatterInterface
{
    public function startLinting(string $source): string
    {
        return "# Lint {$source}..." . PHP_EOL;
    }

    public function printFatalError(?string $source, mixed $error): string
    {
        if ($error instanceof Throwable) {
            $error = $error->getMessage();
        }

        if ($source) {
            $error = "$source - " . $error;
        }

        return "\033[31m/!\ Error: " . $error . "\033[0m" . PHP_EOL;
    }

    public function printLintError(string $source, LintError $lintError): string
    {
        return "\033[31m    - " . $lintError . "\033[0m" . PHP_EOL;
    }

    public function endLinting(string $source, bool $isValid): string
    {
        if ($isValid) {
            return "\033[32m => Success: {$source} is valid.\033[0m" . PHP_EOL . PHP_EOL;
        } else {
            return "\033[31m => Failure: {$source} is invalid CSS.\033[0m" . PHP_EOL;
        }
    }

    public function getName(): string
    {
        return 'plain';
    }
}
