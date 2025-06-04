<?php

declare(strict_types=1);

namespace CssLint\Token;

class WhitespaceToken extends AbstractToken implements TokenBoundary
{
    public function __construct(string $value, int $line, int $start, int $end)
    {
        parent::__construct('whitespace', $value, $line, $start, $end);
    }

    public function canBeStartOfToken(string $tokenClass): bool
    {
        return in_array($tokenClass, $this->getTransitionableTokens(), true);
    }

    public function getTransitionableTokens(): array
    {
        return [
            BlockToken::class,
            CommentToken::class,
            PropertyToken::class,
            SelectorToken::class,
        ];
    }
}
