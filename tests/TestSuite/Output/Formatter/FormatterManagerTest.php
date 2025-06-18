<?php

declare(strict_types=1);

namespace Tests\TestSuite\Output\Formatter;

use CssLint\Output\Formatter\FormatterManager;
use CssLint\Output\Formatter\FormatterInterface;
use CssLint\LintError;
use CssLint\Output\OutputInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Exception;

class FormatterManagerTest extends TestCase
{
    public function testStartLintingPropagatesToAllFormatters(): void
    {
        // Aarrange
        $output = $this->createMock(OutputInterface::class);

        $formatter1 = $this->createMock(FormatterInterface::class);
        $formatter1->expects($this->once())
            ->method('startLinting')
            ->with('source.css');

        $formatter2 = $this->createMock(FormatterInterface::class);
        $formatter2->expects($this->once())
            ->method('startLinting')
            ->with('source.css');

        // Act
        $manager = new FormatterManager([
            [$output, $formatter1],
            [$output, $formatter2],
        ]);
        $manager->startLinting('source.css');
    }

    public function testPrintFatalErrorPropagatesToAllFormatters(): void
    {
        // Arrange
        $error = new Exception('fatal error');

        $output = $this->createMock(OutputInterface::class);

        $formatter1 = $this->createMock(FormatterInterface::class);
        $formatter1->expects($this->once())
            ->method('printFatalError')
            ->with('file.css', $error);

        $formatter2 = $this->createMock(FormatterInterface::class);
        $formatter2->expects($this->once())
            ->method('printFatalError')
            ->with('file.css', $error);

        $manager = new FormatterManager([[$output, $formatter1], [$output, $formatter2]]);

        // Act
        $manager->printFatalError('file.css', $error);
    }

    public function testPrintLintErrorPropagatesToAllFormatters(): void
    {
        // Arrange
        $lintError = $this->createMock(LintError::class);

        $output = $this->createMock(OutputInterface::class);

        $formatter1 = $this->createMock(FormatterInterface::class);
        $formatter1->expects($this->once())
            ->method('printLintError')
            ->with('file.css', $lintError);

        $formatter2 = $this->createMock(FormatterInterface::class);
        $formatter2->expects($this->once())
            ->method('printLintError')
            ->with('file.css', $lintError);


        $manager = new FormatterManager([
            [$output, $formatter1],
            [$output, $formatter2],
        ]);

        // Act
        $manager->printLintError('file.css', $lintError);
    }

    public function testEndLintingPropagatesToAllFormatters(): void
    {
        // Arrange
        $output = $this->createMock(OutputInterface::class);

        $formatter1 = $this->createMock(FormatterInterface::class);
        $formatter1->expects($this->once())
            ->method('endLinting')
            ->with('file.css', true);

        $formatter2 = $this->createMock(FormatterInterface::class);
        $formatter2->expects($this->once())
            ->method('endLinting')
            ->with('file.css', true);

        $manager = new FormatterManager([
            [$output, $formatter1],
            [$output, $formatter2],
        ]);

        // Act
        $manager->endLinting('file.css', true);
    }
}
