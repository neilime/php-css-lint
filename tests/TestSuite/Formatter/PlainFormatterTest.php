<?php

declare(strict_types=1);

namespace Tests\TestSuite\Formatter;

use CssLint\Formatter\PlainFormatter;
use CssLint\LintError;
use PHPUnit\Framework\TestCase;
use Exception;

class PlainFormatterTest extends TestCase
{
    public function testGetNameReturnsPlain(): void
    {
        $formatter = new PlainFormatter();
        $this->assertSame('plain', $formatter->getName());
    }

    public function testStartLintingOutputsCorrectMessage(): void
    {
        $formatter = new PlainFormatter();
        $this->expectOutputString("# Lint file.css..." . PHP_EOL);
        $formatter->startLinting('file.css');
    }

    public function testPrintFatalErrorWithThrowableOutputsColoredMessage(): void
    {
        $formatter = new PlainFormatter();
        $error = new Exception('fatal error');
        $this->expectOutputString(
            "\033[31m/!\ Error: file.css - fatal error\033[0m" . PHP_EOL
        );
        $formatter->printFatalError('file.css', $error);
    }

    public function testPrintFatalErrorWithStringOutputsColoredMessage(): void
    {
        $formatter = new PlainFormatter();
        $message = 'some error';
        $this->expectOutputString(
            "\033[31m/!\ Error: file.css - some error\033[0m" . PHP_EOL
        );
        $formatter->printFatalError('file.css', $message);
    }

    public function testPrintLintErrorOutputsColoredMessage(): void
    {
        $formatter = new PlainFormatter();
        // Using a LintError stub to provide a string representation
        $error = $this->createStub(LintError::class);
        $error->method('__toString')->willReturn('lint issue');

        $this->expectOutputString(
            "\033[31m    - lint issue\033[0m" . PHP_EOL
        );
        $formatter->printLintError('file.css', $error);
    }

    public function testEndLintingOutputsSuccessWhenValid(): void
    {
        $formatter = new PlainFormatter();
        $this->expectOutputString(
            "\033[32m => Success: file.css is valid.\033[0m" . PHP_EOL . PHP_EOL
        );
        $formatter->endLinting('file.css', true);
    }

    public function testEndLintingOutputsFailureWhenInvalid(): void
    {
        $formatter = new PlainFormatter();
        $this->expectOutputString(
            "\033[31m => Failure: file.css is invalid CSS.\033[0m" . PHP_EOL
        );
        $formatter->endLinting('file.css', false);
    }
}
