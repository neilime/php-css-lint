<?php

declare(strict_types=1);

namespace CssLint\Tokenizer\Parser;

use CssLint\Token\BlockToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;

/**
 * @template TToken of Token
 * @implements Parser<TToken>
 */
abstract class AbstractParser implements Parser
{

    protected function isSpace(TokenizerContext $tokenizerContext): bool
    {
        $lastChar = $tokenizerContext->getLastChar();
        return $lastChar !== null && ctype_space($lastChar);
    }

    /**
     * @param callable(?BlockToken, ?TToken): ?TToken $generateToken 
     */
    protected function provideToken(TokenizerContext $tokenizerContext, callable $generateToken)
    {
        if (!$tokenizerContext->assertCurrentToken(BlockToken::class)) {
            $token = call_user_func($generateToken);
            if ($token) {
                $token->setEnd($this->getTokenEnd($token, $tokenizerContext));
                $tokenizerContext->setCurrentToken($token);
            }
            return $token;
        }

        /** @var BlockToken $blockToken */
        $blockToken = $tokenizerContext->getCurrentToken();
        $currentToken = $blockToken->getBlockCurrentToken();

        if ($currentToken === null) {
            $token = call_user_func($generateToken, $blockToken);
            if ($token) {
                $blockToken->addToken($token);
            }
            return null;
        }

        if (get_class($currentToken) !== $this->getHandledTokenClass()) {
            return null;
        }

        $token = call_user_func($generateToken, $blockToken, $currentToken);
        if ($token instanceof Token) {
            $token->setEnd($this->getTokenEnd($token, $tokenizerContext));
        }
        return $token;
    }

    protected function getTokenEnd(Token $token, TokenizerContext $tokenizerContext): int
    {
        $end = $tokenizerContext->getCharNumber();
        $start = $token->getStart();
        if ($end <= $start) {
            return $start + 1;
        }
        return $end;
    }
}
