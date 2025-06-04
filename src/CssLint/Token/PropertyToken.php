<?php

declare(strict_types=1);

namespace CssLint\Token;

use CssLint\Position;
use CssLint\Tokenizer\Parser\BlockParser;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @phpstan-extends AbstractToken<array{name: string, value: string|null}, string|null>
 */
class PropertyToken extends AbstractToken implements TokenBoundary
{
    /**
     * Constructs a PropertyToken.
     *
     * @param string $name The property name
     * @param string $value The property value
     * @param Position $start The start position of the property in the source
     * @param ?Position $end The end position of the property in the source
     */
    public function __construct(string $name, ?string $value, Position $start, ?Position $end = null)
    {
        parent::__construct('property', ['name' => $name, 'value' => $value], $start, $end);
    }

    public function canTransitionTo(string $tokenClass, TokenizerContext $tokenizerContext): bool
    {
        return $tokenClass === BlockToken::class
            && $tokenizerContext->currentContentEndsWith(BlockParser::$BLOCK_END);
    }

    /**
     * Gets the property name.
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    public function setName(string $name): self
    {
        $this->data['name'] = $name;
        return $this;
    }

    /**
     * Gets the property value.
     */
    public function getValue(): ?string
    {
        return $this->data['value'];
    }

    public function setValue(string $value): self
    {
        $this->data['value'] = $value;
        return $this;
    }

    public function isVariable(): bool
    {
        return str_starts_with($this->getName(), '--');
    }
}
