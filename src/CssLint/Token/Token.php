<?php

declare(strict_types=1);

namespace CssLint\Token;

use JsonSerializable;

/**
 * @phpstan-type TokenValue Token[]|string|null
 */
interface Token extends JsonSerializable
{
    public function getType(): string;

    /**
     * @return TokenValue
     */
    public function getValue(): mixed;

    /**
     * @param TokenValue $value
     */
    public function setValue(mixed $value): void;

    public function getLine(): int;
    public function getStart(): int;
    public function getEnd(): int;
    public function setEnd(int $end): void;

    public function isComplete(): bool;

    public function setParent(?Token $parent): void;

    public function getParent(): ?Token;

    public function getRoot(): Token;
}
