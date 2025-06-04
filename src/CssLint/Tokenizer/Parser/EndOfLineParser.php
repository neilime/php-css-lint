<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\Token;
use CssLint\Token\WhitespaceToken;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @extends AbstractParser<Token>
 */
class EndOfLineParser extends AbstractParser
{
    /**
     * @return class-string<Token>
     */
    public function getHandledTokenClass(): string
    {
        return WhitespaceToken::class;
    }

    /**
     * Performs parsing tokenizer current context, check end of line part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->isEndOfLineChar($tokenizerContext)) {
            $tokenizerContext->incrementLine();
        }

        return null;
    }

    /**
     * Check if a given char is an end of line token
     * @return boolean : true if the char is an end of line token, else false
     */
    private function isEndOfLineChar(TokenizerContext $tokenizerContext): bool
    {
        foreach (self::$END_OF_LINE_CHARS as $endOfLineChar) {
            if ($tokenizerContext->currentContentEndsWith($endOfLineChar)) {
                return true;
            }
        }

        return false;
    }
}
