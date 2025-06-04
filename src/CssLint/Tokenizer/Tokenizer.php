<?php

declare(strict_types=1);

namespace CssLint\Tokenizer;

use CssLint\Token\Token;
use Generator;
use CssLint\LintError;
use CssLint\LintErrorKey;
use CssLint\Token\BlockToken;
use CssLint\Token\WhitespaceToken;
use CssLint\Tokenizer\Parser\AtRuleParser;
use CssLint\Tokenizer\Parser\BlockParser;
use CssLint\Tokenizer\Parser\CommentParser;
use CssLint\Tokenizer\Parser\Parser;
use CssLint\Tokenizer\Parser\PropertyParser;
use CssLint\Tokenizer\Parser\SelectorParser;
use CssLint\Tokenizer\Parser\WhitespaceParser;
use CssLint\TokenLinter\TokenError;
use CssLint\Token\TokenBoundary;
use CssLint\Tokenizer\Parser\EndOfLineParser;
use LogicException;

class Tokenizer
{
    private TokenizerContext $tokenizerContext;

    /**
     * The list of parsers to handle different CSS chars.
     * @var Parser[]
     */
    private array $parsers;

    /**
     * Tokenizes a CSS stream resource into an array of tokens.
     *
     * @param resource $stream The CSS stream resource to tokenize.
     * @return Generator<Token|LintError> An array of tokens or issues found during tokenizing.
     */
    public function tokenize($stream): Generator
    {
        $this->resetTokenizerContext();

        yield from $this->processStreamContent($stream);

        yield from $this->assertTokenizerContextIsClean();

        return;
    }

    private function resetTokenizerContext(): self
    {
        $this->tokenizerContext = new TokenizerContext();
        return $this;
    }

    /**
     * Process the content of the stream chunk by chunk.
     *
     * @param resource $stream
     * @return Generator<Token|LintError>
     */
    private function processStreamContent($stream): Generator
    {
        $buffer = '';
        $position = 0;

        while (!feof($stream)) {
            $buffer .= fread($stream, 1024);

            yield from $this->processBuffer($buffer, $position);

            // Remove processed part of the buffer
            $buffer = substr($buffer, $position);
            $position = 0;
        }

        return;
    }

    /**
     * Process a buffer of characters and generate tokens.
     *
     * @param string $buffer The buffer to process
     * @param int &$position Current position in the buffer, passed by reference
     * @return Generator<Token|LintError>
     */
    private function processBuffer(string $buffer, int &$position): Generator
    {
        $length = strlen($buffer);

        while ($position < $length) {
            $currentChar = $buffer[$position];

            yield from $this->processCharacter($currentChar);

            $this->tokenizerContext->incrementColumn();
            $position++;
        }

        return;
    }

    /**
     * Process a single character and generate tokens if applicable.
     *
     * @param string $char The character to process
     * @return Generator<Token|LintError>
     */
    private function processCharacter(string $char): Generator
    {
        $this->tokenizerContext->appendCurrentContent($char);
        foreach ($this->getParsers() as $parser) {
            $currentBlockToken = $this->tokenizerContext->getCurrentBlockToken();
            $generatedToken = null;
            foreach ($this->parseCharacter($parser) as $result) {
                if ($result instanceof LintError) {
                    yield $result;
                    continue;
                }

                if (!$result->isComplete()) {
                    throw new LogicException(sprintf('Token "%s" is not complete', $result::class));
                }

                $generatedToken = $result;
                // Do not yield token having a parent as it is already in parent token
                if ($result->getParent() === null) {
                    yield $result;
                }
                break;
            }

            $enterInNewBlock = $this->tokenizerContext->getCurrentBlockToken() !== $currentBlockToken && BlockParser::isBlockStart($this->tokenizerContext);

            // Handle token transitions using TokenBoundary interface
            $shouldParseWithAnotherParser = $generatedToken instanceof TokenBoundary && $this->findNextCompatibleParser($generatedToken) !== null;

            // Token has been generated, reset current content
            if ($generatedToken || $enterInNewBlock) {
                $this->tokenizerContext->resetCurrentContent();
            }

            if (!$generatedToken) {
                continue;
            }

            // Token has been generated, and it is the current token
            $generatedTokenIsCurrentToken = $generatedToken === $this->tokenizerContext->getCurrentToken();
            if ($generatedTokenIsCurrentToken) {
                $this->tokenizerContext->resetCurrentToken();
            }


            if ($shouldParseWithAnotherParser) {
                $this->tokenizerContext->appendCurrentContent($char);
            } else {
                break;
            }
        }

        return;
    }

    /**
     * Find the next parser that can handle tokens that the current token can transition to
     */
    private function findNextCompatibleParser(TokenBoundary $token): ?Parser
    {
        foreach ($this->getParsers() as $parser) {
            $handledTokenClass = $parser->getHandledTokenClass();
            if (
                $handledTokenClass
                && $token->canTransitionTo($handledTokenClass, $this->tokenizerContext)
            ) {
                return $parser;
            }
        }
        return null;
    }

    /**
     * Parses the current context of the tokenizer and yields tokens or lint errors.
     * @return Generator<Token|LintError>
     */
    private function parseCharacter(Parser $parser): Generator
    {
        $result = $parser->parseCurrentContext($this->tokenizerContext);
        if (!$result) {
            return;
        }

        if ($result instanceof BlockToken) {
            yield from $this->assertBlockTokenIsClean($result);
        }

        yield $result;

        return;
    }

    /**
     * Returns the list of parsers used to handle different CSS chars.
     *
     * @return Parser[] The list of parsers.
     */
    private function getParsers(): array
    {
        if (empty($this->parsers)) {
            $this->parsers = [
                new EndOfLineParser(),
                new CommentParser(),
                new AtRuleParser(),
                new SelectorParser(),
                new PropertyParser(),
                new BlockParser(),
                new WhitespaceParser(),
            ];
        }

        return $this->parsers;
    }

    /**
     * Assert that the tokenizer context is clean, meaning that the current token is closed.
     * Yields the current token if it is a block token for linting purposes.
     * @return Generator<LintError|BlockToken>
     */
    private function assertTokenizerContextIsClean(): Generator
    {
        $currentToken = $this->tokenizerContext->getCurrentToken();

        if ($currentToken !== null && !$currentToken->isComplete()) {
            $currentToken->setEnd(
                ($currentToken::class)::calculateEndPosition(
                    $this->tokenizerContext,
                    $currentToken
                )
            );

            if (!$currentToken instanceof WhitespaceToken) {
                $value = $currentToken->getValue();
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                yield new TokenError(
                    LintErrorKey::UNCLOSED_TOKEN,
                    sprintf('Unclosed "%s" detected', $currentToken->getType()),
                    $currentToken
                );
            }
        }

        $currentBlockToken = $this->tokenizerContext->getCurrentBlockToken();
        if ($currentBlockToken !== null) {
            if (!$currentBlockToken->isComplete()) {
                yield $currentBlockToken;
            }
            yield from $this->assertBlockTokenIsClean($currentBlockToken);
        }

        $currentContent = trim($this->tokenizerContext->getCurrentContent());
        if ($currentContent !== '') {
            yield LintError::fromTokenizerContext(
                LintErrorKey::UNEXPECTED_CHARACTER_END_OF_CONTENT,
                sprintf('Unexpected character at end of content: "%s"', $currentContent),
                $this->tokenizerContext
            );
        }

        return null;
    }

    /**
     * Assert that the block token is clean, meaning that it has a property token.
     * @return Generator<LintError>
     */
    private function assertBlockTokenIsClean(BlockToken $blockToken): Generator
    {
        $end = $blockToken->getEnd() ?? $this->tokenizerContext->getCurrentPosition();
        $blockTokenTokens = $blockToken->getValue();
        foreach ($blockTokenTokens as $token) {
            $isLastWhitespaceToken = $token instanceof WhitespaceToken && $token === $blockTokenTokens[count($blockTokenTokens) - 1];
            if (!$token->isComplete() && !$isLastWhitespaceToken) {
                $token->setEnd($end);
                yield new TokenError(
                    LintErrorKey::UNCLOSED_TOKEN,
                    sprintf('Unclosed "%s" detected', $token->getType()),
                    $token,
                );
            }

            if ($token instanceof BlockToken) {
                yield from $this->assertBlockTokenIsClean($token);
            }
        }

        if (!$blockToken->isComplete()) {
            $blockToken->setEnd($end);
            yield new TokenError(
                LintErrorKey::UNCLOSED_TOKEN,
                sprintf('Unclosed "%s" detected', $blockToken->getType()),
                $blockToken,
            );
            return;
        }

        $blockContent = BlockParser::getBlockContent($this->tokenizerContext);

        if ($blockContent !== '' && !BlockParser::isBlockEnd($this->tokenizerContext, true)) {
            yield new TokenError(
                LintErrorKey::UNEXPECTED_CHARACTER_IN_BLOCK_CONTENT,
                sprintf('Unexpected character: "%s"', $blockContent),
                $blockToken,
            );
        }

        return;
    }
}
