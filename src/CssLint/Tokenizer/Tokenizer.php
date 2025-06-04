<?php

declare(strict_types=1);

namespace CssLint\Tokenizer;

use CssLint\LintConfiguration;
use CssLint\Token\Token;
use Generator;
use CssLint\LintError;
use CssLint\LintErrorKey;
use CssLint\Token\BlockToken;
use CssLint\Token\WhitespaceToken;
use CssLint\Tokenizer\Parser\AtRuleParser;
use CssLint\Tokenizer\Parser\BlockParser;
use CssLint\Tokenizer\Parser\CommentParser;
use CssLint\Tokenizer\Parser\EndOfLineParser;
use CssLint\Tokenizer\Parser\Parser;
use CssLint\Tokenizer\Parser\PropertyParser;
use CssLint\Tokenizer\Parser\SelectorParser;
use CssLint\Tokenizer\Parser\WhitespaceParser;
use CssLint\TokenLinter\TokenError;
use CssLint\Token\TokenBoundary;

class Tokenizer
{
    private TokenizerContext $tokenizerContext;

    /**
     * The list of parsers to handle different CSS chars.
     * @var Parser[]
     */
    private array $parsers;

    public function __construct(private readonly LintConfiguration $lintConfiguration) {}

    /**
     * Tokenizes a CSS stream resource into an array of tokens.
     *
     * @param resource $stream The CSS stream resource to tokenize.
     * @return Generator<Token|LintError> An array of tokens or issues found during tokenizing.
     */
    public function tokenize($stream): Generator
    {
        $this->resetTokenizerContext();
        $this->tokenizerContext->incrementLineNumber();

        foreach ($this->processStreamContent($stream) as $result) {
            if ($result) {
                yield $result;
            }
        }

        $error = $this->assertTokenizerContextIsClean();
        if ($error !== null) {
            yield $error;
        }
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

            foreach ($this->processBuffer($buffer, $position) as $result) {
                if ($result) {
                    yield $result;
                }
            }

            // Remove processed part of the buffer
            $buffer = substr($buffer, $position);
            $position = 0;
        }
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

            $this->tokenizerContext->incrementCharNumber();
            $position++;
        }
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
            $generatedToken = null;
            foreach ($this->parseCharacter($parser) as $result) {
                if ($result instanceof Token) {
                    $generatedToken = $result;
                }

                // Do not yield token inside a block as they are added in the block token
                $shouldYieldResult = $result instanceof LintError || $result instanceof BlockToken || !$this->tokenizerContext->assertCurrentToken(BlockToken::class);
                if ($shouldYieldResult) {
                    yield $result;
                }
            }

            // Token has been generated, reset current context except if we are parsing a block
            $currentTokenIsBlock = $this->tokenizerContext->assertCurrentToken(BlockToken::class);
            $isBlockStart = $currentTokenIsBlock && BlockParser::isBlockStart($this->tokenizerContext);

            $shouldResetContent = $generatedToken || $isBlockStart;
            if ($shouldResetContent) {
                $this->tokenizerContext->resetCurrentContent();
            }

            // Token has been generated, reset current context except if we are parsing a block
            $generatedTokenIsBlock = $generatedToken instanceof BlockToken;
            $shouldResetToken = $generatedToken && (!$currentTokenIsBlock || $generatedTokenIsBlock);
            if ($shouldResetToken) {
                $this->tokenizerContext->resetCurrentToken();
            }

            // Handle token transitions using TokenBoundary interface
            if ($generatedToken instanceof TokenBoundary) {
                $nextParser = $this->findNextCompatibleParser($generatedToken);
                if ($nextParser !== null) {
                    $this->tokenizerContext->appendCurrentContent($char);
                }
            }

            if ($generatedToken && !($generatedToken instanceof TokenBoundary && $this->findNextCompatibleParser($generatedToken) !== null)) {
                break;
            }
        }
    }

    /**
     * Find the next parser that can handle tokens that the current token can transition to
     */
    private function findNextCompatibleParser(TokenBoundary $token): ?Parser
    {
        foreach ($this->getParsers() as $parser) {
            $handledTokenClass = $parser->getHandledTokenClass();
            if ($handledTokenClass && $token->canBeStartOfToken($handledTokenClass)) {
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

        yield $result;

        if ($result instanceof BlockToken) {
            yield from $this->assertBlockTokenIsClean($result);
        }
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
                new CommentParser(),
                new AtRuleParser(),
                new SelectorParser(),
                new BlockParser(),
                new PropertyParser(),
                new EndOfLineParser(),
                new WhitespaceParser(),
            ];
        }

        return $this->parsers;
    }

    private function assertTokenizerContextIsClean(): ?LintError
    {
        $currentToken = $this->tokenizerContext->getCurrentToken();

        if ($currentToken !== null) {
            $currentToken->setEnd($this->tokenizerContext->getCharNumber());

            if (!$currentToken instanceof WhitespaceToken) {
                $value = $currentToken->getValue();
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                return new TokenError(
                    LintErrorKey::UNCLOSED_TOKEN,
                    sprintf('Unclosed %s detected', $currentToken->getType()),
                    $currentToken
                );
            }
        }

        $currentContent = trim($this->tokenizerContext->getCurrentContent());
        if ($currentContent !== '') {
            return LintError::fromTokenizerContext(
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
        $nonCompleteTokens = $blockToken->getNonCompleteTokens();
        foreach ($nonCompleteTokens as $nonCompleteToken) {
            $nonCompleteToken->setEnd($blockToken->getEnd());
            if ($nonCompleteToken instanceof WhitespaceToken) {
                continue;
            }

            yield new TokenError(
                LintErrorKey::UNCLOSED_TOKEN,
                sprintf('Unclosed %s detected: "%s"', $nonCompleteToken->getType(), $nonCompleteToken->getValue()),
                $nonCompleteToken
            );
        }

        if (!BlockParser::isBlockEnd($this->tokenizerContext, true)) {
            yield new TokenError(
                LintErrorKey::UNEXPECTED_CHARACTER_IN_BLOCK_CONTENT,
                sprintf('Unexpected character: "%s"', BlockParser::getBlockContent($this->tokenizerContext)),
                $blockToken
            );
        }
    }
}
