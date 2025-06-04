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
    private static string $SELECTOR_PATTERN = '/^[.a-zA-Z0-9-_]+$/';
    private static string $SELECTOR_BLOCK_START = '{';

    /**
     * Performs parsing tokenizer current context, check comment part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->isSpace($tokenizerContext)) {
            return null;
        }

        $currentTokenIsSelector = $tokenizerContext->assertCurrentToken($this->getHandledTokenClass());

        if (!$currentTokenIsSelector && !$tokenizerContext->assertCurrentToken(null)) {
            // If we are in a different token context, we do not handle selectors
            return null;
        }

        if (!$currentTokenIsSelector) {
            if ($this->isSelector($tokenizerContext)) {
                $selectorTokenOrError = $this->createSelectorToken($tokenizerContext);

                if ($selectorTokenOrError instanceof LintError) {
                    return $selectorTokenOrError;
                }

                $tokenizerContext->setCurrentToken($selectorTokenOrError);
            }
            return null;
        }

        // If we encounter a selector block start, we finalize the current selector token
        if ($this->isSelectorBlockStart($tokenizerContext)) {
            $token = $this->createSelectorToken($tokenizerContext);
            $token->setEnd($this->getTokenEnd($token, $tokenizerContext));
            return $token;
        }

        return null;
    }

    private function isSelector(TokenizerContext $tokenizerContext): bool
    {
        $lastChar = $tokenizerContext->getLastChar();

        return $lastChar !== null && preg_match(self::$SELECTOR_PATTERN, $lastChar) === 1;
    }

    /**
     * Check if the current char is the start of a selector block
     */
    private function isSelectorBlockStart(TokenizerContext $tokenizerContext): bool
    {
        return str_ends_with($tokenizerContext->getCurrentContent(), self::$SELECTOR_BLOCK_START);
    }

    private function createSelectorToken(TokenizerContext $tokenizerContext): SelectorToken|LintError|null
    {
        // remove last character which is the block start
        $value = $tokenizerContext->getCurrentContent();
        $value = trim(substr($value, 0, -1));

        $end = $tokenizerContext->getCharNumber() - 1;
        $start = $end - strlen($value);
        return new ($this->getHandledTokenClass())(
            $value,
            $tokenizerContext->getLineNumber(),
            $start,
            0
        );
    }

    public function getHandledTokenClass(): string
    {
        return SelectorToken::class;
    }
}
