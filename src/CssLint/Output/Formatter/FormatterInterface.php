<?php

declare(strict_types=1);

namespace CssLint\Output\Formatter;

use CssLint\LintError;
use Throwable;

interface FormatterInterface
{
    /**
     * Returns the name used to select this formatter (e.g., 'plain', 'json').
     *
     * @return non-empty-string
     */
    public function getName(): string;

    /**
     * Start linting a source file.
     *
     * @param string $source The source being linted (e.g., "CSS file \"...\"").
     */
    public function startLinting(string $source): string;

    /**
     * Output a fatal error message.
     *
     * @param string|null $source The source being linted (e.g., "CSS file \"...\"").
     * @param Throwable|string $error The exception or error that occurred, which may include a message and stack trace.
     */
    public function printFatalError(?string $source, mixed $error): string;

    /**
     * Output a parsing or runtime error message.
     *
     * @param string $source The source being linted (e.g., "CSS file \"...\"").
     * @param LintError $error The error to be printed, which may include details like line number, column, and message.
     */
    public function printLintError(string $source, LintError $error): string;


    /**
     * End linting a source file.
     *
     * @param string $source The source being linted (e.g., "CSS file \"...\"").
     * @param bool $isValid Whether the source is valid CSS.
     */
    public function endLinting(string $source, bool $isValid): string;
}
