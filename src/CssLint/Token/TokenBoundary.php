<?php

declare(strict_types=1);

namespace CssLint\Token;

use CssLint\Tokenizer\TokenizerContext;

interface TokenBoundary
{
    /**
     * Check if this token can transition to another token type
     * @param class-string<Token> $tokenClass
     */
    public function canTransitionTo(string $tokenClass, TokenizerContext $tokenizerContext): bool;
}
