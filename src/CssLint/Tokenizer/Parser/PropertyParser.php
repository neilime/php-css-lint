<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\LintErrorKey;
use CssLint\Token\PropertyToken;
use CssLint\Tokenizer\TokenizerContext;
use CssLint\Token\Token;
use CssLint\TokenLinter\TokenError;

/**
 * @extends AbstractParser<PropertyToken>
 */
class PropertyParser extends AbstractParser
{
    /**
     * CSS property names can include:
     * - Letters a-z, A-Z
     * - Numbers 0-9
     * - Hyphens and underscores
     * - Custom property prefix --
     * @var non-empty-string
     */
    private static string $PROPERTY_NAME_PATTERN = '/^-{0,2}[a-zA-Z][a-zA-Z0-9-_]*\s*:$/';

    /**
     * @var non-empty-string
     */
    private static string $PROPERTY_SEPARATOR = ':';

    /**
     * @var non-empty-string
     */
    private static string $PROPERTY_END = ';';

    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->lastCharIsSpace($tokenizerContext)) {
            return null;
        }

        // Property token is only valid in a block token context
        if ($tokenizerContext->getCurrentBlockToken() === null) {
            return null;
        }

        return $this->handleTokenForCurrentContext(
            $tokenizerContext,
            function (?PropertyToken $currentPropertyToken = null) use ($tokenizerContext) {
                if ($currentPropertyToken === null) {
                    if (!$this->isPropertyName($tokenizerContext)) {
                        return null;
                    }

                    return $this->createPropertyToken($tokenizerContext);
                }

                if ($this->isPropertyEnd($tokenizerContext)) {
                    $currentPropertyToken = $this->updatePropertyToken($tokenizerContext, $currentPropertyToken);
                    return $currentPropertyToken;
                }

                return null;
            }
        );
    }

    private function isPropertyName(TokenizerContext $tokenizerContext): bool
    {
        $currentContent = trim($tokenizerContext->getCurrentContent());
        return preg_match(self::$PROPERTY_NAME_PATTERN, $currentContent) === 1;
    }

    private function isPropertyEnd(TokenizerContext $tokenizerContext): bool
    {
        foreach (
            [
                self::$PROPERTY_END,
                BlockParser::$BLOCK_END,
            ] as $endChar
        ) {
            if ($tokenizerContext->currentContentEndsWith($endChar)) {
                return true;
            }
        }

        return false;
    }

    private function createPropertyToken(TokenizerContext $tokenizerContext): PropertyToken
    {
        return new ($this->getHandledTokenClass())(
            trim($tokenizerContext->getCurrentContent()),
            null,
            PropertyToken::calculateStartPosition($tokenizerContext),
        );
    }

    private function updatePropertyToken(TokenizerContext $tokenizerContext, PropertyToken $propertyToken): PropertyToken|TokenError
    {
        $currentContent = $tokenizerContext->getCurrentContent();

        foreach (
            [
                self::$PROPERTY_END,
                BlockParser::$BLOCK_END,
            ] as $endChar
        ) {
            $currentContent = self::removeEndingString($currentContent, $endChar);
        }

        $parts = array_map(
            trim(...),
            explode(
                self::$PROPERTY_SEPARATOR,
                $currentContent,
                2
            )
        );

        if (count($parts) !== 2) {
            return new TokenError(
                LintErrorKey::INVALID_PROPERTY_DECLARATION,
                sprintf('Invalid property declaration, missing separator: %s', $currentContent),
                $propertyToken
            );
        }
        [$name, $value] = $parts;

        $propertyToken
            ->setName($name)
            ->setValue($value);

        return $propertyToken;
    }

    public function getHandledTokenClass(): string
    {
        return PropertyToken::class;
    }
}
