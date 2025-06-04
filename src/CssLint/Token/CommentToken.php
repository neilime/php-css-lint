<?php

declare(strict_types=1);

namespace CssLint\Token;

class CommentToken extends AbstractToken
{
    public function __construct(string $value, int $line, int $start, ?int $end = null)
    {
        parent::__construct('comment', $value, $line, $start, $end);
    }
}
