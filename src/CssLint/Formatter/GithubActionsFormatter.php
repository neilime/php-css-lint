<?php

declare(strict_types=1);

namespace CssLint\Formatter;

use CssLint\LintError;
use Throwable;

/**
 * Formatter for GitHub Actions annotations.
 */
class GithubActionsFormatter implements FormatterInterface
{
    public function getName(): string
    {
        return 'github-actions';
    }

    public function startLinting(string $source): void
    {
        echo "::group::Lint {$source}" . PHP_EOL;
    }

    public function printFatalError(?string $source, mixed $error): void
    {
        $message = $error instanceof Throwable ? $error->getMessage() : (string) $error;
        $location = '';
        if ($source) {
            $location = "file={$source}";
        }
        echo "::error {$location}::{$message}" . PHP_EOL;
    }

    public function printLintError(string $source, LintError $lintError): void
    {
        $key = $lintError->getKey();
        $message = $lintError->getMessage();
        $startPosition = $lintError->getStart();
        $line = $startPosition->getLine();
        $col = $startPosition->getColumn();
        echo "::error file={$source},line={$line},col={$col}::{$key->value} - {$message}" . PHP_EOL;
    }

    public function endLinting(string $source, bool $isValid): void
    {
        if ($isValid) {
            echo "::notice ::Success: {$source} is valid." . PHP_EOL;
        } else {
            echo "::error file={$source}::{$source} is invalid CSS." . PHP_EOL;
        }
        echo "::endgroup::" . PHP_EOL;
    }
}
