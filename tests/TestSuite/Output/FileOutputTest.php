<?php

declare(strict_types=1);

namespace Tests\TestSuite\Output;

use CssLint\Output\FileOutput;
use RuntimeException;
use Tests\TestSuite\TestCase;

class FileOutputTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir();
    }

    protected function tearDown(): void
    {
        // Clean up any test files
        $pattern = $this->tempDir . '/test_file_output_*.txt';
        foreach (glob($pattern) as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    public function testConstructorCreatesFile(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';

        $output = new FileOutput($filePath);

        $this->assertFileExists($filePath);

        // Clean up
        unset($output);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function testConstructorThrowsExceptionForInvalidPath(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Directory is not writable: /invalid/path');

        new FileOutput('/invalid/path/file.txt');
    }

    public function testWrite(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';
        $output = new FileOutput($filePath);

        $output->write('Hello, World!');

        $content = file_get_contents($filePath);
        $this->assertEquals('Hello, World!', $content);

        // Clean up
        unset($output);
        unlink($filePath);
    }

    public function testWriteMultipleContents(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';
        $output = new FileOutput($filePath);

        $output->write('First ');
        $output->write('Second ');
        $output->write('Third');

        $content = file_get_contents($filePath);
        $this->assertEquals('First Second Third', $content);

        // Clean up
        unset($output);
        unlink($filePath);
    }

    public function testWriteLine(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';
        $output = new FileOutput($filePath);

        $output->writeLine('Line 1');
        $output->writeLine('Line 2');

        $content = file_get_contents($filePath);
        $expected = 'Line 1' . PHP_EOL . 'Line 2' . PHP_EOL;
        $this->assertEquals($expected, $content);

        // Clean up
        unset($output);
        unlink($filePath);
    }

    public function testWriteEmptyString(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';
        $output = new FileOutput($filePath);

        $output->write('');

        $content = file_get_contents($filePath);
        $this->assertEquals('', $content);

        // Clean up
        unset($output);
        unlink($filePath);
    }

    public function testWriteLineEmptyString(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';
        $output = new FileOutput($filePath);

        $output->writeLine('');

        $content = file_get_contents($filePath);
        $this->assertEquals(PHP_EOL, $content);

        // Clean up
        unset($output);
        unlink($filePath);
    }

    public function testFlush(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';
        $output = new FileOutput($filePath);

        $output->write('Test content');

        // After flush, content should definitely be written
        $content = file_get_contents($filePath);
        $this->assertEquals('Test content', $content);

        // Clean up
        unset($output);
        unlink($filePath);
    }

    public function testDestructorClosesFile(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';

        $output = new FileOutput($filePath);
        $output->write('Test content');

        // Explicitly destroy the object
        unset($output);

        // File should be closed and content written
        $this->assertFileExists($filePath);
        $content = file_get_contents($filePath);
        $this->assertEquals('Test content', $content);

        // Clean up
        unlink($filePath);
    }

    public function testWriteSpecialCharacters(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';
        $output = new FileOutput($filePath);

        $specialContent = "Special chars: \n\t\r\0\x0B àéîöü 🚀";
        $output->write($specialContent);

        $content = file_get_contents($filePath);
        $this->assertEquals($specialContent, $content);

        // Clean up
        unset($output);
        unlink($filePath);
    }

    public function testWriteUnicodeContent(): void
    {
        $filePath = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';
        $output = new FileOutput($filePath);

        $unicodeContent = 'Unicode: 中文 العربية русский 日本語';
        $output->writeLine($unicodeContent);

        $content = file_get_contents($filePath);
        $this->assertEquals($unicodeContent . PHP_EOL, $content);

        // Clean up
        unset($output);
        unlink($filePath);
    }
}
