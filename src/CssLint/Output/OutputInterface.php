<?php

declare(strict_types=1);

namespace CssLint\Output;

/**
 * Interface for output destination abstraction.
 */
interface OutputInterface
{
    /**
     * Write content to the output destination.
     */
    public function write(string $content): void;

    /**
     * Write content to the output destination with a newline.
     */
    public function writeLine(string $content): void;
}
