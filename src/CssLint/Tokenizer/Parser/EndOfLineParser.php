<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

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
    public function parseCurrentContext(TokenizerContext $tokenizerContext): null
    {
        if ($this->isEndOfLineChar($tokenizerContext)) {
            $tokenizerContext->incrementLine();
        }

        return null;
    }
}
