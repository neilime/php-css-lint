<?php

declare(strict_types=1);

namespace CssLint\Token;

class SelectorToken extends AbstractToken implements TokenBoundary
{
    public function __construct(string $value, int $line, int $start, int $end)
    {
        parent::__construct('selector', $value, $line, $start, $end);
    }

    public function canBeStartOfToken(string $tokenClass): bool
    {
        return $tokenClass === BlockToken::class;
    }

    public function getTransitionableTokens(): array
    {
        return [BlockToken::class];
    }
}
