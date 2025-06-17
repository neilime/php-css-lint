<?php

declare(strict_types=1);

namespace CssLint\Formatter;

use RuntimeException;

/**
 * Factory to create FormatterManager based on requested names.
 */
class FormatterFactory
{
    /** @var array<non-empty-string, FormatterInterface> */
    private array $available;

    public function __construct()
    {
        $availableFormatters = [new PlainFormatter()];
        foreach ($availableFormatters as $formatter) {
            $this->available[$formatter->getName()] = $formatter;
        }
    }

    /**
     * Create a FormatterManager based on a comma-separated list of formatter names.
     * @param string|null $formatterArg e.g. 'plain,json'
     * @return FormatterManager
     * @throws RuntimeException on invalid formatter names
     */
    public function create(?string $formatterArg): FormatterManager
    {
        $names = array_filter(array_map('trim', explode(',', (string) $formatterArg)));
        $instances = [];
        $invalid = [];

        $available = $this->getAvailableFormatters();

        foreach ($names as $name) {
            if (in_array($name, $available, true)) {
                $instances[] = $this->available[$name];
            } else {
                $invalid[] = $name;
            }
        }

        if (!empty($invalid)) {
            throw new RuntimeException('Invalid formatter(s): ' . implode(', ', $invalid));
        }

        if (empty($instances)) {
            // Return the first available formatter if none specified
            // If no formatters are available, throw an exception
            if (empty($this->available)) {
                throw new RuntimeException('No formatters available');
            }

            $instances[] = $this->available[array_key_first($this->available)];
        }

        return new FormatterManager($instances);
    }

    /**
     * Get the names of all available formatters.
     * @return non-empty-string[] List of formatter names
     */
    public function getAvailableFormatters(): array
    {
        return array_keys($this->available);
    }
}
