<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\SelectorToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;
use CssLint\TokenLinter\SelectorTokenLinter;

/**
 * @extends AbstractParser<SelectorToken>
 */
class SelectorParser extends AbstractParser
{
    /**
     * Performs parsing tokenizer current context, check comment part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->lastCharIsSpace($tokenizerContext)) {
            return null;
        }

        $token = $this->handleTokenForCurrentContext(
            $tokenizerContext,
            fn(?SelectorToken $currentToken = null) => $this->handleSelectorToken($tokenizerContext, $currentToken)
        );

        if (BlockParser::isBlockStart($tokenizerContext)) {
            $token = $this->handleTokenForCurrentContext(
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
        if (!$tokenizerContext->currentContentEndsWith(BlockParser::$BLOCK_START)) {
            return false;
        }

        $selectorValue = $this->getSelectorValue($tokenizerContext);
        return preg_match(SelectorTokenLinter::$SELECTOR_PATTERN, $selectorValue) === 1;
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
        $selectorToken->setValue($this->getSelectorValue($tokenizerContext));
        return $selectorToken;
    }

    private function getSelectorValue(TokenizerContext $tokenizerContext): string
    {
        $value = $tokenizerContext->getCurrentContent();
        $value = trim(self::removeEndingString($value, BlockParser::$BLOCK_START));
        return $value;
    }

    public function getHandledTokenClass(): string
    {
        return SelectorToken::class;
    }
}
