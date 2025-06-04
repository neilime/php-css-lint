<?php

declare(strict_types=1);

namespace CssLint;

use CssLint\Tokenizer\Tokenizer;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;
use Generator;
use InvalidArgumentException;
use RuntimeException;

/**
 * @package CssLint
 * @phpstan-import-type Errors from TokenizerContext
 */
class Linter
{
    /**
     * Class to provide css properties knowledge
     * @var LintConfiguration|null
     */
    protected $lintConfiguration;

    /**
     * Class to provide css tokenizer
     * @var Tokenizer|null
     */
    protected $tokenizer;

    /**
     * Constructor
     * @param LintConfiguration $lintConfiguration An instance of the LintConfiguration class to provide css properties knowledge
     */
    public function __construct(?LintConfiguration $lintConfiguration = null, ?Tokenizer $tokenizer = null)
    {
        if ($lintConfiguration instanceof LintConfiguration) {
            $this->setLintConfiguration($lintConfiguration);
        }

        if ($tokenizer instanceof Tokenizer) {
            $this->tokenizer = $tokenizer;
        }
    }

    /**
     * Return an instance of the "\CssLint\Properties" helper, initialize a new one if not define already
     */
    public function getLintConfiguration(): LintConfiguration
    {
        if ($this->lintConfiguration) {
            return $this->lintConfiguration;
        }

        return $this->lintConfiguration = new LintConfiguration();
    }

    /**
     * Set an instance of the "\CssLint\Properties" helper
     */
    public function setLintConfiguration(LintConfiguration $lintConfiguration): self
    {
        $this->lintConfiguration = $lintConfiguration;
        return $this;
    }

    /**
     * Returns an instance of a tokenizer.
     * You may need to adjust the class name and constructor as per your project structure.
     */
    public function getTokenizer(): Tokenizer
    {
        if ($this->tokenizer) {
            return $this->tokenizer;
        }

        return $this->tokenizer = new Tokenizer();
    }

    /**
     * Sets an instance of a tokenizer.
     * @param Tokenizer $tokenizer
     * @return Linter
     */
    public function setTokenizer(Tokenizer $tokenizer): self
    {
        $this->tokenizer = $tokenizer;
        return $this;
    }

    /**
     * Performs lint on a given string
     * @return Generator<LintError> An array of issues found during linting.
     */
    public function lintString(string $stringValue): Generator
    {
        $stream = fopen('php://memory', 'r+');
        if ($stream === false) {
            throw new RuntimeException('An error occurred while opening a memory stream');
        }

        if (fwrite($stream, $stringValue) === false) {
            throw new RuntimeException('An error occurred while writing to a memory stream');
        }
        rewind($stream);

        yield from $this->lintStream($stream);

        return;
    }

    /**
     * Performs lint for a given file path
     * @param string $filePath A path of an existing and readable file
     * @return Generator<LintError> An array of issues found during linting.
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function lintFile(string $filePath): Generator
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf(
                'Argument "$filePath" "%s" is not an existing file path',
                $filePath
            ));
        }

        if (!is_readable($filePath)) {
            throw new InvalidArgumentException(sprintf(
                'Argument "$filePath" "%s" is not a readable file path',
                $filePath
            ));
        }

        $fileHandle = fopen($filePath, 'r');
        if ($fileHandle === false) {
            throw new RuntimeException('An error occurred while opening file "' . $filePath . '"');
        }

        yield from $this->lintStream($fileHandle);

        if (!feof($fileHandle)) {
            throw new RuntimeException('An error occurred while reading file "' . $filePath . '"');
        }

        fclose($fileHandle);

        return;
    }

    /**
     * Lint a stream of tokens
     * @param resource $stream A valid stream resource
     * @return Generator<LintError> An array of issues found during linting.
     * @throws InvalidArgumentException
     */
    protected function lintStream($stream): Generator
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Argument "$stream" must be a valid resource');
        }

        yield from $this->lintTokens(
            $this->getTokenizer()->tokenize($stream)
        );

        return;
    }

    /**
     * Lint a list of tokens or errors
     * @param iterable<Token|LintError> $tokens
     * @return Generator<LintError> An array of issues found during linting.
     */
    private function lintTokens(iterable $tokens): Generator
    {
        foreach ($tokens as $tokenOrError) {
            if ($tokenOrError instanceof LintError) {
                yield $tokenOrError;
                continue;
            }

            yield from $this->lintToken($tokenOrError);
        }

        return;
    }

    /**
     * Lint a token
     * @param Token $token
     * @return Generator<LintError> An array of issues found during linting.
     */
    private function lintToken(Token $token): Generator
    {
        $linters = $this->getLintConfiguration()->getLinters();
        foreach ($linters as $tokenLinter) {
            if (!$tokenLinter->supports($token)) {
                continue;
            }

            yield from $tokenLinter->lint($token);
        }

        $tokenValue = $token->getValue();
        if (is_iterable($tokenValue)) {
            yield from $this->lintTokens($tokenValue);
        }
    }
}
