<?php

declare(strict_types=1);

namespace CssLint;

enum LintContextName: string
{
    case CONTEXT_SELECTOR = 'selector';
    case CONTEXT_SELECTOR_CONTENT = 'selector content';
    case CONTEXT_NESTED_SELECTOR_CONTENT = 'nested selector content';
    case CONTEXT_PROPERTY_NAME = 'property name';
    case CONTEXT_PROPERTY_CONTENT = 'property content';
}

/**
 * @package CssLint
 * @phpstan-type Errors array<string>
 * @phpstan-type ContextEntry string|null
 * @phpstan-type Context ContextEntry|ContextEntry[]
 */
class LintContext
{
    /**
     * Errors occurred during the lint process
     * @var Errors
     */
    protected $errors = [];

    /**
     * Current line number
     * @var int
     */
    protected $lineNumber = 0;

    /**
     * Current char number
     * @var int
     */
    protected $charNumber = 0;

    /**
     * Current context name of parsing
     */
    private ?LintContextName $lintContextName = null;

    /**
     * Current content of parse. Ex: the selector name, the property name or the property content
     */
    private string $currentContent = '';

    /**
     * The previous linted char
     */
    private ?string $previousChar = null;

    /**
     * Tells if the linter is parsing a nested selector. Ex: @media, @keyframes...
     */
    private int $nestedSelectorLevel = 0;

    /**
     * Tells if the linter is parsing a comment
     */
    private bool $comment = false;

    public function getCurrentContext(): ?LintContextName
    {
        return $this->lintContextName;
    }

    /**
     * Reset context property
     */
    public function resetCurrentContext(): self
    {
        return $this->setCurrentContext(null);
    }

    /**
     * Set new context
     */
    public function setCurrentContext(?LintContextName $lintContextName): self
    {
        $this->lintContextName = $lintContextName;
        $this->currentContent = '';
        return $this;
    }

    /**
     * Assert that current context is the same as given
     */
    public function assertCurrentContext(?LintContextName $lintContextName): bool
    {
        return $this->lintContextName === $lintContextName;
    }

    /**
     * Tells if the linter is parsing a comment
     */
    public function isComment(): bool
    {
        return $this->comment;
    }

    /**
     * Set the comment flag
     */
    public function setComment(bool $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * Assert that previous char is the same as given
     */
    public function assertPreviousChar(string $charValue): bool
    {
        return $this->previousChar === $charValue;
    }

    /**
     * Set new previous char
     */
    public function setPreviousChar(string $charValue): self
    {
        $this->previousChar = $charValue;
        return $this;
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
     * Tells if the linter is parsing a nested selector
     */
    public function isNestedSelector(): bool
    {
        return $this->nestedSelectorLevel > 0;
    }

    /**
     * Increase the nested selector level
     */
    public function incrementNestedSelector(): void
    {
        ++$this->nestedSelectorLevel;
    }

    /**
     * Decrement the nested selector level
     */
    public function decrementNestedSelector(): void
    {
        if ($this->nestedSelectorLevel > 0) {
            --$this->nestedSelectorLevel;
        }
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
     * Add 1 to the current line number
     */
    public function incrementLineNumber(): self
    {
        ++$this->lineNumber;
        return $this;
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
     * Add a new error message to the errors property, it adds extra infos to the given error message
     */
    public function addError(string $error): self
    {
        $this->errors[] = $error . ' (line: ' . $this->lineNumber . ', char: ' . $this->charNumber . ')';
        return $this;
    }

    /**
     * Return the errors occurred during the lint process
     * @return Errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
