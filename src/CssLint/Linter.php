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
    public const CONTEXT_SELECTOR = 'selector';

    public const CONTEXT_SELECTOR_CONTENT = 'selector content';

    public const CONTEXT_NESTED_SELECTOR_CONTENT = 'nested selector content';

    public const CONTEXT_PROPERTY_NAME = 'property name';

    public const CONTEXT_PROPERTY_CONTENT = 'property content';

    /**
     * Class to provide css properties knowledge
     * @var Properties|null
     */
    protected $cssLintProperties;

    /**
     * Errors occurred during the lint process
     * @var Errors
     */
    protected $errors = [];

    /**
     * Current line number
     * @var int
     */
    protected $lineNumber = 0;

    /**
     * Current char number
     * @var int
     */
    protected $charNumber = 0;

    /**
     * Current context of parsing (must be a constant starting by CONTEXT_...)
     * @var string|null
     */
    protected $context;

    /**
     * Current content of parse. Ex: the selector name, the property name or the property content
     * @var string
     */
    protected $contextContent;

    /**
     * The previous linted char
     * @var string|null
     */
    protected $previousChar;

    /**
     * Tells if the linter is parsing a nested selector. Ex: @media, @keyframes...
     * @var boolean
     */
    protected $nestedSelector = false;

    /**
     * Tells if the linter is parsing a comment
     * @var boolean
     */
    protected $comment = false;

    /**
     * Constructor
     * @param Properties $oProperties (optional) an instance of the "\CssLint\Properties" helper
     */
    public function __construct(?Properties $oProperties = null)
    {
        if ($oProperties instanceof Properties) {
            $this->setCssLintProperties($oProperties);
        }
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

        if (!$this->assertContext(null)) {
            $this->addError('Unterminated "' . $this->context . '"');
        }

        return in_array($this->getErrors(), [null, []], true);
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

        if (!$this->assertContext(null)) {
            $this->addError('Unterminated "' . $this->context . '"');
        }

        return in_array($this->getErrors(), [null, []], true);
    }

    /**
     * Initialize linter, reset all process properties
     */
    protected function initLint(): static
    {
        $this
            ->resetPreviousChar()
            ->resetContext()
            ->resetLineNumber()->incrementLineNumber()
            ->resetCharNumber()
            ->resetErrors()
            ->resetContextContent();
        return $this;
    }

    /**
     * Performs lint on a given char
     * @return boolean : true if the process should continue, else false
     */
    protected function lintChar(string $charValue): ?bool
    {
        $this->incrementCharNumber();
        if ($this->isEndOfLine($charValue)) {
            $this->setPreviousChar($charValue);
            if ($charValue === "\n") {
                $this->incrementLineNumber()->resetCharNumber();
            }

            return true;
        }

        if (is_bool($lintImportChar = $this->lintImportChar($charValue))) {
            $this->setPreviousChar($charValue);
            return $lintImportChar;
        }

        if (is_bool($lintCommentChar = $this->lintCommentChar($charValue))) {
            $this->setPreviousChar($charValue);
            return $lintCommentChar;
        }

        if (is_bool($lintSelectorChar = $this->lintSelectorChar($charValue))) {
            $this->setPreviousChar($charValue);
            return $lintSelectorChar;
        }

        if (is_bool($lintSelectorContentChar = $this->lintSelectorContentChar($charValue))) {
            $this->setPreviousChar($charValue);
            return $lintSelectorContentChar;
        }

        if (is_bool($lintPropertyNameChar = $this->lintPropertyNameChar($charValue))) {
            $this->setPreviousChar($charValue);
            return $lintPropertyNameChar;
        }

        if (is_bool($lintPropertyContentChar = $this->lintPropertyContentChar($charValue))) {
            $this->setPreviousChar($charValue);
            return $lintPropertyContentChar;
        }

        if (is_bool($lintNestedSelectorChar = $this->lintNestedSelectorChar($charValue))) {
            $this->setPreviousChar($charValue);
            return $lintNestedSelectorChar;
        }

        $this->addError('Unexpected char ' . json_encode($charValue));
        $this->setPreviousChar($charValue);
        return false;
    }

    /**
     * Performs lint for a given char, check comment part
     * @return boolean|null : true if the process should continue, else false, null if this char is not about comment
     */
    protected function lintCommentChar(string $charValue): ?bool
    {
        // Manage comment context
        if ($this->isComment()) {
            if ($charValue === '/' && $this->assertPreviousChar('*')) {
                $this->setComment(false);
            }

            $this->setPreviousChar($charValue);
            return true;
        }

        // First char for a comment
        if ($charValue === '/') {
            return true;
        }

        // First char for a comment
        if ($charValue === '*' && $this->assertPreviousChar('/')) {
            // End of comment
            $this->setComment(true);
            return true;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check selector part
     * @return boolean|null : true if the process should continue, else false, null if this char is not about selector
     */
    protected function lintSelectorChar(string $charValue): ?bool
    {
        // Selector must start by #.a-zA-Z
        if ($this->assertContext(null)) {
            if ($this->getCssLintProperties()->isAllowedIndentationChar($charValue)) {
                return true;
            }

            if (preg_match('/[@#.a-zA-Z\[\*-:]+/', $charValue)) {
                $this->setContext(self::CONTEXT_SELECTOR);
                $this->addContextContent($charValue);
                return true;
            }

            return null;
        }

        // Selector must contains
        if ($this->assertContext(self::CONTEXT_SELECTOR)) {
            // A space is valid
            if ($charValue === ' ') {
                $this->addContextContent($charValue);
                return true;
            }

            // Start of selector content
            if ($charValue === '{') {
                // Check if selector if valid
                $selector = trim($this->getContextContent());

                // @nested is a specific selector content
                if (
                    // @media selector
                    preg_match('/^@media.+/', $selector)
                    // Keyframes selector
                    || preg_match('/^@.*keyframes.+/', $selector)
                ) {
                    $this->setNestedSelector(true);
                    $this->resetContext();
                } else {
                    $this->setContext(self::CONTEXT_SELECTOR_CONTENT);
                }

                $this->addContextContent($charValue);
                return true;
            }

            // There cannot have two following commas
            if ($charValue === ',') {
                $selector = $this->getContextContent();
                if ($selector === '' || $selector === '0' || in_array(preg_match('/, *$/', $selector), [0, false], true)) {
                    $this->addContextContent($charValue);
                    return true;
                }

                $this->addError(sprintf(
                    'Selector token %s cannot be preceded by "%s"',
                    json_encode($charValue),
                    $selector
                ));
                return false;
            }

            // Wildcard and hash
            if (in_array($charValue, ['*', '#'], true)) {
                $selector = $this->getContextContent();
                if ($selector === '' || $selector === '0' || preg_match('/[a-zA-Z>,\'"] *$/', $selector)) {
                    $this->addContextContent($charValue);
                    return true;
                }

                $this->addError('Selector token "' . $charValue . '" cannot be preceded by "' . $selector . '"');
                return true;
            }

            // Dot
            if ($charValue === '.') {
                $selector = $this->getContextContent();
                if ($selector === '' || $selector === '0' || preg_match('/(, |[a-zA-Z]).*$/', $selector)) {
                    $this->addContextContent($charValue);
                    return true;
                }

                $this->addError('Selector token "' . $charValue . '" cannot be preceded by "' . $selector . '"');
                return true;
            }

            if (preg_match('/^[#*.0-9a-zA-Z,:()\[\]="\'-^~_%]+/', $charValue)) {
                $this->addContextContent($charValue);
                return true;
            }


            $this->addError('Unexpected selector token "' . $charValue . '"');
            return true;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check selector content part
     * @return bool|null : true if the process should continue, else false, null if this char is not a selector content
     */
    protected function lintSelectorContentChar(string $charValue): ?bool
    {
        if (!$this->assertContext(self::CONTEXT_SELECTOR_CONTENT)) {
            return null;
        }

        $contextContent = $this->getContextContent();
        if (
            ($contextContent === '' || $contextContent === '0' || $contextContent === '{') &&
            $this->getCssLintProperties()->isAllowedIndentationChar($charValue)
        ) {
            return true;
        }

        if ($charValue === '}') {
            $this->resetContext();

            return true;
        }

        if (preg_match('/[-a-zA-Z]+/', $charValue)) {
            $this->setContext(self::CONTEXT_PROPERTY_NAME);
            $this->addContextContent($charValue);
            return true;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check property name part
     * @return bool|null : true if the process should continue, else false, null if this char is not a property name
     */
    protected function lintPropertyNameChar(string $charValue): ?bool
    {
        if (!$this->assertContext(self::CONTEXT_PROPERTY_NAME)) {
            return null;
        }

        if ($charValue === ':') {
            $propertyName = trim($this->getContextContent());

            // Ignore CSS variables (names starting with --)
            if (str_starts_with($propertyName, '--')) {
                $this->setContext(self::CONTEXT_PROPERTY_CONTENT);
                return true;
            }

            // Check if property name exists
            if (!$this->getCssLintProperties()->propertyExists($propertyName)) {
                $this->addError('Unknown CSS property "' . $propertyName . '"');
            }

            $this->setContext(self::CONTEXT_PROPERTY_CONTENT);
            return true;
        }

        $this->addContextContent($charValue);

        if ($charValue === ' ') {
            return true;
        }

        if (in_array(preg_match('/[-a-zA-Z0-9]+/', $charValue), [0, false], true)) {
            $this->addError('Unexpected property name token "' . $charValue . '"');
        }

        return true;
    }

    /**
     * Performs lint for a given char, check property content part
     * @return bool|null : true if the process should continue, else false, null if this char is not a property content
     */
    protected function lintPropertyContentChar(string $charValue): ?bool
    {
        if (!$this->assertContext(self::CONTEXT_PROPERTY_CONTENT)) {
            return null;
        }

        $this->addContextContent($charValue);

        // End of the property content
        if ($charValue === ';') {
            // Check if the ";" is not quoted
            $contextContent = $this->getContextContent();
            if ((substr_count($contextContent, '"') & 1) === 0 && (substr_count($contextContent, "'") & 1) === 0) {
                $this->setContext(self::CONTEXT_SELECTOR_CONTENT);
            }

            if (trim($contextContent) !== '' && trim($contextContent) !== '0') {
                return true;
            }

            $this->addError('Property cannot be empty');
            return true;
        }

        // No property content validation
        return true;
    }

    /**
     * Performs lint for a given char, check nested selector part
     * @return bool|null : true if the process should continue, else false, null if this char is not a nested selector
     */
    protected function lintNestedSelectorChar(string $charValue): ?bool
    {
        // End of nested selector
        if ($this->isNestedSelector() && $this->assertContext(null) && $charValue === '}') {
            $this->setNestedSelector(false);
            return true;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check @import rules
     * @return bool|null : true if the process should continue, else false, null if this char is not an @import rule
     */
    protected function lintImportChar(string $charValue): ?bool
    {
        if ($this->assertContext(null) && $charValue === '@') {
            $this->setContext(self::CONTEXT_SELECTOR);
            $this->addContextContent($charValue);
            return true;
        }

        if ($this->assertContext(self::CONTEXT_SELECTOR) && str_starts_with($this->getContextContent(), '@import')) {
            $this->addContextContent($charValue);

            if ($charValue === ';') {
                $this->resetContext();
                return true;
            }

            return true;
        }

        return null;
    }

    /**
     * Check if a given char is an end of line token
     * @return boolean : true if the char is an end of line token, else false
     */
    protected function isEndOfLine(string $charValue): bool
    {
        return $charValue === "\r" || $charValue === "\n";
    }

    /**
     * Return the current char number
     */
    protected function getCharNumber(): int
    {
        return $this->charNumber;
    }

    /**
     * Assert that previous char is the same as given
     */
    protected function assertPreviousChar(string $charValue): bool
    {
        return $this->previousChar === $charValue;
    }

    /**
     * Reset previous char property
     */
    protected function resetPreviousChar(): self
    {
        $this->previousChar = null;
        return $this;
    }

    /**
     * Set new previous char
     */
    protected function setPreviousChar(string $charValue): self
    {
        $this->previousChar = $charValue;
        return $this;
    }

    /**
     * Return the current line number
     */
    protected function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    /**
     * Add 1 to the current line number
     */
    protected function incrementLineNumber(): self
    {
        ++$this->lineNumber;
        return $this;
    }

    /**
     * Reset current line number property
     */
    protected function resetLineNumber(): self
    {
        $this->lineNumber = 0;
        return $this;
    }

    /**
     * Reset current char number property
     */
    protected function resetCharNumber(): self
    {
        $this->charNumber = 0;
        return $this;
    }

    /**
     * Add 1 to the current char number
     */
    protected function incrementCharNumber(): self
    {
        ++$this->charNumber;
        return $this;
    }

    /**
     * Assert that current context is the same as given
     * @param Context $context
     */
    protected function assertContext($context): bool
    {
        if (is_array($context)) {
            foreach ($context as $tmpContext) {
                if ($this->assertContext($tmpContext)) {
                    return true;
                }
            }

            return false;
        }

        return $this->context === $context;
    }

    /**
     * Reset context property
     */
    protected function resetContext(): self
    {
        return $this->setContext(null);
    }

    /**
     * Set new context
     * @param string|null $context
     */
    protected function setContext($context): self
    {
        $this->context = $context;
        return $this->resetContextContent();
    }

    /**
     * Return context content
     */
    protected function getContextContent(): string
    {
        return $this->contextContent;
    }

    /**
     * Reset context content property
     */
    protected function resetContextContent(): self
    {
        $this->contextContent = '';
        return $this;
    }

    /**
     * Append new value to context content
     */
    protected function addContextContent(string $contextContent): self
    {
        $this->contextContent .= $contextContent;
        return $this;
    }

    /**
     * Add a new error message to the errors property, it adds extra infos to the given error message
     */
    protected function addError(string $error): self
    {
        $this->errors[] = $error . ' (line: ' . $this->getLineNumber() . ', char: ' . $this->getCharNumber() . ')';
        return $this;
    }

    /**
     * Return the errors occurred during the lint process
     * @return Errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Reset the errors property
     */
    protected function resetErrors(): Linter
    {
        $this->errors = [];
        return $this;
    }

    /**
     * Tells if the linter is parsing a nested selector
     */
    protected function isNestedSelector(): bool
    {
        return $this->nestedSelector;
    }

    /**
     * Set the nested selector flag
     */
    protected function setNestedSelector(bool $nestedSelector): void
    {
        $this->nestedSelector = $nestedSelector;
    }

    /**
     * Tells if the linter is parsing a comment
     */
    protected function isComment(): bool
    {
        return $this->comment;
    }

    /**
     * Set the comment flag
     */
    protected function setComment(bool $comment): void
    {
        $this->comment = $comment;
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
}
