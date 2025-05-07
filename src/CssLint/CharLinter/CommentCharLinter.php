<?php

declare(strict_types=1);

namespace CssLint\CharLinter;

use CssLint\LintContext;

class CommentCharLinter implements CharLinter
{
    private static $COMMENT_DELIMITER = '/';

    /**
     * Performs lint for a given char, check comment part
     * @return boolean|null : true if the process should continue, else false, null if this char is not about comment
     */
    public function lintChar(string $charValue, LintContext $lintContext): ?bool
    {
        // Manage comment context
        if ($lintContext->isComment()) {
            if ($this->isCommentEnd($charValue, $lintContext)) {
                $lintContext->setComment(false);
            }
            $lintContext->setPreviousChar($charValue);
            return true;
        }

        // First char for a comment
        if ($this->isCommentDelimiter($charValue)) {
            return true;
        }

        // First char for a comment
        if ($this->isCommentStart($charValue, $lintContext)) {
            // End of comment
            $lintContext->setComment(true);
            return true;
        }

        return null;
    }

    /**
     * Check if the current char is a comment
     * @param string $charValue
     * @return bool
     */
    private function isCommentDelimiter(string $charValue): bool
    {
        return $charValue === self::$COMMENT_DELIMITER;
    }

    /**
     * Check if the current char is the end of a comment
     * @param string $charValue
     * @return bool
     */
    private function isCommentEnd(string $charValue, LintContext $lintContext): bool
    {
        return $this->isCommentDelimiter($charValue) && $lintContext->assertPreviousChar('*');
    }

    /**
     * Check if the current char is the start of a comment
     * @param string $charValue
     * @return bool
     */
    private function isCommentStart(string $charValue, LintContext $lintContext): bool
    {
        return $charValue === '*' && $lintContext->assertPreviousChar(self::$COMMENT_DELIMITER);
    }
}
