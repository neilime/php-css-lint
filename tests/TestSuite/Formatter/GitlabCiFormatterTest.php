<?php

declare(strict_types=1);

namespace Tests\TestSuite\Formatter;

use PHPUnit\Framework\TestCase;
use CssLint\Formatter\GitlabCiFormatter;
use CssLint\Position;
use CssLint\LintError;
use CssLint\LintErrorKey;
use Exception;
use RuntimeException;

class GitlabCiFormatterTest extends TestCase
{
    public function testGetNameReturnsGitlabCi(): void
    {
        $formatter = new GitlabCiFormatter();
        $this->assertSame('gitlab-ci', $formatter->getName());
    }

    public function testStartAndEndLintingOutputsEmptyArray(): void
    {
        $formatter = new GitlabCiFormatter();

        $this->expectOutputString('[]');
        $formatter->startLinting('file.css');
        $formatter->endLinting('file.css', false);
    }

    public function testPrintFatalErrorFormatsIssueCorrectly(): void
    {
        $formatter = new GitlabCiFormatter();
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

        $expected = '[' . json_encode($issue) . ']';

        $this->expectOutputString($expected);

        $formatter->startLinting($path);
        $formatter->printFatalError($path, $error);
        $formatter->endLinting($path, false);
    }

    public function testPrintLintErrorFormatsIssueCorrectly(): void
    {
        $formatter = new GitlabCiFormatter();
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

        $this->expectOutputString($expected);

        $formatter->startLinting($path);
        $formatter->printLintError($path, $lintError);
        $formatter->endLinting($path, false);
    }

    public function testDuplicateIssues(): void
    {
        $formatter = new GitlabCiFormatter();
        $path = 'file.css';
        $error = new Exception('dup');


        $formatter->startLinting($path);
        // Print the same fatal error twice
        $formatter->printFatalError($path, $error);
        $formatter->printFatalError($path, $error);
        $formatter->endLinting($path, false);

        $output = $this->getActualOutputForAssertion();
        $this->assertJson($output, 'Output is not valid JSON');
        $issues = json_decode($output, true);
        $this->assertCount(2, $issues);

        // Ensure fingerprints are different
        $fingerprints = array_map(fn($issue) => $issue['fingerprint'], $issues);
        $this->assertCount(count(array_unique($fingerprints)), $fingerprints, 'Duplicate fingerprints found in output');
    }
}
