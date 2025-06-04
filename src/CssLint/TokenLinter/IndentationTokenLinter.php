<?php

declare(strict_types=1);

namespace CssLint\TokenLinter;

use CssLint\LintConfiguration;
use CssLint\LintErrorKey;
use CssLint\Token\WhitespaceToken;
use CssLint\Token\Token;
use CssLint\Tokenizer\Parser\EndOfLineParser;
use CssLint\TokenLinter\TokenError;
use Generator;

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
        if (!$this->supports($token)) {
            return;
        }

        // Check indentation character is allowed
        yield from $this->checkIndentationCharacter($token);
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
        $defaultEndOfLineChar = EndOfLineParser::$END_OF_LINE_CHARS[count(EndOfLineParser::$END_OF_LINE_CHARS) - 1];
        $lines = str_replace(EndOfLineParser::$END_OF_LINE_CHARS, $defaultEndOfLineChar, $value);
        $lines = explode($defaultEndOfLineChar, $lines);
        $lineNumber = $token->getLine();
        $position = $token->getStart();

        // Get allowed indentation characters from configuration
        $allowedChars = $this->lintConfiguration->getAllowedIndentationChars();

        foreach ($lines as $index => $line) {
            // Skip the first line as it's not indentation
            if ($index === 0) {
                $position += strlen($line) + 1; // +1 for the newline
                continue;
            }

            $lineNumber++;
            $indentation = '';

            // Check each character of indentation
            for ($i = 0; $i < strlen($line); $i++) {
                $char = $line[$i];
                if (!in_array($char, $allowedChars, true)) {
                    if (trim($char) === '') {
                        $indentation .= $char;
                    } else {
                        break; // Non-whitespace character found, end of indentation
                    }
                    yield new TokenError(
                        LintErrorKey::INVALID_INDENTATION_CHARACTER,
                        sprintf('Unexpected char "%s"', str_replace(["\t"], ['\t'], $char)),
                        $token,
                        $lineNumber,
                        $position + $i,
                        $position + $i + 1
                    );
                }
            }

            $position += strlen($line) + 1; // +1 for the newline
        }
    }
}
