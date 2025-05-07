<?php

declare(strict_types=1);

namespace CssLint\CharLinter;

use CssLint\LintContext;
use CssLint\LintContextName;
use CssLint\LintConfiguration;

class PropertyCharLinter implements CharLinter
{
    public function __construct(
        protected readonly LintConfiguration $lintConfiguration,
    ) {}

    /**
     * Performs lint for a given char, check property part
     * @return boolean|null : true if the process should continue, else false, null if this char is not about selector
     */
    public function lintChar(string $charValue, LintContext $lintContext): ?bool
    {
        if (is_bool($lintPropertyNameChar = $this->lintPropertyNameChar($charValue, $lintContext))) {
            return $lintPropertyNameChar;
        }

        if (is_bool($lintPropertyContentChar = $this->lintPropertyContentChar($charValue, $lintContext))) {
            return $lintPropertyContentChar;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check property name part
     * @return bool|null : true if the process should continue, else false, null if this char is not a property name
     */
    protected function lintPropertyNameChar(string $charValue, LintContext $lintContext): ?bool
    {
        if (!$lintContext->assertCurrentContext(LintContextName::CONTEXT_PROPERTY_NAME)) {
            return null;
        }

        if ($charValue === ':') {
            $propertyName = trim($lintContext->getCurrentContent());

            // Ignore CSS variables (names starting with --)
            if (str_starts_with($propertyName, '--')) {
                $lintContext->setCurrentContext(LintContextName::CONTEXT_PROPERTY_CONTENT);
                return true;
            }

            // Check if property name exists
            if (!$this->lintConfiguration->propertyExists($propertyName)) {
                $lintContext->addError('Unknown CSS property "' . $propertyName . '"');
            }

            $lintContext->setCurrentContext(LintContextName::CONTEXT_PROPERTY_CONTENT);
            return true;
        }

        $lintContext->appendCurrentContent($charValue);

        if ($charValue === ' ') {
            return true;
        }

        if (in_array(preg_match('/[-a-zA-Z0-9]+/', $charValue), [0, false], true)) {
            $lintContext->addError('Unexpected property name token "' . $charValue . '"');
        }

        return true;
    }

    /**
     * Performs lint for a given char, check property content part
     * @return bool|null : true if the process should continue, else false, null if this char is not a property content
     */
    protected function lintPropertyContentChar(string $charValue, LintContext $lintContext): ?bool
    {
        if (!$lintContext->assertCurrentContext(LintContextName::CONTEXT_PROPERTY_CONTENT)) {
            return null;
        }

        $lintContext->appendCurrentContent($charValue);

        // End of the property content
        if ($charValue === ';' || $charValue === '}') {
            // Check if the char is not quoted
            $contextContent = $lintContext->getCurrentContent();
            if ((substr_count($contextContent, '"') & 1) === 0 && (substr_count($contextContent, "'") & 1) === 0) {
                $lintContext->setCurrentContext(LintContextName::CONTEXT_SELECTOR_CONTENT);
            }

            if (trim($contextContent) !== '' && trim($contextContent) !== '0') {
                if ($charValue === '}') {
                    $lintContext->resetCurrentContext();
                }

                return true;
            }

            $lintContext->addError('Property cannot be empty');
            return true;
        }

        // No property content validation
        return true;
    }
}
