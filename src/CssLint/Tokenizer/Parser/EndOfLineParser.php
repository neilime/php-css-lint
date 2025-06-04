<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @extends AbstractParser<null>
 */
class EndOfLineParser extends AbstractParser
{
    public static array $END_OF_LINE_CHARS = ["\r\n", "\n"];

    /**
     * Performs parsing tokenizer current context, check comment part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->isSpace($tokenizerContext)) {
            return null;
        }

        if ($this->isEndOfLineChar($tokenizerContext)) {
            $tokenizerContext
                ->incrementLineNumber()
                ->resetCharNumber();
        }

        return null;
    }

    /**
     * Check if a given char is an end of line token
     * @return boolean : true if the char is an end of line token, else false
     */
    protected function isEndOfLineChar(TokenizerContext $tokenizerContext): bool
    {
        foreach (self::$END_OF_LINE_CHARS as $endOfLineChar) {
            if ($tokenizerContext->getNthLastChars(strlen($endOfLineChar)) === $endOfLineChar) {
                return true;
            }
        }

        return false;
    }

    public function getHandledTokenClass(): ?string
    {
        return null;
    }
}
