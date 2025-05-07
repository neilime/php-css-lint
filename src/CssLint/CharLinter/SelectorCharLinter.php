<?php

declare(strict_types=1);

namespace CssLint\CharLinter;

use CssLint\LintContext;
use CssLint\LintContextName;
use CssLint\Properties;

class SelectorCharLinter implements CharLinter
{
    public function __construct(
        protected readonly Properties $cssLintProperties,
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
        // Selector must start by #.a-zA-Z
        if ($lintContext->assertCurrentContext(null)) {
            if ($this->cssLintProperties->isAllowedIndentationChar($charValue)) {
                return true;
            }

            if (preg_match('/[@#.a-zA-Z\[\*-:]+/', $charValue)) {
                $lintContext->setCurrentContext(LintContextName::CONTEXT_SELECTOR);
                $lintContext->appendCurrentContent($charValue);
                return true;
            }

            return null;
        }

        // Selector must contains
        if ($lintContext->assertCurrentContext(LintContextName::CONTEXT_SELECTOR)) {
            // A space is valid
            if ($charValue === ' ') {
                $lintContext->appendCurrentContent($charValue);
                return true;
            }

            // Start of selector content
            if ($charValue === '{') {
                // Check if selector if valid
                $selector = trim($lintContext->getCurrentContent());

                // @nested is a specific selector content
                if (
                    // @media selector
                    preg_match('/^@media.+/', $selector)
                    // Keyframes selector
                    || preg_match('/^@.*keyframes.+/', $selector)
                ) {
                    $lintContext->setNestedSelector(true);
                    $lintContext->resetCurrentContext();
                } else {
                    $lintContext->setCurrentContext(LintContextName::CONTEXT_SELECTOR_CONTENT);
                }

                $lintContext->appendCurrentContent($charValue);
                return true;
            }

            // There cannot have two following commas
            if ($charValue === ',') {
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
                if ($selector === '' || $selector === '0' || preg_match('/[a-zA-Z>+,\'"] *$/', $selector)) {
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

        return null;
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
            $this->cssLintProperties->isAllowedIndentationChar($charValue)
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
            $lintContext->setNestedSelector(false);
            return true;
        }

        return null;
    }
}
