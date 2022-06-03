<?php

namespace CssLint;

class Linter
{
    public const CONTEXT_SELECTOR = 'selector';
    public const CONTEXT_SELECTOR_CONTENT = 'selector content';
    public const CONTEXT_NESTED_SELECTOR_CONTENT = 'nested selector content';
    public const CONTEXT_PROPERTY_NAME = 'property name';
    public const CONTEXT_PROPERTY_CONTENT = 'property content';

    /**
     * Class to provide css properties knowledge
     * @var \CssLint\Properties|null
     */
    protected $cssLintProperties;

    /**
     * Errors occurred during the lint process
     * @var array|null
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
     * @param \CssLint\Properties $oProperties (optional) an instance of the "\CssLint\Properties" helper
     */
    public function __construct(\CssLint\Properties $oProperties = null)
    {
        if ($oProperties) {
            $this->setCssLintProperties($oProperties);
        }
    }

    /**
     * Performs lint on a given string
     * @param string $sString
     * @return boolean : true if the string is a valid css string, false else
     */
    public function lintString(string $sString): bool
    {
        $this->initLint();
        $iIterator = 0;
        while (isset($sString[$iIterator])) {
            if ($this->lintChar($sString[$iIterator]) === false) {
                return false;
            }
            $iIterator++;
        }

        if (!$this->assertContext(null)) {
            $this->addError('Unterminated "' . $this->context . '"');
        }

        return !$this->getErrors();
    }

    /**
     * Performs lint for a given file path
     * @param string $sFilePath : a path of an existing and readable file
     * @return boolean : true if the file is a valid css file, else false
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function lintFile(string $sFilePath): bool
    {
        if (!file_exists($sFilePath)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument "$sFilePath" "%s" is not an existing file path',
                $sFilePath
            ));
        }

        if (!is_readable($sFilePath)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument "$sFilePath" "%s" is not a readable file path',
                $sFilePath
            ));
        }

        $rFileHandle = fopen($sFilePath, 'r');
        if ($rFileHandle === false) {
            throw new \RuntimeException('An error occurred while opening file "' . $sFilePath . '"');
        }

        $this->initLint();

        while (($sChar = fgetc($rFileHandle)) !== false) {
            if ($this->lintChar($sChar) === false) {
                fclose($rFileHandle);
                return false;
            }
        }

        if (!feof($rFileHandle)) {
            throw new \RuntimeException('An error occurred while reading file "' . $sFilePath . '"');
        }
        fclose($rFileHandle);

        if (!$this->assertContext(null)) {
            $this->addError('Unterminated "' . $this->context . '"');
        }

        return !$this->getErrors();
    }

    /**
     * Initialize linter, reset all process properties
     * @return \CssLint\Linter
     */
    protected function initLint()
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
     * @param string $sChar
     * @return boolean : true if the process should continue, else false
     */
    protected function lintChar(string $sChar): ?bool
    {
        $this->incrementCharNumber();
        if ($this->isEndOfLine($sChar)) {
            $this->setPreviousChar($sChar);
            if ($sChar === "\n") {
                $this->incrementLineNumber()->resetCharNumber();
            }
            return true;
        }

        if (is_bool($bLintCommentChar = $this->lintCommentChar($sChar))) {
            $this->setPreviousChar($sChar);
            return $bLintCommentChar;
        }

        if (is_bool($bLintSelectorChar = $this->lintSelectorChar($sChar))) {
            $this->setPreviousChar($sChar);
            return $bLintSelectorChar;
        }

        if (is_bool($bLintSelectorContentChar = $this->lintSelectorContentChar($sChar))) {
            $this->setPreviousChar($sChar);
            return $bLintSelectorContentChar;
        }

        if (is_bool($bLintPropertyNameChar = $this->lintPropertyNameChar($sChar))) {
            $this->setPreviousChar($sChar);
            return $bLintPropertyNameChar;
        }

        if (is_bool($bLintPropertyContentChar = $this->lintPropertyContentChar($sChar))) {
            $this->setPreviousChar($sChar);
            return $bLintPropertyContentChar;
        }

        if (is_bool($bLintNestedSelectorChar = $this->lintNestedSelectorChar($sChar))) {
            $this->setPreviousChar($sChar);
            return $bLintNestedSelectorChar;
        }

        $this->addError('Unexpected char ' . json_encode($sChar));
        $this->setPreviousChar($sChar);
        return false;
    }

    /**
     * Performs lint for a given char, check comment part
     * @param string $sChar
     * @return boolean|null : true if the process should continue, else false, null if this char is not about comment
     */
    protected function lintCommentChar(string $sChar): ?bool
    {
        // Manage comment context
        if ($this->isComment()) {
            if ($sChar === '/' && $this->assertPreviousChar('*')) {
                $this->setComment(false);
            }
            $this->setPreviousChar($sChar);
            return true;
        }
        // First char for a comment
        if ($sChar === '/') {
            return true;
        } elseif ($sChar === '*' && $this->assertPreviousChar('/')) {
            // End of comment
            $this->setComment(true);
            return true;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check selector part
     * @param string $sChar
     * @return boolean|null : true if the process should continue, else false, null if this char is not about selector
     */
    protected function lintSelectorChar(string $sChar): ?bool
    {
        // Selector must start by #.a-zA-Z
        if ($this->assertContext(null)) {
            if ($this->getCssLintProperties()->isAllowedIndentationChar($sChar)) {
                return true;
            }

            if (preg_match('/[@#.a-zA-Z\[\*-:]+/', $sChar)) {
                $this->setContext(self::CONTEXT_SELECTOR);
                $this->addContextContent($sChar);
                return true;
            }
            return null;
        }
        // Selector must contains
        if ($this->assertContext(self::CONTEXT_SELECTOR)) {
            // A space is valid
            if ($sChar === ' ') {
                $this->addContextContent($sChar);
                return true;
            }
            // Start of selector content
            if ($sChar === '{') {
                // Check if selector if valid
                $sSelector = trim($this->getContextContent());

                // @nested is a specific selector content
                if (
                    // @media selector
                    preg_match('/^@media.+/', $sSelector)
                    // Keyframes selector
                    || preg_match('/^@.*keyframes.+/', $sSelector)
                ) {
                    $this->setNestedSelector(true);
                    $this->resetContext();
                } else {
                    $this->setContext(self::CONTEXT_SELECTOR_CONTENT);
                }
                $this->addContextContent($sChar);
                return true;
            }

            // There cannot have two following commas
            if ($sChar === ',') {
                $sSelector = $this->getContextContent();
                if (!$sSelector || !preg_match('/, *$/', $sSelector)) {
                    $this->addContextContent($sChar);
                    return true;
                }
                $this->addError(sprintf(
                    'Selector token %s cannot be preceded by "%s"',
                    json_encode($sChar),
                    $sSelector
                ));
                return false;
            }

            // Wildcard and hash
            if (in_array($sChar, ['*', '#'], true)) {
                $sSelector = $this->getContextContent();
                if (!$sSelector || preg_match('/[a-zA-Z>,\'"] *$/', $sSelector)) {
                    $this->addContextContent($sChar);
                    return true;
                }
                $this->addError('Selector token "' . $sChar . '" cannot be preceded by "' . $sSelector . '"');
                return true;
            }
            // Dot
            if ($sChar === '.') {
                $sSelector = $this->getContextContent();
                if (!$sSelector || preg_match('/(, |[a-zA-Z]).*$/', $sSelector)) {
                    $this->addContextContent($sChar);
                    return true;
                }
                $this->addError('Selector token "' . $sChar . '" cannot be preceded by "' . $sSelector . '"');
                return true;
            }
            if (preg_match('/^[#*.0-9a-zA-Z,:()\[\]="\'-^~_%]+/', $sChar)) {
                $this->addContextContent($sChar);
                return true;
            }


            $this->addError('Unexpected selector token "' . $sChar . '"');
            return true;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check selector content part
     * @param string $sChar
     * @return bool|null : true if the process should continue, else false, null if this char is not a selector content
     */
    protected function lintSelectorContentChar(string $sChar): ?bool
    {
        if (!$this->assertContext(self::CONTEXT_SELECTOR_CONTENT)) {
            return null;
        }

        $sContextContent = $this->getContextContent();
        if (
            (!$sContextContent || $sContextContent === '{') &&
            $this->getCssLintProperties()->isAllowedIndentationChar($sChar)
        ) {
            return true;
        }

        if ($sChar === '}') {
            if ($this->isNestedSelector()) {
                $this->resetContext();
            } else {
                $this->resetContext();
            }
            return true;
        }

        if (preg_match('/[-a-zA-Z]+/', $sChar)) {
            $this->setContext(self::CONTEXT_PROPERTY_NAME);
            $this->addContextContent($sChar);
            return true;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check property name part
     * @param string $sChar
     * @return bool|null : true if the process should continue, else false, null if this char is not a property name
     */
    protected function lintPropertyNameChar(string $sChar): ?bool
    {
        if (!$this->assertContext(self::CONTEXT_PROPERTY_NAME)) {
            return null;
        }

        if ($sChar === ':') {
            // Check if property name exists
            $sPropertyName = trim($this->getContextContent());

            if (!$this->getCssLintProperties()->propertyExists($sPropertyName)) {
                $this->addError('Unknown CSS property "' . $sPropertyName . '"');
            }
            $this->setContext(self::CONTEXT_PROPERTY_CONTENT);
            return true;
        }

        $this->addContextContent($sChar);

        if ($sChar === ' ') {
            return true;
        }

        if (!preg_match('/[-a-zA-Z]+/', $sChar)) {
            $this->addError('Unexpected property name token "' . $sChar . '"');
        }
        return true;
    }

    /**
     * Performs lint for a given char, check property content part
     * @param string $sChar
     * @return bool|null : true if the process should continue, else false, null if this char is not a property content
     */
    protected function lintPropertyContentChar(string $sChar): ?bool
    {
        if (!$this->assertContext(self::CONTEXT_PROPERTY_CONTENT)) {
            return null;
        }

        $this->addContextContent($sChar);

        // End of the property content
        if ($sChar === ';') {
            // Check if the ";" is not quoted
            $sContextContent = $this->getContextContent();
            if (!(substr_count($sContextContent, '"') & 1) && !(substr_count($sContextContent, '\'') & 1)) {
                $this->setContext(self::CONTEXT_SELECTOR_CONTENT);
            }
            if (trim($sContextContent)) {
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
     * @param string $sChar
     * @return bool|null : true if the process should continue, else false, null if this char is not a nested selector
     */
    protected function lintNestedSelectorChar(string $sChar): ?bool
    {
        // End of nested selector
        if ($this->isNestedSelector() && $this->assertContext(null) && $sChar === '}') {
            $this->setNestedSelector(false);
            return true;
        }

        return null;
    }

    /**
     * Check if a given char is an end of line token
     * @param string $sChar
     * @return boolean : true if the char is an end of line token, else false
     */
    protected function isEndOfLine(string $sChar): bool
    {
        return $sChar === "\r" || $sChar === "\n";
    }

    /**
     * Return the current char number
     * @return int
     */
    protected function getCharNumber(): int
    {
        return $this->charNumber;
    }

    /**
     * Assert that previous char is the same as given
     * @param string $sChar
     * @return boolean
     */
    protected function assertPreviousChar(string $sChar): bool
    {
        return $this->previousChar === $sChar;
    }

    /**
     * Reset previous char property
     * @return \CssLint\Linter
     */
    protected function resetPreviousChar(): Linter
    {
        $this->previousChar = null;
        return $this;
    }

    /**
     * Set new previous char
     * @param string $sChar
     * @return \CssLint\Linter
     */
    protected function setPreviousChar(string $sChar): Linter
    {
        $this->previousChar = $sChar;
        return $this;
    }

    /**
     * Return the current line number
     * @return int
     */
    protected function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    /**
     * Add 1 to the current line number
     * @return \CssLint\Linter
     */
    protected function incrementLineNumber(): Linter
    {
        $this->lineNumber++;
        return $this;
    }

    /**
     * Reset current line number property
     * @return \CssLint\Linter
     */
    protected function resetLineNumber(): Linter
    {
        $this->lineNumber = 0;
        return $this;
    }

    /**
     * Reset current char number property
     * @return \CssLint\Linter
     */
    protected function resetCharNumber(): Linter
    {
        $this->charNumber = 0;
        return $this;
    }

    /**
     * Add 1 to the current char number
     * @return \CssLint\Linter
     */
    protected function incrementCharNumber(): Linter
    {
        $this->charNumber++;
        return $this;
    }

    /**
     * Assert that current context is the same as given
     * @param string|array|null $sContext
     * @return boolean
     */
    protected function assertContext($sContext): bool
    {
        if (is_array($sContext)) {
            foreach ($sContext as $sTmpContext) {
                if ($this->assertContext($sTmpContext)) {
                    return true;
                }
            }
            return false;
        }
        return $this->context === $sContext;
    }

    /**
     * Reset context property
     * @return \CssLint\Linter
     */
    protected function resetContext(): Linter
    {
        return $this->setContext(null);
    }

    /**
     * Set new context
     * @param string|null $sContext
     * @return \CssLint\Linter
     */
    protected function setContext($sContext): Linter
    {
        $this->context = $sContext;
        return $this->resetContextContent();
    }

    /**
     * Return context content
     * @return string
     */
    protected function getContextContent(): string
    {
        return $this->contextContent;
    }

    /**
     * Reset context content property
     * @return \CssLint\Linter
     */
    protected function resetContextContent(): Linter
    {
        $this->contextContent = '';
        return $this;
    }

    /**
     * Append new value to context content
     * @param string $sContextContent
     * @return \CssLint\Linter
     */
    protected function addContextContent($sContextContent): Linter
    {
        $this->contextContent .= $sContextContent;
        return $this;
    }

    /**
     * Add a new error message to the errors property, it adds extra infos to the given error message
     * @param string $sError
     * @return \CssLint\Linter
     */
    protected function addError($sError): Linter
    {
        $this->errors[] = $sError . ' (line: ' . $this->getLineNumber() . ', char: ' . $this->getCharNumber() . ')';
        return $this;
    }

    /**
     * Return the errors occurred during the lint process
     * @return array
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Reset the errors property
     * @return \CssLint\Linter
     */
    protected function resetErrors(): Linter
    {
        $this->errors = null;
        return $this;
    }

    /**
     * Tells if the linter is parsing a nested selector
     * @return boolean
     */
    protected function isNestedSelector(): bool
    {
        return $this->nestedSelector;
    }

    /**
     * Set the nested selector flag
     * @param boolean $bNestedSelector
     */
    protected function setNestedSelector(bool $bNestedSelector): void
    {
        $this->nestedSelector = $bNestedSelector;
    }

    /**
     * Tells if the linter is parsing a comment
     * @return boolean
     */
    protected function isComment(): bool
    {
        return $this->comment;
    }

    /**
     * Set the comment flag
     * @param boolean $bComment
     */
    protected function setComment(bool $bComment): void
    {
        $this->comment = $bComment;
    }

    /**
     * Return an instance of the "\CssLint\Properties" helper, initialize a new one if not define already
     * @return \CssLint\Properties
     */
    public function getCssLintProperties(): \CssLint\Properties
    {
        if (!$this->cssLintProperties) {
            $this->setCssLintProperties(new \CssLint\Properties());
        }
        return $this->cssLintProperties;
    }

    /**
     * Set an instance of the "\CssLint\Properties" helper
     */
    public function setCssLintProperties(\CssLint\Properties $oCssLintProperties): void
    {
        $this->cssLintProperties = $oCssLintProperties;
    }
}
