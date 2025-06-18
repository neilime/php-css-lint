<?php

declare(strict_types=1);

namespace CssLint\Output;

use RuntimeException;

/**
 * File output implementation that writes to a file.
 */
class FileOutput implements OutputInterface
{
    /** @var resource */
    private $fileHandle;

    public function __construct(string $filePath)
    {
        if (!is_writable(dirname($filePath))) {
            throw new RuntimeException("Directory is not writable: " . dirname($filePath));
        }

        $fileHandle = fopen($filePath, 'w');

        if ($fileHandle === false) {
            throw new RuntimeException("Cannot open file for writing: {$filePath}");
        }

        $this->fileHandle = $fileHandle;
    }

    public function write(string $content): void
    {
        if (fwrite($this->fileHandle, $content) === false) {
            throw new RuntimeException('Failed to write to file');
        }
    }

    public function writeLine(string $content): void
    {
        $this->write($content . PHP_EOL);
    }

    public function __destruct()
    {
        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
        }
    }
}
