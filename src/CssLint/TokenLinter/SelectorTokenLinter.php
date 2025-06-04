<?php

declare(strict_types=1);

namespace CssLint\TokenLinter;

use CssLint\LintErrorKey;
use CssLint\Token\SelectorToken;
use CssLint\Token\Token;
use CssLint\TokenLinter\TokenError;
use Generator;
use InvalidArgumentException;

class SelectorTokenLinter implements TokenLinter
{
    private static string $SELECTOR_PATTERN = '/^[.#a-zA-Z0-9\[\]=\'"\-_:>+~\s\(\)]+$/';

    /**
     * Lints a token and returns a list of issues found.
     *
     * @param Token $token The token to lint.
     * @return Generator<TokenError> A list of issues found during linting.
     */
    public function lint(Token $token): Generator
    {
        if (!$token instanceof SelectorToken) {
            throw new InvalidArgumentException(
                'SelectorTokenLinter can only lint SelectorToken'
            );
        }

        $value = $token->getValue();

        // Check if the selector contains invalid characters
        if (
            !preg_match(self::$SELECTOR_PATTERN, $value)
            || str_contains($value, '##')
        ) {
            yield new TokenError(
                LintErrorKey::UNEXPECTED_SELECTOR_CHARACTER,
                sprintf('Selector contains invalid characters: "%s"', $value),
                $token
            );
        }

        return;
    }

    /**
     * Checks if the linter supports the given token.
     *
     * @param Token $token The token to check.
     * @return bool True if the linter supports the token, false otherwise.
     */
    public function supports(Token $token): bool
    {
        return $token instanceof SelectorToken;
    }
}
