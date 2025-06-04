<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\Token;
use CssLint\Token\WhitespaceToken;
use CssLint\Tokenizer\TokenizerContext;
use LogicException;

/**
 * @extends AbstractParser<WhitespaceToken>
 */
class WhitespaceParser extends AbstractParser
{
    /**
     * @return class-string<WhitespaceToken>
     */
    public function getHandledTokenClass(): string
    {
        return WhitespaceToken::class;
    }

    /**
     * Performs parsing tokenizer current context, check end of line part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): WhitespaceToken|LintError|null
    {
        return $this->handleTokenForCurrentContext(
            $tokenizerContext,
            fn(?WhitespaceToken $currentWhitespaceToken = null) => $this->handleWhitespaceToken($tokenizerContext, $currentWhitespaceToken)
        );
    }

    private function handleWhitespaceToken(TokenizerContext $tokenizerContext, ?WhitespaceToken $currentWhitespaceToken): ?WhitespaceToken
    {
        if ($currentWhitespaceToken) {
            $currentWhitespaceToken = $this->updateWhitespaceToken($tokenizerContext, $currentWhitespaceToken);
            // If we encounter an another char than a space, we finalize the current whitespace token
            if (!$this->lastCharIsSpace($tokenizerContext)) {
                return $currentWhitespaceToken;
            }
            return null;
        }

        // If we're starting a new whitespace sequence
        if ($this->isNewLineWhitespace($tokenizerContext)) {
            return $this->createWhitespaceToken($tokenizerContext);
        }

        return null;
    }

    private function isNewLineWhitespace(TokenizerContext $tokenizerContext): bool
    {
        $content = $tokenizerContext->getCurrentContent();

        foreach (self::$END_OF_LINE_CHARS as $endOfLineChar) {
            if (str_starts_with($content, $endOfLineChar)) {
                $content = $this->removeStartingString($content, $endOfLineChar);
                if ($content === '') {
                    return false;
                }
                if ($this->stringIsSpace($content)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function createWhitespaceToken(TokenizerContext $tokenizerContext): WhitespaceToken
    {
        $lastChar = $tokenizerContext->getLastChar();
        if ($lastChar === null) {
            throw new LogicException('Last char is null');
        }

        return new ($this->getHandledTokenClass())(
            $lastChar,
            WhitespaceToken::calculateStartPosition($tokenizerContext),
        );
    }

    private function updateWhitespaceToken(TokenizerContext $tokenizerContext, WhitespaceToken $currentWhitespaceToken): WhitespaceToken
    {
        $content = $tokenizerContext->getCurrentContent();
        $content = str_replace(self::$END_OF_LINE_CHARS, '', $content);

        if (!$this->lastCharIsSpace($tokenizerContext)) {
            $lastChar = $tokenizerContext->getLastChar();
            if ($lastChar !== null) {
                $content = $this->removeEndingString($content, $lastChar);
            }
        }
        $currentWhitespaceToken->setValue($content);
        return $currentWhitespaceToken;
    }
}
