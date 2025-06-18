<?php

declare(strict_types=1);

namespace CssLint\Output;

/**
 * Standard output implementation that writes to stdout.
 */
class StdoutOutput implements OutputInterface
{
    public function write(string $content): void
    {
        echo $content;
    }

    public function writeLine(string $content): void
    {
        echo $content . PHP_EOL;
    }
}
