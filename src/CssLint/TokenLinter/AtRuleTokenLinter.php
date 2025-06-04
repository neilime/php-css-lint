<?php

declare(strict_types=1);

namespace CssLint\TokenLinter;

use CssLint\LintConfiguration;
use CssLint\LintErrorKey;
use CssLint\Token\AtRuleToken;
use CssLint\Token\Token;
use Generator;
use InvalidArgumentException;

class AtRuleTokenLinter implements TokenLinter
{
    private static string $AT_RULE_IMPORT = 'import';
    private static string $AT_RULE_CHARSET = 'charset';
    private static string $AT_RULE_LAYER = 'layer';

    public function __construct(private readonly LintConfiguration $lintConfiguration) {}

    public function supports(Token $token): bool
    {
        return $token instanceof AtRuleToken;
    }

    /**
     * @param Token $token
     */
    public function lint(Token $token): Generator
    {
        if (!$token instanceof AtRuleToken) {
            throw new InvalidArgumentException(
                'AtRuleTokenLinter can only lint AtRuleToken'
            );
        }

        $name = $token->getName();
        if (!$name) {
            yield new TokenError(
                LintErrorKey::INVALID_AT_RULE_DECLARATION,
                'At-rule name is empty',
                $token
            );
            return;
        }

        // Check if at-rule exists
        if (!$this->lintConfiguration->atRuleExists($name)) {
            yield new TokenError(
                LintErrorKey::INVALID_AT_RULE_DECLARATION,
                sprintf('Unknown at-rule "%s"', $name),
                $token
            );
            return;
        }

        switch ($name) {
            case self::$AT_RULE_IMPORT:
                yield from $this->validateImportRule($token);
                break;
            case self::$AT_RULE_CHARSET:
                yield from $this->validateCharsetRule($token);
                break;
        }
    }

    private function validateImportRule(AtRuleToken $token): Generator
    {
        // Validate at-rule value if present
        $value = $token->getValue();
        if ($value === null || trim($value) === '') {
            yield new TokenError(
                LintErrorKey::INVALID_AT_RULE_DECLARATION,
                'Import value is empty',
                $token
            );
            return;
        }

        // Parse the import value
        $parts = preg_split('/\s+/', trim($value), 2);
        $url = $parts[0];
        $conditions = $parts[1] ?? '';

        // Validate URL format
        if (!$this->isValidImportUrl($url)) {
            yield new TokenError(
                LintErrorKey::INVALID_AT_RULE_DECLARATION,
                'Import URL must be a quoted string or url() function',
                $token
            );
            return;
        }

        // Validate conditions if present
        if ($conditions !== '' && !$this->isValidImportConditions($conditions)) {
            yield new TokenError(
                LintErrorKey::INVALID_AT_RULE_DECLARATION,
                'Invalid import conditions. Must be a valid media query, supports() condition, or layer() declaration',
                $token
            );
        }
    }

    private function isValidImportUrl(string $url): bool
    {
        // Match either a quoted string or url() function
        return preg_match('/^(["\'][^"\']+["\']|url\(["\']?[^"\'\(\)]+["\']?\))$/', $url) === 1;
    }

    private function isValidImportConditions(string $conditions): bool
    {
        // Basic validation for media queries, supports(), and layer() conditions
        $validPatterns = [
            // Media types and features
            '/^(all|print|screen|speech)(\s+and\s+\([^)]+\))*$/',
            // Supports conditions
            '/^supports\s*\([^)]+\)$/',
            // Layer declaration
            '/^layer\s*\([^)]+\)$/',
            // Combinations
            '/^(layer\s*\([^)]+\)\s+)?((all|print|screen|speech)(\s+and\s+\([^)]+\))*|supports\s*\([^)]+\))$/'
        ];

        foreach ($validPatterns as $pattern) {
            if (preg_match($pattern, trim($conditions))) {
                return true;
            }
        }

        return false;
    }

    private function validateCharsetRule(AtRuleToken $token): Generator
    {
        $value = $token->getValue();
        if ($value === null || !preg_match('/^"[^"]+?"$/', trim($value))) {
            yield new TokenError(
                LintErrorKey::INVALID_AT_RULE_DECLARATION,
                'Charset value must be a quoted string',
                $token
            );
        }
    }
}
