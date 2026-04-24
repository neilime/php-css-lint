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
