<?php

declare(strict_types=1);

namespace CssLint\Output\Formatter;

use CssLint\LintError;
use PhpCsFixer\DocBlock\Annotation;
use Throwable;

enum AnnotationType: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
    case NOTICE = 'notice';
}

/**
 * Formatter for GitHub Actions annotations.
 * @phpstan-type AnnotationProperties array{title?: string|null, file?: string|null, col?: int|null, endColumn?: int|null, line?: int|null, endLine?: int|null}
 */
class GithubActionsFormatter implements FormatterInterface
{
    public function getName(): string
    {
        return 'github-actions';
    }

    public function startLinting(string $source): string
    {
        return "::group::Lint {$source}" . PHP_EOL;
    }

    public function printFatalError(?string $source, mixed $error): string
    {
        $message = $error instanceof Throwable ? $error->getMessage() : (string) $error;

        $annotationProperties = [];
        if ($source) {
            $annotationProperties['file'] = $source;
        }

        return $this->printAnnotation(AnnotationType::ERROR, $message, $annotationProperties);
    }

    public function printLintError(string $source, LintError $lintError): string
    {
        $key = $lintError->getKey();
        $message = $lintError->getMessage();
        $startPosition = $lintError->getStart();
        $endPosition = $lintError->getEnd();
        return $this->printAnnotation(
            AnnotationType::ERROR,
            sprintf('%s - %s', $key->value, $message),
            [
                'file' => $source,
                'line' => $startPosition->getLine(),
                'col' => $startPosition->getColumn(),
                'endLine' => $endPosition->getLine(),
                'endColumn' => $endPosition->getColumn(),
            ]
        );
    }

    public function endLinting(string $source, bool $isValid): string
    {
        $content = '';
        if ($isValid) {
            $content .= $this->printAnnotation(AnnotationType::NOTICE, "Success: {$source} is valid.");
        } else {
            $content .= $this->printAnnotation(AnnotationType::ERROR, "{$source} is invalid CSS.", ['file' => $source]);
        }
        $content .= "::endgroup::" . PHP_EOL;
        return $content;
    }

    /**
     * @param AnnotationProperties $annotationProperties
     */
    private function printAnnotation(AnnotationType $type, string $message, array $annotationProperties = []): string
    {
        $properties = $this->sanitizeAnnotationProperties($annotationProperties);
        $command = sprintf('::%s %s::%s', $type->value, $properties, $message);
        // Sanitize command
        $command = str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $command);
        return $command . PHP_EOL;
    }

    /**
     * @param AnnotationProperties $annotationProperties
     */
    private function sanitizeAnnotationProperties(array $annotationProperties): string
    {
        $nonNullProperties = array_filter(
            $annotationProperties,
            static fn($value): bool => $value !== null
        );
        $sanitizedProperties = array_map(
            fn($key, $value): string => sprintf('%s=%s', $key, $this->sanitizeAnnotationProperty($value)),
            array_keys($nonNullProperties),
            $nonNullProperties
        );
        return implode(',', $sanitizedProperties);
    }

    /**
     * @param string|int|null $value
     */
    private function sanitizeAnnotationProperty($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $value = (string) $value;
        return str_replace(['%', "\r", "\n", ':', ','], ['%25', '%0D', '%0A', '%3A', '%2C'], $value);
    }
}
