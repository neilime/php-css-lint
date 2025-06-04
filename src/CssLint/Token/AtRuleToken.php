<?php

declare(strict_types=1);

namespace CssLint\Token;

use CssLint\Position;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @phpstan-extends AbstractToken<array{name: string, value: string|null, isBlock: bool}, string|null>
 */
class AtRuleToken extends AbstractToken implements TokenBoundary
{
    /**
     * Constructs an AtRuleToken.
     *
     * @param string $name The at-rule name (without the @ symbol)
     * @param string|null $value The at-rule value/parameters
     * @param Position $start The start position of the at-rule in the source
     * @param ?Position $end The end position of the at-rule in the source
     */
    public function __construct(string $name, ?string $value, Position $start, ?Position $end = null)
    {
        parent::__construct('at-rule', ['name' => $name, 'value' => $value, 'isBlock' => false], $start, $end);
    }

    /**
     * @param class-string<Token> $tokenClass
     */
    public function canTransitionTo(string $tokenClass, TokenizerContext $tokenizerContext): bool
    {
        return $this->isBlock() && $tokenClass === BlockToken::class;
    }

    /**
     * Gets the at-rule name.
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
     * Gets the at-rule value/parameters.
     */
    public function getValue(): ?string
    {
        return $this->data['value'];
    }

    public function setValue(?string $value): self
    {
        $this->data['value'] = $value;
        return $this;
    }

    public function isBlock(): bool
    {
        return !!$this->data['isBlock'];
    }

    public function setIsBlock(bool $isBlock): self
    {
        $this->data['isBlock'] = $isBlock;
        return $this;
    }
}
