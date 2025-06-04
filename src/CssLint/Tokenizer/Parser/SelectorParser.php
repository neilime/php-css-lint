<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\SelectorToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @extends AbstractParser<SelectorToken>
 */
class SelectorParser extends AbstractParser
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
    private static string $SELECTOR_PATTERN = '/^[.#a-zA-Z0-9\[\]=\'"\-_\:>\+~\s\(\)]+\{$/';

    /**
     * Performs parsing tokenizer current context, check comment part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->lastCharIsSpace($tokenizerContext)) {
            return null;
        }

        $token = $this->provideToken(
            $tokenizerContext,
            fn(?SelectorToken $currentToken = null) => $this->handleSelectorToken($tokenizerContext, $currentToken)
        );

        if (BlockParser::isBlockStart($tokenizerContext)) {
            $token = $this->provideToken(
                $tokenizerContext,
                fn(?SelectorToken $currentToken = null) => $this->handleSelectorToken($tokenizerContext, $currentToken)
            );
        }

        return $token;
    }

    private function handleSelectorToken(TokenizerContext $tokenizerContext, ?SelectorToken $currentToken): ?SelectorToken
    {
        if ($currentToken) {
            $currentToken = $this->updateSelectorToken($tokenizerContext, $currentToken);

            // If we encounter a selector block start, we finalize the current selector token
            if (BlockParser::isBlockStart($tokenizerContext)) {
                return $currentToken;
            }
            return null;
        }

        if ($this->isSelector($tokenizerContext)) {
            return $this->createSelectorToken($tokenizerContext);
        }

        return null;
    }

    private function isSelector(TokenizerContext $tokenizerContext): bool
    {
        $currentContent = trim($tokenizerContext->getCurrentContent());
        return preg_match(self::$SELECTOR_PATTERN, $currentContent) === 1;
    }

    private function createSelectorToken(TokenizerContext $tokenizerContext): SelectorToken
    {
        return new ($this->getHandledTokenClass())(
            trim($tokenizerContext->getCurrentContent()),
            SelectorToken::calculateStartPosition($tokenizerContext),
        );
    }

    private function updateSelectorToken(TokenizerContext $tokenizerContext, SelectorToken $selectorToken): SelectorToken
    {
        // remove last character which is the block start
        $value = $tokenizerContext->getCurrentContent();
        $value = trim(rtrim($value, BlockParser::$BLOCK_START));
        $selectorToken->setValue($value);
        return $selectorToken;
    }

    public function getHandledTokenClass(): string
    {
        return SelectorToken::class;
    }
}
