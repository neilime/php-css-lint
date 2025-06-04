<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\AtRuleToken;
use CssLint\Token\BlockToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @extends AbstractParser<AtRuleToken>
 */
class AtRuleParser extends AbstractParser
{
    private static string $AT_RULE_START = '@';
    private static string $AT_RULE_NAME_PATTERN = '/^[a-zA-Z0-9-]+$/';
    private static string $AT_RULE_BLOCK_START = '{';
    private static string $AT_RULE_END = ';';

    /**
     * Performs parsing tokenizer current context for at-rules
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->isSpace($tokenizerContext)) {
            return null;
        }

        $currentTokenIsAtRule = $tokenizerContext->assertCurrentToken($this->getHandledTokenClass());

        if (!$currentTokenIsAtRule && !$tokenizerContext->assertCurrentToken(null)) {
            // If we are in a different token context, we do not handle at-rules
            return null;
        }

        return $this->provideToken(
            $tokenizerContext,
            function (?BlockToken $blockToken = null, ?AtRuleToken $currentAtRuleToken = null) use ($tokenizerContext) {
                if (!$currentAtRuleToken) {
                    if ($this->isAtRuleStart($tokenizerContext)) {
                        return $this->createAtRuleToken($tokenizerContext);
                    }
                    return null;
                }

                if ($this->isAtRuleEnd($tokenizerContext) || $this->isAtRuleBlockStart($tokenizerContext)) {
                    return $this->updateAtRuleToken($tokenizerContext, $currentAtRuleToken);
                }

                return null;
            }
        );
    }

    private function isAtRuleStart(TokenizerContext $tokenizerContext): bool
    {
        return $tokenizerContext->getNthLastChars(strlen(self::$AT_RULE_START)) === self::$AT_RULE_START;
    }

    private function isAtRuleEnd(TokenizerContext $tokenizerContext): bool
    {
        return $tokenizerContext->getNthLastChars(strlen(self::$AT_RULE_END)) === self::$AT_RULE_END;
    }

    private function isAtRuleBlockStart(TokenizerContext $tokenizerContext): bool
    {
        return $tokenizerContext->getNthLastChars(strlen(self::$AT_RULE_BLOCK_START)) === self::$AT_RULE_BLOCK_START;
    }

    private function createAtRuleToken(TokenizerContext $tokenizerContext): AtRuleToken
    {
        $content = trim($tokenizerContext->getCurrentContent());
        $name = ltrim($content, self::$AT_RULE_START);

        return new AtRuleToken(
            $name,
            null,
            $tokenizerContext->getLineNumber(),
            $tokenizerContext->getCharNumber() - strlen($content)
        );
    }

    private function updateAtRuleToken(TokenizerContext $tokenizerContext, AtRuleToken $token): AtRuleToken
    {
        $content = $tokenizerContext->getCurrentContent();
        $parts = explode(' ', trim($content), 2);

        $name = ltrim($parts[0], self::$AT_RULE_START);
        $value = isset($parts[1]) ? rtrim($parts[1], self::$AT_RULE_END . self::$AT_RULE_BLOCK_START) : null;

        $token->setValue(['name' => $name, 'value' => $value]);
        $token->setEnd($this->getTokenEnd($token, $tokenizerContext));

        return $token;
    }

    public function getHandledTokenClass(): string
    {
        return AtRuleToken::class;
    }
}
