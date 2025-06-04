<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\LintErrorKey;
use CssLint\Token\BlockToken;
use CssLint\Token\PropertyToken;
use CssLint\Tokenizer\TokenizerContext;
use CssLint\Token\Token;

/**
 * @extends AbstractParser<PropertyToken>
 */
class PropertyParser extends AbstractParser
{
    private static string $PROPERTY_NAME_PATTERN = '/^[a-zA-Z0-9-_]+$/';
    private static string $PROPERTY_SEPARATOR = ':';
    private static array $PROPERTY_END_CHARS = [';', '\n', '\r\n'];

    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->isSpace($tokenizerContext)) {
            return null;
        }

        // Property token is only valid in a block token context
        if (!$tokenizerContext->assertCurrentToken(BlockToken::class)) {
            return null;
        }

        return $this->provideToken(
            $tokenizerContext,
            function (?BlockToken $blockToken = null, ?PropertyToken $currentPropertyToken = null) use ($tokenizerContext) {
                if (!$blockToken) {
                    return null;
                }

                if ($currentPropertyToken === null) {
                    if (!$this->isPropertyName($tokenizerContext)) {
                        return null;
                    }

                    return $this->createPropertyToken($tokenizerContext);
                }

                if ($this->isPropertyEnd($tokenizerContext)) {
                    return $this->updatePropertyToken($tokenizerContext, $currentPropertyToken);
                }

                return null;
            }
        );

        return null;
    }

    private function isPropertyName(TokenizerContext $tokenizerContext): bool
    {
        $lastChar = $tokenizerContext->getLastChar();
        return $lastChar !== null && preg_match(self::$PROPERTY_NAME_PATTERN, $lastChar) === 1;
    }

    private function isPropertyEnd(TokenizerContext $tokenizerContext): bool
    {
        foreach (self::$PROPERTY_END_CHARS as $endChar) {
            if ($tokenizerContext->getNthLastChars(strlen($endChar)) === $endChar) {
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
            $tokenizerContext->getLineNumber(),
            $tokenizerContext->getCharNumber()
        );
    }

    private function updatePropertyToken(TokenizerContext $tokenizerContext, PropertyToken $propertyToken): LintError|PropertyToken
    {
        $currentContent = $tokenizerContext->getCurrentContent();
        $parts = array_map('trim', explode(self::$PROPERTY_SEPARATOR, rtrim($currentContent, join(self::$PROPERTY_END_CHARS)), 2));

        if (count($parts) !== 2) {
            return new LintError(
                LintErrorKey::INVALID_PROPERTY_DECLARATION,
                sprintf('Invalid property declaration: %s', $currentContent),
                $tokenizerContext->getLineNumber(),
                $tokenizerContext->getCharNumber(),
                $tokenizerContext->getCharNumber() + strlen($currentContent)
            );
        }
        [$name, $value] = $parts;

        $propertyToken->setValue(['name' => $name, 'value' => $value]);
        $propertyToken->setEnd($this->getTokenEnd($propertyToken, $tokenizerContext));
        return $propertyToken;
    }

    public function getHandledTokenClass(): string
    {
        return PropertyToken::class;
    }
}
