<?php

declare(strict_types=1);

namespace CssLint\TokenLinter;

use CssLint\LintConfiguration;
use CssLint\LintErrorKey;
use CssLint\Position;
use CssLint\Token\WhitespaceToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\Parser\WhitespaceParser;
use CssLint\TokenLinter\TokenError;
use Generator;
use InvalidArgumentException;

class IndentationTokenLinter implements TokenLinter
{
    public function __construct(
        private readonly LintConfiguration $lintConfiguration,
    ) {}

    /**
     * Lints a token and returns a list of issues found.
     *
     * @param Token $token The token to lint.
     * @return Generator<TokenError> A list of issues found during linting.
     */
    public function lint(Token $token): Generator
    {
        if (!$token instanceof WhitespaceToken) {
            throw new InvalidArgumentException(
                'IndentationTokenLinter can only lint WhitespaceToken'
            );
        }

        // Check indentation character is allowed
        yield from $this->checkIndentationCharacter($token);

        return;
    }

    /**
     * Checks if the linter supports the given token.
     *
     * @param Token $token The token to check.
     * @return bool True if the linter supports the token, false otherwise.
     */
    public function supports(Token $token): bool
    {
        return $token instanceof WhitespaceToken;
    }

    /**
     * Check if the indentation character is allowed
     * @param WhitespaceToken $token The token to check
     * @return Generator<TokenError> A list of issues found during linting.
     */
    private function checkIndentationCharacter(WhitespaceToken $token): Generator
    {
        $value = $token->getValue();
        $defaultEndOfLineChar = WhitespaceParser::$END_OF_LINE_CHARS[count(WhitespaceParser::$END_OF_LINE_CHARS) - 1];
        $lines = str_replace(WhitespaceParser::$END_OF_LINE_CHARS, $defaultEndOfLineChar, $value);
        $lines = explode($defaultEndOfLineChar, $lines);
        $lineNumber = $token->getStart()->getLine();

        // Get allowed indentation characters from configuration
        $allowedChars = $this->lintConfiguration->getAllowedIndentationChars();

        foreach ($lines as $line) {
            $column = 1;
            $currentPosition = new Position($lineNumber, $column);

            $currentCharError = null;
            /** @var Position|null $currentCharErrorStart */
            $currentCharErrorStart = null;

            // Check each character of indentation
            for ($i = 0; $i < strlen($line); $i++) {
                $char = $line[$i];
                if (!in_array($char, $allowedChars, true)) {
                    if ($currentCharError === null) {
                        $currentCharError = $char;
                        $currentCharErrorStart = new Position($lineNumber, $column);
                    } elseif ($currentCharErrorStart && $currentCharError !== $char) {
                        yield from $this->generateError(
                            $token,
                            $currentCharErrorStart,
                            $currentPosition,
                            $currentCharError
                        );

                        $currentCharError = $char;
                        $currentCharErrorStart = $currentPosition;
                    }
                }

                $column++;
                $currentPosition = new Position($lineNumber, $column);
            }

            if ($currentCharErrorStart && $currentCharError !== null) {
                yield from $this->generateError(
                    $token,
                    $currentCharErrorStart,
                    $currentPosition,
                    $currentCharError
                );
                $currentCharError = null;
                $currentCharErrorStart = null;
            }
            $lineNumber++;
        }

        return;
    }

    /**
     * @return Generator<TokenError>
     */
    private function generateError(WhitespaceToken $token, Position $start, Position $end, string $char): Generator
    {
        yield new TokenError(
            LintErrorKey::INVALID_INDENTATION_CHARACTER,
            sprintf('Unexpected char "%s"', str_replace(["\t"], ['\t'], $char)),
            $token,
            $start,
            $end
        );

        return;
    }
}
