<?php

declare(strict_types=1);

namespace CssLint;

use CssLint\Tokenizer\TokenizerContext;
use JsonSerializable;

class LintError implements JsonSerializable
{
    private LintErrorKey $key;
    private string $message;
    private int $line;
    private int $start;
    private int $end;

    /**
     * Constructor for the LintError class.
     *
     * @param LintErrorKey $key The unique descriptive key for the error.
     * @param string $message The error message.
     * @param int $line The line number where the error occurred.
     * @param int $start The start position of the error.
     * @param int $end The end position of the error.
     */
    public function __construct(LintErrorKey $key, string $message, int $line, int $start, int $end)
    {
        $this->key = $key;
        $this->message = $message;
        $this->line = $line;
        $this->start = $start;
        $this->end = $end;
    }

    public static function fromTokenizerContext(
        LintErrorKey $key,
        string $message,
        TokenizerContext $tokenizerContext
    ): self {
        return new self(
            $key,
            $message,
            $tokenizerContext->getLineNumber(),
            $tokenizerContext->getCharNumber(),
            $tokenizerContext->getCharNumber() + 1
        );
    }

    /**
     * Get the unique descriptive key for the error.
     *
     * @return LintErrorKey
     */
    public function getKey(): LintErrorKey
    {
        return $this->key;
    }

    /**
     * Get the error message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the line number where the error occurred.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Get the start position of the error.
     *
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * Get the end position of the error.
     *
     * @return int
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * Convert the error to a string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '[%s]: %s at line %d, position %d-%d',
            $this->key->value,
            $this->message,
            $this->line,
            $this->start,
            $this->end
        );
    }

    /**
     * Serialize the error to JSON.
     *
     * @return array{ key: string, message: string, line: int, start: int, end: int }
     */
    public function jsonSerialize(): array
    {
        return [
            'key' => $this->key->value,
            'message' => $this->message,
            'line' => $this->line,
            'start' => $this->start,
            'end' => $this->end,
        ];
    }
}
