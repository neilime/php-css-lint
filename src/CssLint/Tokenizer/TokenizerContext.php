<?php

declare(strict_types=1);

namespace CssLint\Tokenizer;

use CssLint\LintError;
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
     * Current line number
     * @var int
     */
    private $lineNumber = 0;

    /**
     * Current char number
     * @var int
     */
    private $charNumber = 0;

    /**
     * Current content of parse. Ex: the selector name, the property name or the property content
     */
    private string $currentContent = '';

    /**
     * Current token being processed
     */
    private ?Token $currentToken = null;

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
        $this->currentToken = $currentToken;
        return $this;
    }

    /**
     * Assert that current token is the same type as given token
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

        return get_class($this->currentToken) === $token;
    }

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
     */
    public function getNthLastChars(int $charsNumber): ?string
    {
        if (!$this->currentContent) {
            return null;
        }

        $contentLength = strlen($this->currentContent);

        if ($contentLength < $charsNumber) {
            return null;
        }


        return $this->currentContent[$contentLength - $charsNumber];
    }

    public function getCharNumber(): int
    {
        return $this->charNumber;
    }

    /**
     * Add 1 to the current char number
     */
    public function incrementCharNumber(): self
    {
        ++$this->charNumber;
        return $this;
    }

    /**
     * Reset current char number property
     */
    public function resetCharNumber(): self
    {
        $this->charNumber = 0;
        return $this;
    }

    /**
     * Get the current line number
     */
    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    /**
     * Add 1 to the current line number
     */
    public function incrementLineNumber(): self
    {
        ++$this->lineNumber;
        return $this;
    }
}
