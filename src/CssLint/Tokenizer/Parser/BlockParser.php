<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\BlockToken;
use CssLint\Tokenizer\TokenizerContext;
use CssLint\Tokenizer\TokenizerContextInspector;
use CssLint\Tokenizer\TokenizerStringInspector;

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
    public static string $BLOCK_END = '}';

    /**
     * Check if the current char is the start of a block
     */
    public static function isBlockStart(TokenizerContext $tokenizerContext): bool
    {
        $tokenizerContextInspector = new TokenizerContextInspector($tokenizerContext);
        $currentContent = $tokenizerContext->getCurrentContent();

        // Ensure we have a valid block start
        if (!$tokenizerContextInspector->currentContentEndsWith(self::$BLOCK_START)) {
            return false;
        }

        // Preserve existing quote behavior while rejecting unfinished function-like contexts.
        $contentBeforeBlock = substr($currentContent, 0, -1);
        $openQuotes = substr_count($contentBeforeBlock, '"') + substr_count($contentBeforeBlock, "'");
        if ($openQuotes % 2 !== 0) {
            return false;
        }

        if (TokenizerStringInspector::hasOpenParenthesisContext($contentBeforeBlock)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the current char is the end of a block
     */
    public static function isBlockEnd(TokenizerContext $tokenizerContext, bool $fullContent = false): bool
    {
        $tokenizerContextInspector = new TokenizerContextInspector($tokenizerContext);
        $value = $fullContent ? trim($tokenizerContext->getCurrentContent()) : $tokenizerContextInspector->nthLastChars(strlen(self::$BLOCK_END));

        return $value === self::$BLOCK_END;
    }

    public static function getBlockContent(TokenizerContext $tokenizerContext): string
    {
        $content = trim($tokenizerContext->getCurrentContent());
        $content = self::removeStartingString($content, self::$BLOCK_START);
        $content = self::removeEndingString($content, self::$BLOCK_END);
        return trim($content);
    }

    /**
     * Performs parsing tokenizer current context, check block part
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): ?BlockToken
    {
        $tokenizerContextInspector = new TokenizerContextInspector($tokenizerContext);

        if ($tokenizerContextInspector->lastCharIsSpace()) {
            return null;
        }

        $currentBlockToken = $tokenizerContext->getCurrentBlockToken();
        return $this->handleBlockToken($tokenizerContext, $currentBlockToken);
    }

    private function handleBlockToken(TokenizerContext $tokenizerContext, ?BlockToken $currentBlockToken): ?BlockToken
    {
        if (static::isBlockStart($tokenizerContext)) {
            $blockToken = $this->createBlockToken($tokenizerContext);
            if ($currentBlockToken === null) {
                $tokenizerContext->setCurrentBlockToken($blockToken);
            } else {
                $currentBlockToken->addToken($blockToken);
                $tokenizerContext->setCurrentBlockToken($blockToken);
            }
            return null;
        }

        if (!static::isBlockEnd($tokenizerContext)) {
            return null;
        }

        if ($currentBlockToken === null) {
            return null;
        }

        $currentBlockToken = $this->updateBlockToken($tokenizerContext, $currentBlockToken);
        $tokenizerContext->setCurrentBlockToken($currentBlockToken->getParent());
        return $currentBlockToken;
    }

    /**
     * Creates a BlockToken from the current context
     */
    private function createBlockToken(TokenizerContext $tokenizerContext): BlockToken
    {
        $blockToken = new ($this->getHandledTokenClass())(
            [],
            BlockToken::calculateStartPosition($tokenizerContext),
        );

        $blockToken->setPreviousToken($tokenizerContext->getPreviousToken());

        return $blockToken;
    }

    private function updateBlockToken(TokenizerContext $tokenizerContext, BlockToken $blockToken): BlockToken
    {
        $blockToken->setEnd(BlockToken::calculateEndPosition($tokenizerContext, $blockToken));
        return $blockToken;
    }

    public function getHandledTokenClass(): string
    {
        return BlockToken::class;
    }
}
