<?php

declare(strict_types=1);

namespace CssLint\Tokenizer;

use CssLint\Token\Token;

final readonly class TokenizerContextInspector
{
    public function __construct(private TokenizerContext $tokenizerContext) {}

    public function lastChar(): ?string
    {
        return $this->nthLastChars(1);
    }

    /**
     * @param int<1, max> $length
     * @param int<0, max> $offset
     */
    public function nthLastChars(int $length, int $offset = 0): ?string
    {
        $currentContent = $this->tokenizerContext->getCurrentContent();
        if ($currentContent === '') {
            return null;
        }

        $contentLength = strlen($currentContent);
        $start = $contentLength - $offset - $length;
        if ($start < 0) {
            return null;
        }

        return substr($currentContent, $start, $length);
    }

    public function currentContentEndsWith(string $string): bool
    {
        return str_ends_with($this->tokenizerContext->getCurrentContent(), $string);
    }

    /**
     * @param class-string<Token>|null $tokenClass
     */
    public function currentTokenMatches(?string $tokenClass): bool
    {
        $currentToken = $this->tokenizerContext->getCurrentToken();

        if ($tokenClass === null) {
            return $currentToken === null;
        }

        if ($currentToken === null) {
            return false;
        }

        return $currentToken::class === $tokenClass;
    }

    public function lastCharIsSpace(): bool
    {
        $lastChar = $this->lastChar();
        return $lastChar !== null && TokenizerStringInspector::isSpace($lastChar);
    }

    public function hasOpenStringOrParenthesisContext(): bool
    {
        return TokenizerStringInspector::hasOpenStringOrParenthesisContext($this->tokenizerContext->getCurrentContent());
    }
}
