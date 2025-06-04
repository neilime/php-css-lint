<?php

declare(strict_types=1);

namespace CssLint\Token;

use CssLint\Position;

/**
 * @phpstan-extends AbstractToken<Token[], Token[]>
 */
class BlockToken extends AbstractToken
{
    /**
     * Constructs a BlockToken.
     *
     * @param Token[] $value The value of the block token, typically an array of properties.
     * @param Position $start The start position of the block in the source.
     * @param ?Position $end The end position of the block in the source.
     */
    public function __construct(array $value, Position $start, ?Position $end = null)
    {
        parent::__construct('block', $value, $start, $end);
    }

    /**
     * Get the current token of a given class.
     *
     * @return Token|null The current token of the given class, or null if not found.
     */
    public function getBlockCurrentToken(): ?Token
    {
        $tokens = $this->getValue();
        $token = $tokens[count($tokens) - 1] ?? null;

        if ($token && !$token->isComplete()) {
            return $token;
        }

        return null;
    }

    public function addToken(Token $token): void
    {
        $tokens = $this->getValue();
        $tokens[] = $token;
        $token->setParent($this);
        $this->data = $tokens;
    }

    public function getValue(): array
    {
        return $this->data;
    }
}
