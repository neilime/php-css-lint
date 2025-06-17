<?php

declare(strict_types=1);

namespace Tests\TestSuite\Formatter;

use CssLint\Formatter\FormatterManager;
use CssLint\Formatter\FormatterInterface;
use CssLint\LintError;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Exception;

class FormatterManagerTest extends TestCase
{
    public function testStartLintingPropagatesToAllFormatters(): void
    {
        $formatter1 = $this->createMock(FormatterInterface::class);
        $formatter1->expects($this->once())
            ->method('startLinting')
            ->with('source.css');

        $formatter2 = $this->createMock(FormatterInterface::class);
        $formatter2->expects($this->once())
            ->method('startLinting')
            ->with('source.css');

        $manager = new FormatterManager([$formatter1, $formatter2]);
        $manager->startLinting('source.css');
    }

    public function testPrintFatalErrorPropagatesToAllFormatters(): void
    {
        $error = new Exception('fatal error');

        $formatter1 = $this->createMock(FormatterInterface::class);
        $formatter1->expects($this->once())
            ->method('printFatalError')
            ->with('file.css', $error);

        $formatter2 = $this->createMock(FormatterInterface::class);
        $formatter2->expects($this->once())
            ->method('printFatalError')
            ->with('file.css', $error);

        $manager = new FormatterManager([$formatter1, $formatter2]);
        $manager->printFatalError('file.css', $error);
    }

    public function testPrintLintErrorPropagatesToAllFormatters(): void
    {
        $lintError = $this->createMock(LintError::class);

        $formatter1 = $this->createMock(FormatterInterface::class);
        $formatter1->expects($this->once())
            ->method('printLintError')
            ->with('file.css', $lintError);

        $formatter2 = $this->createMock(FormatterInterface::class);
        $formatter2->expects($this->once())
            ->method('printLintError')
            ->with('file.css', $lintError);

        $manager = new FormatterManager([$formatter1, $formatter2]);
        $manager->printLintError('file.css', $lintError);
    }

    public function testEndLintingPropagatesToAllFormatters(): void
    {
        $formatter1 = $this->createMock(FormatterInterface::class);
        $formatter1->expects($this->once())
            ->method('endLinting')
            ->with('file.css', true);

        $formatter2 = $this->createMock(FormatterInterface::class);
        $formatter2->expects($this->once())
            ->method('endLinting')
            ->with('file.css', true);

        $manager = new FormatterManager([$formatter1, $formatter2]);
        $manager->endLinting('file.css', true);
    }

    public function testGetNameThrowsRuntimeException(): void
    {
        $formatter = $this->createMock(FormatterInterface::class);

        $manager = new FormatterManager([$formatter]);
        $this->expectException(RuntimeException::class);
        $manager->getName();
    }
}
