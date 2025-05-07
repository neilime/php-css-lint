<?php

declare(strict_types=1);

namespace CssLint;

use InvalidArgumentException;
use RuntimeException;

/**
 * @package CssLint
 * @phpstan-type Errors array<string>
 * @phpstan-type ContextEntry string|null
 * @phpstan-type Context ContextEntry|ContextEntry[]
 */
class Linter
{
    /**
     * Class to provide css properties knowledge
     * @var Properties|null
     */
    protected $cssLintProperties;

    /**
     * The list of char linters
     * @var CharLinter[]
     */
    protected array $charLinters;

    /**
     * The current context of parsing
     * @var LintContext|null
     */
    protected ?LintContext $lintContext = null;

    /**
     * Constructor
     * @param Properties $properties (optional) an instance of the "\CssLint\Properties" helper
     */
    public function __construct(?Properties $properties = null)
    {
        if ($properties instanceof Properties) {
            $this->setCssLintProperties($properties);
        }
    }

    /**
     * Return an instance of the "\CssLint\Properties" helper, initialize a new one if not define already
     */
    public function getCssLintProperties(): Properties
    {
        if ($this->cssLintProperties) {
            return $this->cssLintProperties;
        }

        return $this->cssLintProperties = new Properties();
    }

    /**
     * Set an instance of the "\CssLint\Properties" helper
     */
    public function setCssLintProperties(Properties $cssLintProperties): self
    {
        $this->cssLintProperties = $cssLintProperties;
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

        return in_array($this->lintContext->getErrors(), [null, []], true);
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

        return in_array($this->lintContext->getErrors(), [null, []], true);
    }

    public function getErrors(): ?array
    {
        return $this->lintContext?->getErrors();
    }

    /**
     * Initialize linter, reset all process properties
     */
    protected function initLint(): static
    {
        $this
            ->resetChartLinters()
            ->resetLintContext();

        $this->lintContext->incrementLineNumber();
        return $this;
    }

    /**
     * Performs lint on a given char
     * @return boolean : true if the process should continue, else false
     */
    protected function lintChar(string $charValue): ?bool
    {
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
        $cssLintProperties = $this->getCssLintProperties();

        $this->charLinters = [
            new CharLinter\EndOfLineCharLinter(),
            new CharLinter\CommentCharLinter(),
            new CharLinter\ImportCharLinter(),
            new CharLinter\SelectorCharLinter($cssLintProperties),
            new CharLinter\PropertyCharLinter($cssLintProperties),
        ];
        return $this;
    }

    protected function assertLintContextIsClean(): bool
    {
        if ($this->lintContext && $this->lintContext->assertCurrentContext(null)) {
            return true;
        }

        $currentContext = $this->lintContext->getCurrentContext();

        $this->lintContext->addError('Unterminated "' . $currentContext->value . '"');
        return false;
    }

    protected function resetLintContext(): self
    {
        $this->lintContext = new LintContext();
        return $this;
    }
}
