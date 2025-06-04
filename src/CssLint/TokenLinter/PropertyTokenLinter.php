<?php

declare(strict_types=1);

namespace CssLint\TokenLinter;

use CssLint\LintConfiguration;
use CssLint\LintErrorKey;
use CssLint\Token\PropertyToken;
use CssLint\Token\Token;
use CssLint\TokenLinter\TokenError;
use Generator;
use InvalidArgumentException;

class PropertyTokenLinter implements TokenLinter
{
    /**
     * https://drafts.csswg.org/css-variables/#defining-variables
     */
    private const VARIABLE_FORMAT = '/^--[a-zA-Z0-9_-]+$/';

    private const PROPERTY_NAME_PATTERN = '/^[-a-zA-Z][a-zA-Z0-9_-]+$/';

    public function __construct(
        private readonly LintConfiguration $lintConfiguration,
    ) {}

    /**
     * Lints a token and returns a list of issues found.
     *
     * @param Token $token The token to lint.
     * @return Generator<TokenError> A list of issues found during linting.
     */
    public function lint(Token $token): Generator
    {
        if (!$token instanceof PropertyToken) {
            throw new InvalidArgumentException(
                'PropertyTokenLinter can only lint PropertyToken'
            );
        }

        yield from $this->lintPropertyName($token);
        yield from $this->lintPropertyValue($token);

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
        return $token instanceof PropertyToken && $token->isComplete();
    }

    /**
     * @return Generator<TokenError>
     */
    private function lintPropertyName(PropertyToken $token): Generator
    {
        /** @var PropertyToken $token */
        $name = $token->getName();
        if (!$name) {
            yield new TokenError(
                LintErrorKey::INVALID_PROPERTY_DECLARATION,
                'Property name is empty',
                $token
            );
            return;
        }

        if ($token->isVariable()) {
            // Check the variable format
            if (!preg_match(self::VARIABLE_FORMAT, $name)) {
                yield new TokenError(
                    LintErrorKey::INVALID_PROPERTY_DECLARATION,
                    sprintf('Invalid variable format: "%s"', $name),
                    $token
                );
            }
            return;
        }

        if (!preg_match(self::PROPERTY_NAME_PATTERN, $name)) {
            yield new TokenError(
                LintErrorKey::INVALID_PROPERTY_DECLARATION,
                sprintf('Invalid property name format: "%s"', $name),
                $token
            );
            return;
        }

        // Check if property name exists in standard CSS properties
        if (!$this->lintConfiguration->propertyExists($name)) {
            yield new TokenError(
                LintErrorKey::INVALID_PROPERTY_DECLARATION,
                sprintf('Unknown property "%s"', $name),
                $token
            );
        }

        return;
    }

    /**
     * @return Generator<TokenError>
     */
    private function lintPropertyValue(PropertyToken $token): Generator
    {
        $value = $token->getValue();
        if ($value === null) {
            yield new TokenError(
                LintErrorKey::INVALID_PROPERTY_DECLARATION,
                'Property value is empty',
                $token
            );
            return;
        }

        return;
    }
}
