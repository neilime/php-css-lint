<?php

declare(strict_types=1);

namespace CssLint\CharLinter;

use CssLint\LintContext;
use CssLint\LintContextName;

class ImportCharLinter implements CharLinter
{
    private static $IMPORT_RULE = '@import';

    /**
     * Performs lint for a given char, check @import rules
     * @return bool|null : true if the process should continue, else false, null if this char is not an @import rule
     */
    public function lintChar(string $charValue, LintContext $lintContext): ?bool
    {
        if ($this->isImportStart($charValue, $lintContext)) {
            $lintContext->setCurrentContext(LintContextName::CONTEXT_SELECTOR);
            $lintContext->appendCurrentContent($charValue);
            return true;
        }

        if ($this->isImportContext($lintContext)) {
            $lintContext->appendCurrentContent($charValue);

            if ($charValue === ';' && $lintContext->assertPreviousChar(')')) {
                $lintContext->resetCurrentContext();
                return true;
            }

            return true;
        }

        return null;
    }

    private function isImportStart(string $charValue, LintContext $lintContext): bool
    {
        return $lintContext->assertCurrentContext(null) && $charValue === self::$IMPORT_RULE[0];
    }

    private function isImportContext(LintContext $lintContext): bool
    {
        return $lintContext->assertCurrentContext(LintContextName::CONTEXT_SELECTOR) && str_starts_with($lintContext->getCurrentContent(), self::$IMPORT_RULE);
    }
}
