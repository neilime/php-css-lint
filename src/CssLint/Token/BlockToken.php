<?php

declare(strict_types=1);

namespace CssLint\Token;

class BlockToken extends AbstractToken
{

    /**
     * Constructs a BlockToken.
     *
     * @param array $value The value of the block token, typically an array of properties.
     * @param int $line The line number where the block starts.
     * @param int $start The start position of the block in the source.
     * @param int $end The end position of the block in the source.
     */
    public function __construct(array $value, int $line, int $start, ?int $end = null)
    {
        parent::__construct('block', $value, $line, $start, $end);
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
        $this->setValue($tokens);
    }

    public function getNonCompleteTokens(): array
    {
        $tokens = $this->getValue();
        return array_filter($tokens, fn($token) => !$token->isComplete());
    }
}
