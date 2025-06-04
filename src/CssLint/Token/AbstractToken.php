<?php

declare(strict_types=1);

namespace CssLint\Token;

use CssLint\Position;
use CssLint\Tokenizer\TokenizerContext;
use JsonSerializable;

/**
 * @phpstan-import-type SerializedPosition from Position
 * @phpstan-type SerializedToken array{ type: string, value: mixed, start: SerializedPosition, end: SerializedPosition|null }
 * @template TData
 * @template TValue of Token[]|string|null = Token[]|string|null
 * @phpstan-implements Token<TValue>
 */
abstract class AbstractToken implements Token
{
    protected ?BlockToken $parent = null;

    protected ?Token $previousToken = null;

    /**
     * @param TData $data
     */
    public function __construct(
        private readonly string $type,
        protected mixed $data,
        private readonly Position $start,
        private ?Position $end = null
    ) {}

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return TValue
     */
    abstract public function getValue(): mixed;

    public function getStart(): Position
    {
        return $this->start;
    }

    public function getEnd(): ?Position
    {
        return $this->end;
    }

    /**
     * @return self<TData, TValue>
     */
    public function setEnd(Position $end): self
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Returns a JSON serializable representation of the token.
     *
     * @return SerializedToken
     */
    public function jsonSerialize(): array
    {
        $value = $this->data;
        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        } elseif (is_array($value)) {
            $value = array_map(fn($item) => $item instanceof JsonSerializable ? $item->jsonSerialize() : $item, $value);
        }

        return [
            'type' => $this->type,
            'value' => $value,
            'start' => $this->start->jsonSerialize(),
            'end' => $this->end?->jsonSerialize(),
        ];
    }

    public function isComplete(): bool
    {
        return $this->end !== null;
    }

    /**
     * @return self<TData, TValue>
     */
    public function setParent(?BlockToken $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function getParent(): ?BlockToken
    {
        return $this->parent;
    }

    public function getPreviousToken(): ?Token
    {
        return $this->previousToken;
    }

    /**
     * @return self<TData, TValue>
     */
    public function setPreviousToken(?Token $previousToken): self
    {
        $this->previousToken = $previousToken;
        return $this;
    }

    public static function calculateStartPosition(TokenizerContext $tokenizerContext): Position
    {
        $currentPosition = $tokenizerContext->getCurrentPosition();

        $startColumn = $currentPosition->getColumn() - strlen($tokenizerContext->getCurrentContent());
        if ($startColumn < 1) {
            $startColumn = 1;
        }

        return new Position(
            $currentPosition->getLine(),
            $startColumn
        );
    }

    public static function calculateEndPosition(TokenizerContext $tokenizerContext, ?Token $token = null): Position
    {
        $currentPosition = $tokenizerContext->getCurrentPosition();
        $endColumn = $currentPosition->getColumn();

        if (is_a(static::class, TokenBoundary::class, true)) {
            $endColumn = $endColumn - 1;
        }

        if ($token) {
            $startPosition = $token->getStart();
            $startLine = $startPosition->getLine();
            $startColumn = $startPosition->getColumn();

            if ($startLine === $currentPosition->getLine() && $endColumn <= $startColumn) {
                $endColumn = $startColumn + 1;
            }
        }

        if ($endColumn < 1) {
            $endColumn = 1;
        }

        return new Position($currentPosition->getLine(), $endColumn);
    }
}
