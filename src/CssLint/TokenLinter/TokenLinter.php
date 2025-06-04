<?php

declare(strict_types=1);

namespace CssLint\TokenLinter;

use CssLint\Token\Token;
use CssLint\LintError;
use Generator;

interface TokenLinter
{
    /**
     * Lints a token and returns a list of issues found.
     *
     * @param Token $token The token to lint.
     * @return Generator<LintError> A list of issues found during linting.
     */
    public function lint(Token $token): Generator;

    public function supports(Token $token): bool;
}
