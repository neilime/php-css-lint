<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
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

    protected static function removeStartingString(string $content, string $search): string
    {
        if (str_starts_with($content, $search)) {
            return substr($content, strlen($search));
        }

        return $content;
    }

    protected static function removeEndingString(string $content, string $search): string
    {
        if (str_ends_with($content, $search)) {
            return substr($content, 0, -strlen($search));
        }

        return $content;
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
    protected function handleTokenForCurrentContext(TokenizerContext $tokenizerContext, callable $generateToken): Token|LintError|null
    {
        $currentParsingToken = $tokenizerContext->getCurrentToken();
        if (!$this->shouldHandleCurrentParsingToken($currentParsingToken)) {
            return null;
        }

        $tokenOrError = call_user_func($generateToken, $currentParsingToken);
        if ($tokenOrError === null || $tokenOrError instanceof LintError) {
            return $tokenOrError;
        }

        $token = $tokenOrError;
        if ($currentParsingToken === null) {
            $this->injectTokenIntoCurrentParent($tokenizerContext, $token);
            return null;
        }

        $end = $token::class::calculateEndPosition($tokenizerContext, $token);
        $token->setEnd($end);

        return $token;
    }

    /**
     * @phpstan-assert-if-true ?TToken $currentParsingToken
     */
    private function shouldHandleCurrentParsingToken(?Token $currentParsingToken): bool
    {
        return $currentParsingToken === null || $currentParsingToken::class === $this->getHandledTokenClass();
    }

    /**
     * @param TToken $token
     */
    private function injectTokenIntoCurrentParent(TokenizerContext $tokenizerContext, Token $token): void
    {
        $currentBlockToken = $tokenizerContext->getCurrentBlockToken();
        if ($currentBlockToken !== null) {
            $currentBlockToken->addToken($token);
        }

        $tokenizerContext->setCurrentToken($token);
    }
}
