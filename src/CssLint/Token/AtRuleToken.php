<?php

declare(strict_types=1);

namespace CssLint\Token;

class AtRuleToken extends AbstractToken
{
    /**
     * Constructs an AtRuleToken.
     *
     * @param string $name The at-rule name (without the @ symbol)
     * @param string|null $value The at-rule value/parameters
     * @param int $line The line number where the at-rule starts
     * @param int $start The start position of the at-rule in the source
     * @param int $end The end position of the at-rule in the source
     */
    public function __construct(string $name, ?string $value, int $line, int $start, ?int $end = null)
    {
        parent::__construct('at-rule', ['name' => $name, 'value' => $value], $line, $start, $end);
    }

    /**
     * Gets the at-rule name.
     */
    public function getName(): string
    {
        return $this->value['name'];
    }

    /**
     * Gets the at-rule value/parameters.
     */
    public function getValue(): ?string
    {
        return $this->value['value'];
    }
}
