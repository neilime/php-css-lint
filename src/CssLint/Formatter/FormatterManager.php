<?php

declare(strict_types=1);

namespace CssLint\Formatter;

use RuntimeException;

/**
 * Manages one or more formatters as a single formatter.
 */
class FormatterManager implements FormatterInterface
{
    /**
     * Constructor for FormatterManager.
     * @param FormatterInterface[] $formatters List of formatter instances to manage.
     */
    public function __construct(private readonly array $formatters) {}

    public function startLinting(string $source): void
    {
        foreach ($this->formatters as $formatter) {
            $formatter->startLinting($source);
        }
    }

    public function printFatalError(?string $source, mixed $error): void
    {
        foreach ($this->formatters as $formatter) {
            $formatter->printFatalError($source, $error);
        }
    }

    public function printLintError(string $source, mixed $error): void
    {
        foreach ($this->formatters as $formatter) {
            $formatter->printLintError($source, $error);
        }
    }

    public function endLinting(string $source, bool $isValid): void
    {
        foreach ($this->formatters as $formatter) {
            $formatter->endLinting($source, $isValid);
        }
    }

    public function getName(): string
    {
        throw new RuntimeException('FormatterManager does not have a single name. Use the names of individual formatters instead.');
    }
}
