<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\BlockToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @template TToken of Token = Token
 * @implements Parser<TToken>
 */
abstract class AbstractParser implements Parser
{
    /**
     * @var non-empty-string[]
     */
    public static array $END_OF_LINE_CHARS = ["\r\n", "\n"];

    /**
     * Check if a given char is an end of line token
     * @param int<0, max> $offset
     * @return boolean : true if the char is an end of line token, else false
     */
    protected function isEndOfLineChar(TokenizerContext $tokenizerContext, int $offset = 0): bool
    {
        foreach (self::$END_OF_LINE_CHARS as $endOfLineChar) {
            if ($tokenizerContext->getNthLastChars(strlen($endOfLineChar), $offset) === $endOfLineChar) {
                return true;
            }
        }

        return false;
    }

    protected function removeStartingString(string $content, string $search): string
    {
        return substr($content, strlen($search));
    }

    protected function removeEndingString(string $content, string $search): string
    {
        return substr($content, 0, -strlen($search));
    }

    protected function lastCharIsSpace(TokenizerContext $tokenizerContext): bool
    {
        $lastChar = $tokenizerContext->getLastChar();
        return $lastChar !== null && $this->stringIsSpace($lastChar);
    }

    protected function stringIsSpace(string $char): bool
    {
        return ctype_space($char);
    }

    /**
     * @param callable(?TToken): (TToken|LintError|null) $generateToken
     * @return TToken|LintError|null
     */
    protected function provideToken(TokenizerContext $tokenizerContext, callable $generateToken): Token|LintError|null
    {
        $currentParentToken = $this->getCurrentParentToken($tokenizerContext);
        if (!$this->shouldHandleCurrentParentToken($tokenizerContext, $currentParentToken)) {
            return null;
        }

        $tokenOrError = call_user_func($generateToken, $currentParentToken);
        if ($tokenOrError === null || $tokenOrError instanceof LintError) {
            return $tokenOrError;
        }

        $token = $tokenOrError;
        if ($currentParentToken === null) {
            $this->injectTokenIntoCurrentParent($tokenizerContext, $token);
            return null;
        }

        $end = $token::class::calculateEndPosition($tokenizerContext, $token);
        $token->setEnd($end);
        return $token;
    }

    private function getCurrentParentToken(TokenizerContext $tokenizerContext): ?Token
    {
        if (!$tokenizerContext->assertCurrentToken(BlockToken::class)) {
            return $tokenizerContext->getCurrentToken();
        }

        /** @var BlockToken $blockToken */
        $blockToken = $tokenizerContext->getCurrentToken();
        return $blockToken->getBlockCurrentToken();
    }

    /**
     * @phpstan-assert-if-true ?TToken $currentParentToken
     */
    private function shouldHandleCurrentParentToken(TokenizerContext $tokenizerContext, ?Token $currentParentToken): bool
    {
        return $currentParentToken === null || $currentParentToken::class === $this->getHandledTokenClass();
    }

    /**
     * @param TToken $token
     */
    private function injectTokenIntoCurrentParent(TokenizerContext $tokenizerContext, Token $token): void
    {
        if (!$tokenizerContext->assertCurrentToken(BlockToken::class)) {
            $tokenizerContext->setCurrentToken($token);
            return;
        }

        /** @var BlockToken $blockToken */
        $blockToken = $tokenizerContext->getCurrentToken();
        $blockToken->addToken($token);
    }
}
