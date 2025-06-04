<?php

declare(strict_types=1);

namespace CssLint\Token;

interface TokenBoundary
{
    /**
     * Check if this token can be the start of another token
     * @param class-string<Token> $tokenClass
     */
    public function canBeStartOfToken(string $tokenClass): bool;
}
