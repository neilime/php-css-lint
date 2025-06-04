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
    /**
     * @var non-empty-string
     */
    private static string $COMMENT_DELIMITER_START = '/*';

    /**
     * @var non-empty-string
     */
    private static string $COMMENT_START_LINE_CHAR = '*';

    /**
     * @var non-empty-string
     */
    private static string $COMMENT_DELIMITER_END = '*/';

    /**
     * Performs parsing tokenizer current context, check comment part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->lastCharIsSpace($tokenizerContext)) {
            return null;
        }

        return $this->handleTokenForCurrentContext(
            $tokenizerContext,
            fn(?CommentToken $currentToken = null) => $this->handleCommentToken($tokenizerContext, $currentToken)
        );
    }

    private function handleCommentToken(TokenizerContext $tokenizerContext, ?CommentToken $currentToken): ?CommentToken
    {
        if ($currentToken) {
            $currentToken = $this->updateCommentToken($tokenizerContext, $currentToken);
            if ($this->isCommentEnd($tokenizerContext)) {
                return $currentToken;
            }
            return null;
        }

        if ($this->isCommentStart($tokenizerContext)) {
            return $this->createCommentToken($tokenizerContext);
        }

        return null;
    }

    /**
     * Check if the current char is the end of a comment
     */
    private function isCommentEnd(TokenizerContext $lintContext): bool
    {
        return $lintContext->currentContentEndsWith(self::$COMMENT_DELIMITER_END);
    }

    /**
     * Check if the current char is the start of a comment
     */
    private function isCommentStart(TokenizerContext $tokenizerContext): bool
    {
        return $tokenizerContext->currentContentEndsWith(self::$COMMENT_DELIMITER_START);
    }

    private function createCommentToken(TokenizerContext $tokenizerContext): CommentToken
    {
        $currentContent = $tokenizerContext->getCurrentContent();
        return new ($this->getHandledTokenClass())(
            $currentContent,
            CommentToken::calculateStartPosition($tokenizerContext),
        );
    }

    private function updateCommentToken(TokenizerContext $tokenizerContext, CommentToken $commentToken): CommentToken
    {
        $commentToken->setValue($this->getCommentValue($tokenizerContext));
        return $commentToken;
    }

    private function getCommentValue(TokenizerContext $tokenizerContext): string
    {
        $commentValue = trim($tokenizerContext->getCurrentContent());
        $commentValue = self::removeStartingString($commentValue, self::$COMMENT_DELIMITER_START);
        $commentValue = self::removeEndingString($commentValue, self::$COMMENT_DELIMITER_END);
        $commentValue = trim($commentValue);

        foreach (self::$END_OF_LINE_CHARS as $endOfLineChar) {
            $commentLines = explode($endOfLineChar, $commentValue);
            $commentLines = array_map(
                fn(string $line) => trim(self::removeStartingString(trim($line), self::$COMMENT_START_LINE_CHAR)),
                $commentLines
            );
            $commentValue = implode($endOfLineChar, $commentLines);
        }

        return $commentValue;
    }

    public function getHandledTokenClass(): string
    {
        return CommentToken::class;
    }
}
