<?php

declare(strict_types=1);

namespace Tests\TestSuite\Output\Formatter;

use CssLint\Output\Formatter\PlainFormatter;
use CssLint\LintError;
use CssLint\LintErrorKey;
use CssLint\Position;
use PHPUnit\Framework\TestCase;
use Exception;

class PlainFormatterTest extends TestCase
{
    private readonly PlainFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new PlainFormatter();
    }

    public function testGetNameReturnsPlain(): void
    {
        $this->assertSame('plain', $this->formatter->getName());
    }

    public function testStartLintingOutputsCorrectMessage(): void
    {
        // Act
        $content = $this->formatter->startLinting('file.css');
        $this->assertSame("# Lint file.css..." . PHP_EOL, $content);
    }

    public function testPrintFatalErrorWithThrowableOutputsColoredMessage(): void
    {
        // Arrange
        $error = new Exception('fatal error');

        // Act
        $content = $this->formatter->printFatalError('file.css', $error);

        // Assert
        $this->assertSame("\033[31m/!\ Error: file.css - fatal error\033[0m" . PHP_EOL, $content);
    }

    public function testPrintFatalErrorWithStringOutputsColoredMessage(): void
    {
        // Arrange
        $message = 'some error';

        // Act
        $content = $this->formatter->printFatalError('file.css', $message);

        // Assert
        $this->assertSame("\033[31m/!\ Error: file.css - some error\033[0m" . PHP_EOL, $content);
    }

    public function testPrintLintErrorOutputsColoredMessage(): void
    {
        // Arrange
        $lintError = new LintError(
            key: LintErrorKey::INVALID_AT_RULE_DECLARATION,
            message: 'issue found',
            start: new Position(),
            end: new Position()
        );

        // Act
        $content = $this->formatter->printLintError('file.css', $lintError);

        // Assert
        $this->assertSame(
            "\033[31m    - [invalid_at_rule_declaration]: issue found (line 1, column 1 to line 1, column 1)\033[0m" . PHP_EOL,
            $content
        );
    }

    public function testEndLintingOutputsSuccessWhenValid(): void
    {
        // Act
        $content = $this->formatter->endLinting('file.css', true);

        // Assert
        $this->assertSame(
            "\033[32m => Success: file.css is valid.\033[0m" . PHP_EOL . PHP_EOL,
            $content
        );
    }

    public function testEndLintingOutputsFailureWhenInvalid(): void
    {
        // Act
        $content = $this->formatter->endLinting('file.css', false);

        // Assert
        $this->assertSame(
            "\033[31m => Failure: file.css is invalid CSS.\033[0m" . PHP_EOL,
            $content
        );
    }
}
