<?php

declare(strict_types=1);

namespace Tests\TestSuite\Formatter;

use CssLint\Formatter\GithubActionsFormatter;
use CssLint\Formatter\FormatterFactory;
use CssLint\Formatter\FormatterManager;
use CssLint\LintError;
use CssLint\LintErrorKey;
use Exception;
use PHPUnit\Framework\TestCase;
use CssLint\Position;

class GithubActionsFormatterTest extends TestCase
{
    public function testGetNameReturnsGithubActions(): void
    {
        $formatter = new GithubActionsFormatter();
        $this->assertSame('github-actions', $formatter->getName());
    }

    public function testStartLintingOutputsGroup(): void
    {
        $formatter = new GithubActionsFormatter();
        $this->expectOutputString("::group::Lint file.css" . PHP_EOL);
        $formatter->startLinting('file.css');
    }

    public function testPrintFatalErrorWithThrowable(): void
    {
        $formatter = new GithubActionsFormatter();
        $error = new Exception('fatal error');
        $this->expectOutputString("::error file=file.css::fatal error" . PHP_EOL);
        $formatter->printFatalError('file.css', $error);
    }

    public function testPrintFatalErrorWithoutSource(): void
    {
        $formatter = new GithubActionsFormatter();
        $this->expectOutputString("::error ::some error" . PHP_EOL);
        $formatter->printFatalError(null, 'some error');
    }

    public function testPrintLintError(): void
    {
        $positionArr = ['line' => 10, 'column' => 5];
        $lintError = new LintError(
            key: LintErrorKey::INVALID_AT_RULE_DECLARATION,
            message: 'issue found',
            start: new Position($positionArr['line'], $positionArr['column']),
            end: new Position($positionArr['line'], $positionArr['column'])
        );

        $formatter = new GithubActionsFormatter();
        $this->expectOutputString("::error file=file.css,line=10,col=5::invalid_at_rule_declaration - issue found" . PHP_EOL);
        $formatter->printLintError('file.css', $lintError);
    }

    public function testEndLintingOutputsEndGroup(): void
    {
        $formatter = new GithubActionsFormatter();
        $this->expectOutputString(
            "::notice ::Success: file.css is valid." . PHP_EOL .
                "::endgroup::" . PHP_EOL
        );
        $formatter->endLinting('file.css', true);
    }

    public function testFactoryIntegration(): void
    {
        $factory = new FormatterFactory();
        $available = $factory->getAvailableFormatters();
        $this->assertContains('github-actions', $available);

        $manager = $factory->create('github-actions');
        $this->assertInstanceOf(FormatterManager::class, $manager);
    }
}
