<?php

declare(strict_types=1);

namespace CssLint\Token;

use CssLint\Position;
use CssLint\Tokenizer\TokenizerContext;
use JsonSerializable;

/**
 * @template TValue of Token[]|string|null = Token[]|string|null
 */
interface Token extends JsonSerializable
{
    public function getType(): string;

    /**
     * @return TValue
     */
    public function getValue(): mixed;

    public function getStart(): Position;

    public function getEnd(): ?Position;

    public function setEnd(Position $end): self;

    public function isComplete(): bool;

    public function setParent(?BlockToken $parent): self;

    public function getParent(): ?BlockToken;

    public function getPreviousToken(): ?Token;

    public function setPreviousToken(?Token $previousToken): self;

    public static function calculateStartPosition(TokenizerContext $tokenizerContext): Position;

    public static function calculateEndPosition(TokenizerContext $tokenizerContext, ?Token $token = null): Position;
}
