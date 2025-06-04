<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\CommentToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @extends AbstractParser<CommentToken>
 */
class CommentParser extends AbstractParser
{
    private static string $COMMENT_DELIMITER_START = '/*';
    private static string $COMMENT_DELIMITER_END = '*/';

    /**
     * Performs parsing tokenizer current context, check comment part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->isSpace($tokenizerContext)) {
            return null;
        }

        // Manage comment context
        if ($tokenizerContext->assertCurrentToken($this->getHandledTokenClass())) {
            if ($this->isCommentEnd($tokenizerContext)) {
                $token = $this->createCommentToken($tokenizerContext);
                $token->setEnd($this->getTokenEnd($token, $tokenizerContext));
                return $token;
            }
            return null;
        }

        if (!$tokenizerContext->assertCurrentToken(null)) {
            // If we are in a different token context, we do not handle comments
            return null;
        }

        if ($this->isCommentStart($tokenizerContext)) {
            $tokenizerContext->setCurrentToken($this->createCommentToken($tokenizerContext));
        }

        return null;
    }

    /**
     * Check if the current char is the end of a comment
     */
    private function isCommentEnd(TokenizerContext $lintContext): bool
    {
        $currentContent = $lintContext->getCurrentContent();
        return str_ends_with($currentContent, self::$COMMENT_DELIMITER_END);
    }

    /**
     * Check if the current char is the start of a comment
     */
    private function isCommentStart(TokenizerContext $lintContext): bool
    {
        $currentContent = $lintContext->getCurrentContent();
        return str_ends_with($currentContent, self::$COMMENT_DELIMITER_START);
    }

    private function createCommentToken(TokenizerContext $tokenizerContext): CommentToken|LintError|null
    {
        $currentContent = $tokenizerContext->getCurrentContent();
        $charNumber = $tokenizerContext->getCharNumber() + 1;
        return new ($this->getHandledTokenClass())(
            $currentContent,
            $tokenizerContext->getLineNumber(),
            $charNumber - strlen($currentContent),
        );
    }

    public function getHandledTokenClass(): string
    {
        return CommentToken::class;
    }
}
