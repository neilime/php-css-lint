<?php

declare(strict_types=1);

namespace Tests\TestSuite\Output\Formatter;

use CssLint\Output\Formatter\GithubActionsFormatter;
use CssLint\Output\Formatter\FormatterFactory;
use CssLint\Output\Formatter\FormatterManager;
use CssLint\LintError;
use CssLint\LintErrorKey;
use Exception;
use PHPUnit\Framework\TestCase;
use CssLint\Position;

class GithubActionsFormatterTest extends TestCase
{
    private readonly GithubActionsFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new GithubActionsFormatter();
    }

    public function testGetNameReturnsGithubActions(): void
    {

        $this->assertSame('github-actions', $this->formatter->getName());
    }

    public function testStartLintingOutputsGroup(): void
    {
        // Act
        $content = $this->formatter->startLinting('file.css');

        // Assert
        $this->assertSame("::group::Lint file.css" . PHP_EOL, $content);
    }

    public function testPrintFatalErrorWithThrowable(): void
    {
        // Arrange
        $error = new Exception('fatal error');

        // Act
        $content = $this->formatter->printFatalError('file.css', $error);

        // Assert
        $this->assertSame("::error file=file.css::fatal error" . PHP_EOL, $content);
    }

    public function testPrintFatalErrorWithoutSource(): void
    {

        // Act
        $content = $this->formatter->printFatalError(null, 'some error');

        // Assert
        $this->assertSame("::error ::some error" . PHP_EOL, $content);
    }

    public function testPrintLintError(): void
    {
        // Arrange
        $positionArr = ['line' => 10, 'column' => 5];
        $lintError = new LintError(
            key: LintErrorKey::INVALID_AT_RULE_DECLARATION,
            message: 'issue found',
            start: new Position($positionArr['line'], $positionArr['column']),
            end: new Position($positionArr['line'], $positionArr['column'])
        );

        // Act
        $content = $this->formatter->printLintError('file.css', $lintError);

        // Assert
        $this->assertSame(
            "::error file=file.css,line=10,col=5,endLine=10,endColumn=5::invalid_at_rule_declaration - issue found" . PHP_EOL,
            $content
        );
    }

    public function testEndLintingOutputsEndGroup(): void
    {
        // Act
        $content = $this->formatter->endLinting('file.css', true);

        // Assert
        $this->assertSame(
            "::notice ::Success: file.css is valid." . PHP_EOL
                . "::endgroup::" . PHP_EOL,
            $content
        );
    }

    public function testFactoryIntegration(): void
    {
        $factory = new FormatterFactory();
        $available = $factory->getAvailableFormatters();
        $this->assertContains('github-actions', $available);

        $manager = $factory->create(['github-actions' => null]);
        $this->assertInstanceOf(FormatterManager::class, $manager);
    }
}
