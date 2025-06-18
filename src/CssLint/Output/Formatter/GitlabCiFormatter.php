<?php

declare(strict_types=1);

namespace CssLint\Output\Formatter;

use CssLint\LintError;
use CssLint\Position;
use RuntimeException;
use Throwable;

enum IssueSeverity: string
{
    case CRITICAL = 'critical';
    case MAJOR = 'major';
    case MINOR = 'minor';
    case INFO = 'info';
}

/**
 * Formatter for GitLab CI Code Quality report (Code Climate JSON format).
 */
class GitlabCiFormatter implements FormatterInterface
{
    /**
     * @var array<string> Used to track fingerprints to avoid duplicates.
     * This is not strictly necessary for GitLab CI, but helps ensure unique issues.
     */
    private $fingerprints = [];

    public function getName(): string
    {
        return 'gitlab-ci';
    }

    public function startLinting(string $source): string
    {
        // Initialize fingerprints to track issues
        $this->fingerprints = [];
        return "[";
    }

    public function printFatalError(?string $source, mixed $error): string
    {
        $checkName = $error instanceof Throwable ? $error::class : 'CssLint';
        $message = $error instanceof Throwable ? $error->getMessage() : (string) $error;

        return $this->printIssue(
            $source ?? '',
            IssueSeverity::CRITICAL,
            $checkName,
            $message,
            new Position()
        );
    }

    public function printLintError(string $source, LintError $lintError): string
    {
        return $this->printIssue(
            $source,
            IssueSeverity::MAJOR,
            $lintError->getKey()->value,
            $lintError->getMessage(),
            $lintError->getStart(),
            $lintError->getEnd()
        );
    }

    public function endLinting(string $source, bool $isValid): string
    {
        return ']';
    }

    private function printIssue(string $path, IssueSeverity $severity, string $checkName, string $message, Position $begin, ?Position $end = null): string
    {
        $content = $this->printCommaIfNeeded();

        $fingerprint = $this->generateFingerprint(
            $path,
            $severity,
            $checkName,
            $message,
            $begin,
            $end
        );

        $issue = [
            'description' => $message,
            'check_name' => $checkName,
            'fingerprint' => $fingerprint,
            'severity' => $severity->value,
            'location' => [
                'path' => $path,
                'positions' => [
                    'begin' => ['line' => $begin->getLine(), 'column' => $begin->getColumn()],
                ],
            ],
        ];

        if ($end) {
            $issue['location']['positions']['end'] = [
                'line' => $end->getLine(),
                'column' => $end->getColumn(),
            ];
        }

        $content .= json_encode($issue);
        return $content;
    }

    private function printCommaIfNeeded(): string
    {
        if ($this->fingerprints) {
            return ',';
        }
        return '';
    }

    private function generateFingerprint(string $path, IssueSeverity $severity, string $checkName, string $message, Position $begin, ?Position $end = null): string
    {
        $attempts = 0;
        while ($attempts < 10) {

            $payload = "{$path}:{$severity->value}:{$checkName}:{$message}:{$begin->getLine()}:{$begin->getColumn()}";
            if ($end) {
                $payload .= ":{$end->getLine()}:{$end->getColumn()}";
            }

            if ($attempts > 0) {
                $uniquid = uniqid('', true);
                $payload .= ":{$uniquid}";
            }

            $fingerprint = md5($payload);
            if (!in_array($fingerprint, $this->fingerprints, true)) {
                $this->fingerprints[] = $fingerprint;
                return $fingerprint;
            }

            $attempts++;
        }

        throw new RuntimeException('Failed to generate unique fingerprint after 10 attempts');
    }
}
