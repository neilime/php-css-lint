<?php

declare(strict_types=1);

namespace CssLint\Token;

use JsonSerializable;

abstract class AbstractToken implements Token
{
    protected static int $UNCOMPLETED_TOKEN_END = -1;

    protected ?Token $parent = null;

    private int $end;

    public function __construct(
        private readonly string $type,
        protected mixed $value,
        private readonly int $line,
        private readonly int $start,
        ?int $end = null
    ) {
        $this->end = $end === null ? self::$UNCOMPLETED_TOKEN_END : $end;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function setEnd(int $end): void
    {
        $this->end = $end;
    }

    /**
     * Returns a JSON serializable representation of the token.
     *
     * @return array{ type: string, value: mixed, line: int, start: int, end: int }
     */
    public function jsonSerialize(): array
    {
        $value = $this->value;
        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        } elseif (is_array($value)) {
            $value = array_map(function ($item) {
                return $item instanceof JsonSerializable ? $item->jsonSerialize() : $item;
            }, $value);
        }

        return [
            'type' => $this->type,
            'value' => $value,
            'line' => $this->line,
            'start' => $this->start,
            'end' => $this->end,
        ];
    }

    public function isComplete(): bool
    {
        return $this->end !== static::$UNCOMPLETED_TOKEN_END;
    }

    public function setParent(?Token $parent): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?Token
    {
        return $this->parent;
    }

    public function getRoot(): Token
    {
        $current = $this;
        while ($current->getParent() !== null) {
            $current = $current->getParent();
        }
        return $current;
    }
}
