<?php

declare(strict_types=1);

namespace CssLint\Formatter;

use CssLint\LintError;
use Generator;
use Throwable;

/**
 * Plain text formatter implementation.
 */
class PlainFormatter implements FormatterInterface
{
    public function startLinting(string $source): void
    {
        echo "# Lint {$source}..." . PHP_EOL;
    }

    public function printFatalError(?string $source, mixed $error): void
    {
        if ($error instanceof Throwable) {
            $error = $error->getMessage();
        }

        if ($source) {
            $error = "$source - " . $error;
        }

        echo "\033[31m/!\ Error: " . $error . "\033[0m" . PHP_EOL;
    }

    public function printLintError(string $source, LintError $lintError): void
    {
        echo "\033[31m    - " . $lintError . "\033[0m" . PHP_EOL;
    }

    public function endLinting(string $source, bool $isValid): void
    {
        if ($isValid) {
            echo "\033[32m => Success: {$source} is valid.\033[0m" . PHP_EOL . PHP_EOL;
        } else {
            echo "\033[31m => Failure: {$source} is invalid CSS.\033[0m" . PHP_EOL;
        }
    }

    public function getName(): string
    {
        return 'plain';
    }
}
