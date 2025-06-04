<?php

declare(strict_types=1);

namespace CssLint\TokenLinter;

use CssLint\LintError;
use CssLint\LintErrorKey;
use CssLint\Position;
use CssLint\Token\Token;

class TokenError extends LintError
{
    /**
     * Constructor for the Error class.
     *
     * @param string $message The error message.
     * @param Token $token The token associated with the error.
     */
    public function __construct(
        LintErrorKey $key,
        string $message,
        Token $token,
        ?Position $start = null,
        ?Position $end = null,
    ) {
        parent::__construct(
            $key,
            sprintf("%s - %s", $token->getType(), $message),
            $start ?? $token->getStart(),
            $end ?? $token->getEnd() ?? $token->getStart()
        );
    }
}
