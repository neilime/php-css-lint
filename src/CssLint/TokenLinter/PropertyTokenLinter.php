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

    private const PROPERTY_NAME_PATTERN = '/^[a-zA-Z][a-zA-Z0-9_-]+$/';

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
        if (!$this->supports($token)) {
            return;
        }

        yield from $this->lintPropertyName($token);
        yield from $this->lintPropertyValue($token);
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
     * @return Generator<LintError>
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
                    'Invalid variable format',
                    $token
                );
            }
            return;
        }

        if (!preg_match(self::PROPERTY_NAME_PATTERN, $name)) {
            yield new TokenError(
                LintErrorKey::INVALID_PROPERTY_DECLARATION,
                'Invalid property name format',
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
    }

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

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                '%s can only lint %s with string value',
                self::class,
                PropertyToken::class
            ));
        }
    }
}
