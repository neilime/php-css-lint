<?php

declare(strict_types=1);

namespace CssLint;

use JsonSerializable;

/**
 * @phpstan-type SerializedPosition array{ line: int<1, max>, column: int<1, max> }
 */
class Position implements JsonSerializable
{
    /**
     * @param int<1, max> $line
     * @param int<1, max> $column
     */
    public function __construct(
        private readonly int $line = 1,
        private readonly int $column = 1,
    ) {}

    /**
     * @return int<1, max>
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return int<1, max>
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * @return SerializedPosition
     */
    public function jsonSerialize(): array
    {
        return [
            'line' => $this->line,
            'column' => $this->column,
        ];
    }
}
