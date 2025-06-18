<?php

declare(strict_types=1);

namespace Tests\TestSuite\Output\Formatter;

use PHPUnit\Framework\TestCase;
use CssLint\Output\Formatter\GitlabCiFormatter;
use CssLint\Position;
use CssLint\LintError;
use CssLint\LintErrorKey;
use Exception;

class GitlabCiFormatterTest extends TestCase
{
    private readonly GitlabCiFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new GitlabCiFormatter();
    }

    public function testGetNameReturnsGitlabCi(): void
    {
        $this->assertSame('gitlab-ci', $this->formatter->getName());
    }

    public function testStartAndEndLintingOutputsEmptyArray(): void
    {
        // Act
        $content = '';
        $content .= $this->formatter->startLinting('file.css');
        $content .= $this->formatter->endLinting('file.css', false);

        // Assert
        $this->assertSame('[]', $content);
    }

    public function testPrintFatalErrorFormatsIssueCorrectly(): void
    {
        // Arrange
        $error = new Exception('fatal error');

        // Prepare expected issue
        $path = 'file.css';
        $severity = 'critical';
        $checkName = get_class($error);
        $message = $error->getMessage();
        $line = 1;
        $column = 1;
        $payload = sprintf("%s:%s:%s:%s:%d:%d", $path, $severity, $checkName, $message, $line, $column);
        $fingerprint = md5($payload);
        $issue = [
            'description' => $message,
            'check_name' => $checkName,
            'fingerprint' => $fingerprint,
            'severity' => $severity,
            'location' => [
                'path' => $path,
                'positions' => [
                    'begin' => ['line' => $line, 'column' => $column],
                ],
            ],
        ];

        // Act
        $content = '';

        $content .= $this->formatter->startLinting($path);
        $content .= $this->formatter->printFatalError($path, $error);
        $content .= $this->formatter->endLinting($path, false);

        // Assert
        $expected = '[' . json_encode($issue) . ']';
        $this->assertSame($expected, $content);
    }

    public function testPrintLintErrorFormatsIssueCorrectly(): void
    {
        // Arrange
        $path = 'file.css';
        $line = 10;
        $col = 5;
        $key = LintErrorKey::INVALID_AT_RULE_DECLARATION;
        $message = 'issue found';
        $lintError = new LintError(
            key: $key,
            message: $message,
            start: new Position($line, $col),
            end: new Position($line, $col)
        );

        // Act
        $content = '';
        $content .= $this->formatter->startLinting($path);
        $content .= $this->formatter->printLintError($path, $lintError);
        $content .= $this->formatter->endLinting($path, false);

        // Assert
        $this->assertJson($content, 'Output is not valid JSON');

        // Compute payload and fingerprint
        $severity = 'major';
        $payload = sprintf("%s:%s:%s:%s:%d:%d:%d:%d", $path, $severity, $key->value, $message, $line, $col, $line, $col);
        $fingerprint = md5($payload);

        $issue = [
            'description' => $message,
            'check_name' => $key->value,
            'fingerprint' => $fingerprint,
            'severity' => $severity,
            'location' => [
                'path' => $path,
                'positions' => [
                    'begin' => ['line' => $line, 'column' => $col],
                    'end' => ['line' => $line, 'column' => $col],
                ],
            ],
        ];

        $expected = '[' . json_encode($issue) . ']';
        $this->assertSame($expected, $content);
    }

    public function testDuplicateIssues(): void
    {
        // Arrange
        $path = 'file.css';
        $error = new Exception('dup');


        $content = '';

        $content .= $this->formatter->startLinting($path);
        // Print the same fatal error twice
        $content .= $this->formatter->printFatalError($path, $error);
        $content .= $this->formatter->printFatalError($path, $error);
        $content .= $this->formatter->endLinting($path, false);

        $this->assertJson($content, 'Output is not valid JSON');
        $issues = json_decode($content, true);
        $this->assertCount(2, $issues);

        // Ensure fingerprints are different
        $fingerprints = array_map(fn($issue) => $issue['fingerprint'], $issues);
        $this->assertCount(count(array_unique($fingerprints)), $fingerprints, 'Duplicate fingerprints found in output');
    }
}
