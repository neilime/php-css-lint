<?php

declare(strict_types=1);

namespace CssLint\Tokenizer;

use CssLint\LintError;
use CssLint\Position;
use CssLint\Token\BlockToken;
use CssLint\Token\Token;

enum LintContextName: string
{
    case CONTEXT_SELECTOR = 'selector';
    case CONTEXT_SELECTOR_CONTENT = 'selector content';
    case CONTEXT_NESTED_SELECTOR_CONTENT = 'nested selector content';
    case CONTEXT_PROPERTY_NAME = 'property name';
    case CONTEXT_PROPERTY_CONTENT = 'property content';
}

/**
 * @phpstan-type Errors array<array-key, LintError>
 */
class TokenizerContext
{
    /**
     * Current content of parse. Ex: the selector name, the property name or the property content
     */
    private string $currentContent = '';

    /**
     * Current position of the tokenizer
     */
    private ?Position $currentPosition = null;

    /**
     * Current token being processed
     */
    private ?Token $currentToken = null;

    /**
     * Previous token being processed
     */
    private ?Token $previousToken = null;

    /**
     * Current block token being processed
     */
    private ?BlockToken $currentBlockToken = null;

    /**
     * Return context content
     */
    public function getCurrentContent(): string
    {
        return $this->currentContent;
    }

    /**
     * Append new value to context content
     */
    public function appendCurrentContent(string $currentContent): self
    {
        $this->currentContent .= $currentContent;
        return $this;
    }

    /**
     * Reset current content property
     */
    public function resetCurrentContent(): self
    {
        $this->currentContent = '';
        return $this;
    }

    /**
     * Get the last char of the current content
     */
    public function getLastChar(): ?string
    {
        return $this->getNthLastChars(1);
    }

    /**
     * Get the nth last char of the current content
     * @param int<1, max> $length
     * @param int<0, max> $offset
     */
    public function getNthLastChars(int $length, int $offset = 0): ?string
    {
        if (!$this->currentContent) {
            return null;
        }

        $contentLength = strlen($this->currentContent);

        $offset = $contentLength - $offset - $length;

        if ($offset < 0) {
            return null;
        }

        return substr($this->currentContent, $offset, $length);
    }

    public function currentContentEndsWith(string $string): bool
    {
        return str_ends_with($this->currentContent, $string);
    }

    public function getCurrentPosition(): Position
    {
        if ($this->currentPosition === null) {
            $this->currentPosition = new Position();
        }

        return $this->currentPosition;
    }

    public function incrementColumn(): self
    {
        $currentPosition = $this->getCurrentPosition();

        $this->currentPosition = new Position(
            $currentPosition->getLine(),
            $currentPosition->getColumn() + 1,
        );
        return $this;
    }

    public function incrementLine(): self
    {
        $currentPosition = $this->getCurrentPosition();

        $this->currentPosition = new Position(
            $currentPosition->getLine() + 1
        );
        return $this;
    }

    /**
     * Get the current token being processed
     */
    public function getCurrentToken(): ?Token
    {
        return $this->currentToken;
    }

    /**
     * Reset current token property
     */
    public function resetCurrentToken(): self
    {
        return $this->setCurrentToken(null);
    }

    /**
     * Set new current token
     */
    public function setCurrentToken(?Token $currentToken): self
    {
        $this->previousToken = $this->currentToken;
        $this->currentToken = $currentToken;
        return $this;
    }

    /**
     * Assert that current token is the same type as given token
     * @param class-string<Token>|null $token
     * @phpstan-assert-if-true Token $this->currentToken
     * @return bool
     */
    public function assertCurrentToken(?string $token): bool
    {
        if ($token === null) {
            return $this->currentToken === null;
        }

        if ($this->currentToken === null) {
            return false;
        }

        return $this->currentToken::class === $token;
    }

    public function getPreviousToken(): ?Token
    {
        return $this->previousToken;
    }

    public function getCurrentBlockToken(): ?BlockToken
    {
        return $this->currentBlockToken;
    }

    public function setCurrentBlockToken(?BlockToken $currentBlockToken): self
    {
        $this->currentBlockToken = $currentBlockToken;
        return $this;
    }
}
