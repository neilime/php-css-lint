<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\BlockToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @extends AbstractParser<BlockToken>
 */
class BlockParser extends AbstractParser
{
    /**
     * @var non-empty-string
     */
    public static string $BLOCK_START = '{';

    /**
     * @var non-empty-string
     */
    private static string $BLOCK_END = '}';

    /**
     * Performs parsing tokenizer current context, check block part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->lastCharIsSpace($tokenizerContext)) {
            return null;
        }

        $currentToken = $tokenizerContext->getCurrentToken();

        if ($currentToken !== null && $currentToken::class !== BlockToken::class) {
            // If we are in a different token context, we do not handle blocks
            return null;
        }

        if ($currentToken === null) {
            if (static::isBlockStart($tokenizerContext)) {
                $tokenizerContext->setCurrentToken($this->createBlockToken($tokenizerContext));
                return null;
            }
            return null;
        }

        // If we encounter a block end, we finalize the current block token
        if (static::isBlockEnd($tokenizerContext)) {
            return $this->updateBlockToken($tokenizerContext, $currentToken);
        }

        return null;
    }

    /**
     * Check if the current char is the start of a block
     */
    public static function isBlockStart(TokenizerContext $tokenizerContext): bool
    {
        $currentContent = $tokenizerContext->getCurrentContent();

        // Ensure we have a valid block start
        $lastChars = $tokenizerContext->getNthLastChars(strlen(self::$BLOCK_START));
        if ($lastChars !== self::$BLOCK_START) {
            return false;
        }

        // Make sure we're not inside a string or comment
        $contentBeforeBlock = substr($currentContent, 0, -1);
        $openQuotes = substr_count($contentBeforeBlock, '"') + substr_count($contentBeforeBlock, "'");
        if ($openQuotes % 2 !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if the current char is the end of a block
     */
    public static function isBlockEnd(TokenizerContext $tokenizerContext, bool $fullContent = false): bool
    {
        $value = $fullContent ? trim($tokenizerContext->getCurrentContent()) : $tokenizerContext->getNthLastChars(strlen(self::$BLOCK_END));

        return $value === self::$BLOCK_END;
    }

    public static function getBlockContent(TokenizerContext $tokenizerContext): string
    {
        $content = trim($tokenizerContext->getCurrentContent());
        $content = ltrim($content, self::$BLOCK_START);
        $content = rtrim($content, self::$BLOCK_END);
        return trim($content);
    }

    /**
     * Creates a BlockToken from the current context
     */
    private function createBlockToken(TokenizerContext $tokenizerContext): BlockToken
    {
        // Get the content without the block delimiters
        $content = $tokenizerContext->getCurrentContent();
        $content = rtrim($content, self::$BLOCK_END);
        $content = rtrim($content, self::$BLOCK_START);
        $content = trim($content);

        // Create token with the block content and property tokens
        return new ($this->getHandledTokenClass())(
            [],
            BlockToken::calculateStartPosition($tokenizerContext),
        );
    }

    private function updateBlockToken(TokenizerContext $tokenizerContext, BlockToken $token): BlockToken
    {
        /** @var BlockToken $token */
        $token->setEnd(BlockToken::calculateEndPosition($tokenizerContext, $token));
        return $token;
    }

    public function getHandledTokenClass(): string
    {
        return BlockToken::class;
    }
}
