<?php

declare(strict_types=1);

namespace CssLint\Token;

interface TokenBoundary
{
    /**
     * Check if this token can be the start of another token
     */
    public function canBeStartOfToken(string $tokenClass): bool;

    /**
     * Get the token classes that this token can transition to
     * @return array<class-string<Token>>
     */
    public function getTransitionableTokens(): array;
}
