<?php

declare(strict_types=1);

namespace CssLint;

use CssLint\CharLinter\CharLinter;
use CssLint\CharLinter\EndOfLineCharLinter;
use CssLint\CharLinter\CommentCharLinter;
use CssLint\CharLinter\ImportCharLinter;
use CssLint\CharLinter\SelectorCharLinter;
use CssLint\CharLinter\PropertyCharLinter;
use InvalidArgumentException;
use RuntimeException;

/**
 * @package CssLint
 * @phpstan-import-type Errors from LintContext
 */
class Linter
{
    /**
     * Class to provide css properties knowledge
     * @var LintConfiguration|null
     */
    protected $lintConfiguration;

    /**
     * The list of char linters
     * @var CharLinter[]
     */
    protected array $charLinters;

    /**
     * The current context of parsing
     */
    protected ?LintContext $lintContext = null;

    /**
     * Constructor
     * @param LintConfiguration $lintConfiguration (optional) an instance of the "\CssLint\Properties" helper
     */
    public function __construct(?LintConfiguration $lintConfiguration = null)
    {
        if ($lintConfiguration instanceof LintConfiguration) {
            $this->setLintConfiguration($lintConfiguration);
        }
    }

    /**
     * Return an instance of the "\CssLint\Properties" helper, initialize a new one if not define already
     */
    public function getLintConfiguration(): LintConfiguration
    {
        if ($this->lintConfiguration) {
            return $this->lintConfiguration;
        }

        return $this->lintConfiguration = new LintConfiguration();
    }

    /**
     * Set an instance of the "\CssLint\Properties" helper
     */
    public function setLintConfiguration(LintConfiguration $lintConfiguration): self
    {
        $this->lintConfiguration = $lintConfiguration;
        return $this;
    }

    /**
     * Performs lint on a given string
     * @return boolean : true if the string is a valid css string, false else
     */
    public function lintString(string $stringValue): bool
    {
        $this->initLint();
        $iIterator = 0;
        while (isset($stringValue[$iIterator])) {
            if ($this->lintChar($stringValue[$iIterator]) === false) {
                return false;
            }

            ++$iIterator;
        }

        $this->assertLintContextIsClean();

        return $this->getErrors() === [];
    }

    /**
     * Performs lint for a given file path
     * @param string $sFilePath : a path of an existing and readable file
     * @return boolean : true if the file is a valid css file, else false
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function lintFile(string $sFilePath): bool
    {
        if (!file_exists($sFilePath)) {
            throw new InvalidArgumentException(sprintf(
                'Argument "$sFilePath" "%s" is not an existing file path',
                $sFilePath
            ));
        }

        if (!is_readable($sFilePath)) {
            throw new InvalidArgumentException(sprintf(
                'Argument "$sFilePath" "%s" is not a readable file path',
                $sFilePath
            ));
        }

        $rFileHandle = fopen($sFilePath, 'r');
        if ($rFileHandle === false) {
            throw new RuntimeException('An error occurred while opening file "' . $sFilePath . '"');
        }

        $this->initLint();

        while (($charValue = fgetc($rFileHandle)) !== false) {
            if ($this->lintChar($charValue) === false) {
                fclose($rFileHandle);
                return false;
            }
        }

        if (!feof($rFileHandle)) {
            throw new RuntimeException('An error occurred while reading file "' . $sFilePath . '"');
        }

        fclose($rFileHandle);

        $this->assertLintContextIsClean();

        return $this->getErrors() === [];
    }

    /**
     * Return the errors occurred during the lint process
     * @return Errors
     */
    public function getErrors(): array
    {
        return $this->lintContext?->getErrors() ?? [];
    }

    /**
     * Initialize linter, reset all process properties
     */
    protected function initLint(): static
    {
        $this
            ->resetChartLinters()
            ->resetLintContext();

        $this->lintContext?->incrementLineNumber();
        return $this;
    }

    /**
     * Performs lint on a given char
     * @return boolean : true if the process should continue, else false
     */
    protected function lintChar(string $charValue): ?bool
    {
        if (!$this->lintContext instanceof LintContext) {
            throw new RuntimeException('Lint context is not initialized');
        }

        $this->lintContext->incrementCharNumber();

        foreach ($this->charLinters as $charLinter) {
            if (is_bool($lintChar = $charLinter->lintChar($charValue, $this->lintContext))) {
                $this->lintContext->setPreviousChar($charValue);
                return $lintChar;
            }
        }

        $this->lintContext->addError('Unexpected char ' . json_encode($charValue));
        $this->lintContext->setPreviousChar($charValue);
        return false;
    }

    protected function resetChartLinters(): self
    {
        $lintConfiguration = $this->getLintConfiguration();

        $this->charLinters = [
            new EndOfLineCharLinter(),
            new CommentCharLinter(),
            new ImportCharLinter(),
            new SelectorCharLinter($lintConfiguration),
            new PropertyCharLinter($lintConfiguration),
        ];
        return $this;
    }

    protected function assertLintContextIsClean(): bool
    {
        if (!$this->lintContext instanceof LintContext) {
            return true;
        }

        $currentContext = $this->lintContext->getCurrentContext();
        if (!$currentContext instanceof LintContextName) {
            return true;
        }


        $error = sprintf(
            'Unterminated "%s"',
            $currentContext->value,
        );

        $currentContent = $this->lintContext->getCurrentContent();
        if ($currentContent !== '') {
            $error .= sprintf(' - "%s"', $currentContent);
        }

        $this->lintContext->addError($error);
        return false;
    }

    protected function resetLintContext(): self
    {
        $this->lintContext = new LintContext();
        return $this;
    }
}
