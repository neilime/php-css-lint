<?php

declare(strict_types=1);

namespace CssLint\CharLinter;

use CssLint\LintContext;

class EndOfLineCharLinter implements CharLinter
{
    /**
     * Performs lint for a given char, check end fo line part
     * @return boolean|null : true if the process should continue, else false, null if this char is not about end of line
     */
    public function lintChar(string $charValue, LintContext $lintContext): ?bool
    {
        if ($this->isEndOfLineChar($charValue)) {
            $lintContext->setPreviousChar($charValue);
            if ($charValue === "\n") {
                $lintContext
                    ->incrementLineNumber()
                    ->resetCharNumber();
            }
            return true;
        }
        return null;
    }

    /**
     * Check if a given char is an end of line token
     * @return boolean : true if the char is an end of line token, else false
     */
    protected function isEndOfLineChar(string $charValue): bool
    {
        return $charValue === "\r" || $charValue === "\n";
    }
}
