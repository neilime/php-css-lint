<?php

declare(strict_types=1);

namespace Tests\TestSuite\Output;

use CssLint\Output\StdoutOutput;
use Tests\TestSuite\TestCase;

class StdoutOutputTest extends TestCase
{
    public function testWrite(): void
    {
        $output = new StdoutOutput();

        $this->expectOutputString('Hello, World!');
        $output->write('Hello, World!');
    }

    public function testWriteMultipleContents(): void
    {
        $output = new StdoutOutput();

        $this->expectOutputString('First Second Third');
        $output->write('First ');
        $output->write('Second ');
        $output->write('Third');
    }

    public function testWriteLine(): void
    {
        $output = new StdoutOutput();

        $expected = 'Line 1' . PHP_EOL . 'Line 2' . PHP_EOL;
        $this->expectOutputString($expected);

        $output->writeLine('Line 1');
        $output->writeLine('Line 2');
    }

    public function testWriteEmptyString(): void
    {
        $output = new StdoutOutput();

        $this->expectOutputString('');
        $output->write('');
    }

    public function testWriteLineEmptyString(): void
    {
        $output = new StdoutOutput();

        $this->expectOutputString(PHP_EOL);
        $output->writeLine('');
    }

    public function testWriteSpecialCharacters(): void
    {
        $output = new StdoutOutput();

        $specialContent = "Special chars: \n\t\r àéîöü";
        $this->expectOutputString($specialContent);
        $output->write($specialContent);
    }

    public function testWriteUnicodeContent(): void
    {
        $output = new StdoutOutput();

        $unicodeContent = 'Unicode: 中文 العربية русский 日本語';
        $this->expectOutputString($unicodeContent . PHP_EOL);
        $output->writeLine($unicodeContent);
    }

    public function testWriteWithControlCharacters(): void
    {
        $output = new StdoutOutput();

        $controlContent = "Control chars: \x1B[31mRed\x1B[0m \x1B[32mGreen\x1B[0m";
        $this->expectOutputString($controlContent);
        $output->write($controlContent);
    }

    public function testCombinedWriteAndWriteLine(): void
    {
        $output = new StdoutOutput();

        $expected = 'Start' . 'Middle' . PHP_EOL . 'End';
        $this->expectOutputString($expected);

        $output->write('Start');
        $output->write('Middle');
        $output->writeLine('');
        $output->write('End');
    }

    public function testLargeContent(): void
    {
        $output = new StdoutOutput();

        // Test with a large string
        $largeContent = str_repeat('Large content line ' . PHP_EOL, 1000);
        $this->expectOutputString($largeContent);
        $output->write($largeContent);
    }

    public function testConsecutiveWrites(): void
    {
        $output = new StdoutOutput();

        $expected = '';
        for ($i = 0; $i < 100; $i++) {
            $expected .= "Line $i" . PHP_EOL;
        }

        $this->expectOutputString($expected);

        for ($i = 0; $i < 100; $i++) {
            $output->writeLine("Line $i");
        }
    }
}
