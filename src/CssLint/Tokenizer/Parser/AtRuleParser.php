<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\LintError;
use CssLint\Token\AtRuleToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @extends AbstractParser<AtRuleToken>
 */
class AtRuleParser extends AbstractParser
{
    /**
     * @var non-empty-string
     */
    private static string $AT_RULE_START = '@';

    /**
     * @var non-empty-string
     */
    private static string $AT_RULE_END = ';';

    private static string $AT_RULE_PATTERN = '/^@[a-zA-Z0-9-]+/';

    /**
     * Get the token class that this parser handles
     * @return class-string<AtRuleToken>
     */
    public function getHandledTokenClass(): string
    {
        return AtRuleToken::class;
    }

    /**
     * Performs parsing tokenizer current context for at-rules
     */
    public function parseCurrentContext(TokenizerContext $tokenizerContext): Token|LintError|null
    {
        if ($this->lastCharIsSpace($tokenizerContext)) {
            return null;
        }

        return $this->handleTokenForCurrentContext(
            $tokenizerContext,
            function (?AtRuleToken $currentAtRuleToken = null) use ($tokenizerContext) {
                if (!$currentAtRuleToken) {
                    if ($this->isAtRule($tokenizerContext)) {
                        return $this->createAtRuleToken($tokenizerContext);
                    }
                    return null;
                }

                $currentAtRuleToken = $this->updateAtRuleToken($tokenizerContext, $currentAtRuleToken);
                if ($this->isAtRuleEnd($tokenizerContext) || $this->isAtRuleBlockStart($tokenizerContext)) {
                    return $currentAtRuleToken;
                }
                return null;
            }
        );
    }

    private function isAtRule(TokenizerContext $tokenizerContext): bool
    {
        $currentContent = trim($tokenizerContext->getCurrentContent());
        return preg_match(self::$AT_RULE_PATTERN, $currentContent) === 1;
    }

    private function isAtRuleEnd(TokenizerContext $tokenizerContext): bool
    {
        return $tokenizerContext->currentContentEndsWith(self::$AT_RULE_END);
    }

    private function isAtRuleBlockStart(TokenizerContext $tokenizerContext): bool
    {
        return BlockParser::isBlockStart($tokenizerContext);
    }

    private function createAtRuleToken(TokenizerContext $tokenizerContext): AtRuleToken
    {
        return new ($this->getHandledTokenClass())(
            $this->getAtRuleName($tokenizerContext),
            null,
            AtRuleToken::calculateStartPosition($tokenizerContext),
        );
    }

    private function updateAtRuleToken(TokenizerContext $tokenizerContext, AtRuleToken $token): AtRuleToken
    {
        $name = $this->getAtRuleName($tokenizerContext);
        $value = $this->getAtRuleValue($tokenizerContext);
        $isBlock = $this->isAtRuleBlockStart($tokenizerContext);

        $token
            ->setName($name)
            ->setValue($value)
            ->setIsBlock($isBlock);

        return $token;
    }

    private function getAtRuleName(TokenizerContext $tokenizerContext): string
    {
        $content = trim($tokenizerContext->getCurrentContent());
        $parts = explode(' ', trim($content), 2);
        $name = trim(
            $this->removeStartingString(
                $parts[0],
                self::$AT_RULE_START
            )
        );

        return $this->removeAtRuleEndingString($name);
    }

    private function getAtRuleValue(TokenizerContext $tokenizerContext): ?string
    {
        $content = trim($tokenizerContext->getCurrentContent());
        $parts = explode(' ', trim($content), 2);

        if (!isset($parts[1])) {
            return null;
        }

        return $this->removeAtRuleEndingString($parts[1]);
    }

    private function removeAtRuleEndingString(string $content): string
    {
        foreach ([self::$AT_RULE_END, BlockParser::$BLOCK_START] as $endChar) {
            $content = self::removeEndingString($content, $endChar);
        }

        return trim($content);
    }
}
