<?php

declare(strict_types=1);

namespace CssLint\Token;

class PropertyToken extends AbstractToken
{
    /**
     * Constructs a PropertyToken.
     *
     * @param string $name The property name
     * @param string $value The property value
     * @param int $line The line number where the property starts
     * @param int $start The start position of the property in the source
     * @param int $end The end position of the property in the source
     */
    public function __construct(string $name, ?string $value, int $line, int $start, ?int $end = null)
    {
        parent::__construct('property', ['name' => $name, 'value' => $value], $line, $start, $end);
    }

    /**
     * Gets the property name.
     */
    public function getName(): string
    {
        return $this->value['name'];
    }

    /**
     * Gets the property value.
     */
    public function getValue(): ?string
    {
        return $this->value['value'];
    }

    public function isVariable(): bool
    {
        return str_starts_with($this->getName(), '--');
    }
}
