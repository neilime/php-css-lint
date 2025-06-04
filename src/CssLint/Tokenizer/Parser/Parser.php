<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @template TToken of Token = Token
 */
interface Parser
{
    /**
     * Parses a tokenizer current context and returns a token or an error if applicable.
     *
     * @param TokenizerContext $tokenizerContext The context of the tokenizer to parse.
     * @return TToken|LintError|null A token if parsing is successful, an error if there is an issue, or null if no action is taken.
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null;

    /**
     * Get the token class that this parser handles
     * @return class-string<TToken>
     */
    public function getHandledTokenClass(): ?string;
}
