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
    /**
     * Matches valid CSS selectors including:
     * - Class selectors (.class)
     * - ID selectors (#id)
     * - Element selectors (div)
     * - Attribute selectors ([attr=value])
     * - Pseudo-classes (:hover)
     * - Combinators (>, +, ~)
     */
    public static string $SELECTOR_PATTERN = '/^[.#a-zA-Z0-9\[\]=\'"\-_\:>\+~\s\(\),]+$/';

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

        if (!preg_match(self::$SELECTOR_PATTERN, $value)) {
            yield new TokenError(
                LintErrorKey::UNEXPECTED_SELECTOR_CHARACTER,
                sprintf('Selector contains invalid characters: "%s"', $value),
                $token
            );
        }

        // Check if the selector contains consecutive characters
        $notAllowedConsecutiveChars = ['#', '.', '::', '>', '+', '~', ',', '['];
        foreach ($notAllowedConsecutiveChars as $char) {
            if (str_contains($value, $char . $char)) {
                yield new TokenError(
                    LintErrorKey::UNEXPECTED_SELECTOR_CHARACTER,
                    sprintf('Selector contains invalid consecutive characters: "%s"', $value),
                    $token
                );
            }
        }

        // Check if selector has the proper number of parentheses
        $openParenthesesCount = substr_count($value, '(');
        $closeParenthesesCount = substr_count($value, ')');
        if ($openParenthesesCount !== $closeParenthesesCount) {
            yield new TokenError(
                LintErrorKey::UNEXPECTED_SELECTOR_CHARACTER,
                sprintf('Selector contains invalid number of parentheses: "%s"', $value),
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
