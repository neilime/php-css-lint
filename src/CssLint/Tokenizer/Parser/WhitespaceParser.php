<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\BlockToken;
use CssLint\Token\WhitespaceToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @extends AbstractParser<WhitespaceToken>
 */
class WhitespaceParser extends AbstractParser
{
    /**
     * Performs parsing tokenizer current context, check whitespace part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        return $this->provideToken(
            $tokenizerContext,
            function (?BlockToken $blockToken = null, ?WhitespaceToken $currentWhitespaceToken = null) use ($tokenizerContext) {
                if (!$blockToken) {
                    if ($tokenizerContext->assertCurrentToken(null) || $tokenizerContext->assertCurrentToken($this->getHandledTokenClass())) {
                        return $this->handleWhitespaceToken($tokenizerContext, $tokenizerContext->getCurrentToken());
                    }
                    return null;
                }
                return $this->handleWhitespaceToken($tokenizerContext, $currentWhitespaceToken);
            }
        );
    }

    private function handleWhitespaceToken(TokenizerContext $tokenizerContext, ?WhitespaceToken $currentWhitespaceToken): ?WhitespaceToken
    {
        // If we're already in a whitespace token context, accumulate the whitespace
        if ($currentWhitespaceToken) {
            if (!$this->isSpace($tokenizerContext)) {
                return $currentWhitespaceToken;
            }

            $currentWhitespaceToken->setValue($currentWhitespaceToken->getValue() . $tokenizerContext->getLastChar());
            $currentWhitespaceToken->setEnd($tokenizerContext->getCharNumber());
            return null;
        }

        // If we're starting a new whitespace sequence
        if ($this->isSpace($tokenizerContext)) {
            return $this->createWhitespaceToken($tokenizerContext);
        }

        return null;
    }

    private function createWhitespaceToken(TokenizerContext $tokenizerContext): WhitespaceToken
    {
        return new ($this->getHandledTokenClass())(
            $tokenizerContext->getLastChar(),
            $tokenizerContext->getLineNumber(),
            $tokenizerContext->getCharNumber(),
            0
        );
    }

    public function getHandledTokenClass(): string
    {
        return WhitespaceToken::class;
    }
}
