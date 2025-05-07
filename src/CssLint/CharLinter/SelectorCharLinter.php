<?php

declare(strict_types=1);

namespace CssLint\CharLinter;

use CssLint\LintContext;
use CssLint\LintContextName;
use CssLint\LintConfiguration;

class SelectorCharLinter implements CharLinter
{
    public function __construct(
        protected readonly LintConfiguration $lintConfiguration,
    ) {}

    /**
     * Performs lint for a given char, check selector part
     * @return boolean|null : true if the process should continue, else false, null if this char is not about selector
     */
    public function lintChar(string $charValue, LintContext $lintContext): ?bool
    {
        if (is_bool($lintSelectorChar = $this->lintSelectorNameChar($charValue, $lintContext))) {
            return $lintSelectorChar;
        }

        if (is_bool($lintSelectorContentChar = $this->lintSelectorContentChar($charValue, $lintContext))) {
            return $lintSelectorContentChar;
        }

        if (is_bool($lintNestedSelectorChar = $this->lintNestedSelectorChar($charValue, $lintContext))) {
            return $lintNestedSelectorChar;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check selector part
     * @return boolean|null : true if the process should continue, else false, null if this char is not about selector
     */
    protected function lintSelectorNameChar(string $charValue, LintContext $lintContext): ?bool
    {
        if ($lintContext->assertCurrentContext(null)) {
            return $this->lintSelectorNameStart($charValue, $lintContext);
        }

        if (!$lintContext->assertCurrentContext(LintContextName::CONTEXT_SELECTOR)) {
            return null;
        }

        // A space is valid
        if ($charValue === ' ') {
            $lintContext->appendCurrentContent($charValue);
            return true;
        }

        if (is_bool($lintSelectorContentStart = $this->lintSelectorContentStart($charValue, $lintContext))) {
            return $lintSelectorContentStart;
        }

        return $this->lintSelectorNameContent($charValue, $lintContext);
    }


    /**
     * Performs lint for a given char, check selector content part
     * @return bool|null : true if the process should continue, else false, null if this char is not a selector content
     */
    protected function lintSelectorContentChar(string $charValue, LintContext $lintContext): ?bool
    {
        if (!$lintContext->assertCurrentContext(LintContextName::CONTEXT_SELECTOR_CONTENT)) {
            return null;
        }

        $contextContent = $lintContext->getCurrentContent();
        if (
            ($contextContent === '' || $contextContent === '0' || $contextContent === '{') &&
            $this->lintConfiguration->isAllowedIndentationChar($charValue)
        ) {
            return true;
        }

        if ($charValue === '}') {
            $lintContext->resetCurrentContext();

            return true;
        }

        if (preg_match('/[-a-zA-Z]+/', $charValue)) {
            $lintContext->setCurrentContext(LintContextName::CONTEXT_PROPERTY_NAME);
            $lintContext->appendCurrentContent($charValue);
            return true;
        }

        return null;
    }

    /**
     * Performs lint for a given char, check nested selector part
     * @return bool|null : true if the process should continue, else false, null if this char is not a nested selector
     */
    protected function lintNestedSelectorChar(string $charValue, LintContext $lintContext): ?bool
    {
        // End of nested selector
        if ($lintContext->isNestedSelector() && $lintContext->assertCurrentContext(null) && $charValue === '}') {
            $lintContext->decrementNestedSelector();
            return true;
        }

        return null;
    }

    protected function lintSelectorNameStart(string $charValue, LintContext $lintContext): ?bool
    {
        if ($this->lintConfiguration->isAllowedIndentationChar($charValue)) {
            return true;
        }

        if (preg_match('/[@#.a-zA-Z\[\*-:]+/', $charValue)) {
            $lintContext->setCurrentContext(LintContextName::CONTEXT_SELECTOR);
            $lintContext->appendCurrentContent($charValue);
            return true;
        }

        return null;
    }

    protected function lintSelectorContentStart(string $charValue, LintContext $lintContext): ?bool
    {
        if ($charValue === ';') {
            $this->lintSelectorName($lintContext);
            $lintContext->resetCurrentContext();
            return null;
        }

        if ($charValue !== '{') {
            return null;
        }

        $this->lintSelectorName($lintContext);

        // Check if selector is a nested selector
        $atRuleSelector = $this->getSelectorNameAtRuleIfexist($lintContext->getCurrentContent());
        if ($atRuleSelector !== null && $atRuleSelector !== '' && $atRuleSelector !== '0') {
            $lintContext->incrementNestedSelector();
            $lintContext->resetCurrentContext();
        } else {
            $lintContext->setCurrentContext(LintContextName::CONTEXT_SELECTOR_CONTENT);
        }

        $lintContext->appendCurrentContent($charValue);
        return true;
    }

    protected function lintSelectorNameContent(string $charValue, LintContext $lintContext): bool
    {
        // There cannot have two following commas
        if ($charValue === ',') {
            $this->lintSelectorName($lintContext);
            $selector = $lintContext->getCurrentContent();

            if ($selector === '' || $selector === '0' || in_array(preg_match('/, *$/', $selector), [0, false], true)) {
                $lintContext->appendCurrentContent($charValue);
                return true;
            }

            $lintContext->addError(sprintf(
                'Selector token %s cannot be preceded by "%s"',
                json_encode($charValue),
                $selector
            ));
            return false;
        }

        // Wildcard and hash
        if (in_array($charValue, ['*', '#'], true)) {
            $selector = $lintContext->getCurrentContent();
            if ($selector === '' || $selector === '0' || preg_match('/[a-zA-Z>+,\'\(\):"] *$/', $selector)) {
                $lintContext->appendCurrentContent($charValue);
                return true;
            }

            $lintContext->addError('Selector token "' . $charValue . '" cannot be preceded by "' . $selector . '"');
            return true;
        }

        // Dot
        if ($charValue === '.') {
            $selector = $lintContext->getCurrentContent();
            if ($selector === '' || $selector === '0' || preg_match('/(, |[a-zA-Z]).*$/', $selector)) {
                $lintContext->appendCurrentContent($charValue);
                return true;
            }

            $lintContext->addError('Selector token "' . $charValue . '" cannot be preceded by "' . $selector . '"');
            return true;
        }

        if (preg_match('/^[#*.0-9a-zA-Z,:()\[\]="\'-^~_%]+/', $charValue)) {
            $lintContext->appendCurrentContent($charValue);
            return true;
        }

        $lintContext->addError('Unexpected selector token "' . $charValue . '"');
        return true;
    }

    protected function lintSelectorName(LintContext $lintContext): void
    {
        $selector = $lintContext->getCurrentContent();

        $atRuleSelector = $this->getSelectorNameAtRuleIfexist($selector);
        if ($atRuleSelector === null) {
            return;
        }

        if ($this->lintConfiguration->atRuleExists($atRuleSelector)) {
            return;
        }

        $lintContext->addError(sprintf(
            'Selector token %s is not a valid at-rule',
            json_encode($atRuleSelector)
        ));
    }

    protected function getSelectorNameAtRuleIfexist(string $selector): ?string
    {
        if (in_array(preg_match('/^@([a-z]+) .*/', trim($selector), $matches), [0, false], true)) {
            // Not an at-rule
            return null;
        }

        return $matches[1];
    }
}
