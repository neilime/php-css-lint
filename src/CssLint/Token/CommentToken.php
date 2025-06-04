<?php

declare(strict_types=1);

namespace CssLint\Token;

use CssLint\Position;

/**
 * @phpstan-extends AbstractToken<string, string>
 */
class CommentToken extends AbstractToken
{
    public function __construct(string $value, Position $start, ?Position $end = null)
    {
        parent::__construct('comment', $value, $start, $end);
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
