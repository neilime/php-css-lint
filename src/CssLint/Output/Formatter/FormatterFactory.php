<?php

declare(strict_types=1);

namespace CssLint\Output\Formatter;

use RuntimeException;
use CssLint\Output\Formatter\GithubActionsFormatter;
use CssLint\Output\Formatter\GitlabCiFormatter;
use CssLint\Output\FileOutput;
use CssLint\Output\StdoutOutput;

/**
 * Factory to create FormatterManager based on requested names.
 * @phpstan-import-type OutputFormatters from FormatterManager
 */
class FormatterFactory
{
    /** @var non-empty-array<non-empty-string, FormatterInterface> */
    private array $formaters;

    public function __construct()
    {
        $availableFormatters = [
            new PlainFormatter(),
            new GitlabCiFormatter(),
            new GithubActionsFormatter(),
        ];
        foreach ($availableFormatters as $formatter) {
            $this->formaters[$formatter->getName()] = $formatter;
        }
    }

    /**
     * Create a FormatterManager based on formatter specifications with output destinations.
     * @param array<string, string|null> $formatterSpecs Array of formatter name => output path
     * @return FormatterManager
     * @throws RuntimeException on invalid formatter names or file creation errors
     */
    public function create(?array $formatterSpecs = null): FormatterManager
    {
        $availableNames = $this->getAvailableFormatters();
        if (empty($formatterSpecs)) {
            // Use default formatter (to stdout)
            $defaultFormatter = $availableNames[0];
            $formatterSpecs = [$defaultFormatter => null];
        }

        /** @var OutputFormatters $outputFormatters */
        $outputFormatters = [];

        foreach ($formatterSpecs as $formatterName => $outputPath) {
            if (!in_array($formatterName, $availableNames, true)) {
                throw new RuntimeException("Invalid formatter: {$formatterName}");
            }

            $formatter = $this->formaters[$formatterName];

            if ($outputPath === null) {
                // Output to stdout
                $outputFormatters[] = [new StdoutOutput(), $formatter];
            } else {
                // Output to file
                $outputFormatters[] = [new FileOutput($outputPath), $formatter];
            }
        }

        return new FormatterManager($outputFormatters);
    }

    /**
     * Get the names of all available formatters.
     * @return non-empty-array<non-empty-string> List of formatter names
     */
    public function getAvailableFormatters(): array
    {
        return array_keys($this->formaters);
    }
}
