<?php

declare(strict_types=1);

namespace Tests\TestSuite\TokenLinter;

use CssLint\LintConfiguration;
use CssLint\Token\BlockToken;
use CssLint\Token\WhitespaceToken;
use CssLint\TokenLinter\IndentationTokenLinter;
use Tests\TestSuite\TestCase;

class IndentationTokenLinterTest extends TestCase
{
    private IndentationTokenLinter $linter;
    private LintConfiguration $configuration;

    protected function setUp(): void
    {
        $this->configuration = new LintConfiguration();
        $this->linter = new IndentationTokenLinter($this->configuration);
    }

    public function testSupportsOnlyWhitespaceTokens(): void
    {
        $token = new WhitespaceToken('    ', 1, 0, 4);
        $this->assertTrue($this->linter->supports($token), 'Should support WhitespaceToken');

        $nonWhitespaceToken = new BlockToken([], 1, 0, 4);
        $this->assertFalse($this->linter->supports($nonWhitespaceToken), 'Should not support non-WhitespaceToken');
    }

    public function testLintWithValidIndentation(): void
    {
        // Arrange
        $token = new WhitespaceToken('    ', 1, 0, 4);
        $this->configuration->setAllowedIndentationChars([' ']);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function testLintWithInvalidIndentation(): void
    {
        // Arrange
        $token = new WhitespaceToken("\n\t", 1, 0, 1);
        $this->configuration->setAllowedIndentationChars([' ']);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([
            [
                'key' => 'invalid_indentation_character',
                'message' => 'whitespace - Unexpected char "\t"',
                'line' => 2,
                'start' => 1,
                'end' => 2,
            ],
        ], $errors);
    }

    public function testLintWithMultipleAllowedCharacters(): void
    {
        // Arrange
        $token = new WhitespaceToken("  \t  ", 1, 0, 5);
        $this->configuration->setAllowedIndentationChars([' ', "\t"]);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function testLintWithMultilineIndentation(): void
    {
        // Arrange
        $token = new WhitespaceToken("\n    \n\t", 1, 0, 7);
        $this->configuration->setAllowedIndentationChars([' ']);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([
            [
                'key' => 'invalid_indentation_character',
                'message' => 'whitespace - Unexpected char "\t"',
                'line' => 3,
                'start' => 6,
                'end' => 7,
            ],
        ], $errors);
    }

    public function testLintWithEmptyToken(): void
    {
        // Arrange
        $token = new WhitespaceToken('', 1, 0, 0);
        $this->configuration->setAllowedIndentationChars([' ']);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }
}
