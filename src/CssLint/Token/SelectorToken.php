<?php

declare(strict_types=1);

namespace CssLint\Token;

use CssLint\Position;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @phpstan-extends AbstractToken<string, string>
 */
class SelectorToken extends AbstractToken implements TokenBoundary
{
    public function __construct(string $value, Position $start, ?Position $end = null)
    {
        parent::__construct('selector', $value, $start, $end);
    }

    public function canTransitionTo(string $tokenClass, TokenizerContext $tokenizerContext): bool
    {
        return $tokenClass === BlockToken::class;
    }

    public function getValue(): string
    {
        return $this->data;
    }

    public function setValue(string $value): self
    {
        $this->data = $value;
        return $this;
    }
}
