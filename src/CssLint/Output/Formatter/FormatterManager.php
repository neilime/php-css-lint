<?php

declare(strict_types=1);

namespace CssLint\Output\Formatter;

use CssLint\LintError;
use CssLint\Output\OutputInterface;
use Throwable;

/**
 * Manages one or more formatters as a single formatter.
 * @phpstan-type OutputFormatter array{OutputInterface,FormatterInterface}
 * @phpstan-type OutputFormatters array<OutputFormatter>
 */
class FormatterManager
{
    /**
     * Constructor for FormatterManager.
     * @param OutputFormatters $outputFormatters List of output formatters tuples to manage.
     */
    public function __construct(private readonly array $outputFormatters) {}

    public function startLinting(string $source): void
    {
        foreach ($this->outputFormatters as [$output, $formatter]) {
            $output->write($formatter->startLinting($source));
        }
    }

    /**
     * Prints a fatal error message for the given source.
     * @param string|null $source The source being linted (e.g., "CSS file \"...\"").
     * @param Throwable|string $error The exception or error that occurred, which may include a message and stack trace.
     * @return void
     */
    public function printFatalError(?string $source, mixed $error): void
    {
        foreach ($this->outputFormatters as [$output, $formatter]) {
            $output->write($formatter->printFatalError($source, $error));
        }
    }

    public function printLintError(string $source, LintError $error): void
    {
        foreach ($this->outputFormatters as [$output, $formatter]) {
            $output->write($formatter->printLintError($source, $error));
        }
    }

    public function endLinting(string $source, bool $isValid): void
    {
        foreach ($this->outputFormatters as [$output, $formatter]) {
            $output->write($formatter->endLinting($source, $isValid));
        }
    }
}
