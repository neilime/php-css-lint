<?php

declare(strict_types=1);

namespace CssLint;

use CssLint\Token\AbstractToken;
use CssLint\Tokenizer\TokenizerContext;
use JsonSerializable;
use Stringable;

/**
 * @phpstan-import-type SerializedPosition from Position
 * @phpstan-type SerializedLintError array{ key: string, message: string, start: SerializedPosition, end: SerializedPosition }
 */
class LintError implements JsonSerializable, Stringable
{
    /**
     * Constructor for the LintError class.
     */
    public function __construct(
        private readonly LintErrorKey $key,
        private readonly string $message,
        private readonly Position $start,
        private readonly Position $end,
    ) {}

    public static function fromTokenizerContext(
        LintErrorKey $key,
        string $message,
        TokenizerContext $tokenizerContext
    ): self {

        $start = AbstractToken::calculateStartPosition($tokenizerContext);
        $end = AbstractToken::calculateEndPosition($tokenizerContext);

        return new self(
            $key,
            $message,
            $start,
            $end,
        );
    }

    /**
     * Convert the error to a string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '[%s]: %s (line %d, column %d to line %d, column %d)',
            $this->key->value,
            $this->message,
            $this->start->getLine(),
            $this->start->getColumn(),
            $this->end->getLine(),
            $this->end->getColumn()
        );
    }

    /**
     * Serialize the error to JSON.
     *
     * @return SerializedLintError
     */
    public function jsonSerialize(): array
    {
        return [
            'key' => $this->key->value,
            'message' => $this->message,
            'start' => $this->start->jsonSerialize(),
            'end' => $this->end->jsonSerialize(),
        ];
    }

    /**
     * Get the key of the lint error.
     *
     * @return LintErrorKey
     */
    public function getKey(): LintErrorKey
    {
        return $this->key;
    }

    /**
     * Get the message of the lint error.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the start position of the lint error.
     *
     * @return Position
     */
    public function getStart(): Position
    {
        return $this->start;
    }

    /**
     * Get the end position of the lint error.
     *
     * @return Position
     */
    public function getEnd(): Position
    {
        return $this->end;
    }
}
