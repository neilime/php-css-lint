<?php

declare(strict_types=1);

namespace Tests\TestSuite\TokenLinter;

use CssLint\LintConfiguration;
use CssLint\Position;
use CssLint\Token\BlockToken;
use CssLint\Token\WhitespaceToken;
use CssLint\TokenLinter\IndentationTokenLinter;
use InvalidArgumentException;
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
        $token = new WhitespaceToken('    ', new Position(1, 0), new Position(1, 4));
        $this->assertTrue($this->linter->supports($token), 'Should support WhitespaceToken');

        $nonWhitespaceToken = new BlockToken([], new Position(1, 0), new Position(1, 4));
        $this->assertFalse($this->linter->supports($nonWhitespaceToken), 'Should not support non-WhitespaceToken');
    }

    public function testThrowsExceptionForNonPropertyToken(): void
    {
        $nonPropertyToken = new BlockToken([], new Position(1, 0), new Position(1, 4));
        $this->expectException(InvalidArgumentException::class);
        iterator_to_array($this->linter->lint($nonPropertyToken));
    }

    public function testLintWithValidIndentation(): void
    {
        // Arrange
        $token = new WhitespaceToken('    ', new Position(1, 0), new Position(1, 4));
        $this->configuration->setAllowedIndentationChars([' ']);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function testLintWithInvalidTabIndentation(): void
    {
        // Arrange
        $token = new WhitespaceToken("\n\t", new Position(1, 0), new Position(1, 1));
        $this->configuration->setAllowedIndentationChars([' ']);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([
            [
                'key' => 'invalid_indentation_character',
                'message' => 'whitespace - Unexpected char "\t"',
                'start' => [
                    'line' => 2,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 2,
                    'column' => 2,
                ],
            ],
        ], $errors);
    }

    public function testLintWithInvalidSpaceIndentation(): void
    {
        // Arrange
        $token = new WhitespaceToken("\n ", new Position(1, 0), new Position(1, 1));
        $this->configuration->setAllowedIndentationChars(['\t']);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([
            [
                'key' => 'invalid_indentation_character',
                'message' => 'whitespace - Unexpected char " "',
                'start' => [
                    'line' => 2,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 2,
                    'column' => 2,
                ],
            ],
        ], $errors);
    }

    public function testLintWithMultipleAllowedCharacters(): void
    {
        // Arrange
        $token = new WhitespaceToken("  \t  ", new Position(1, 0), new Position(1, 5));
        $this->configuration->setAllowedIndentationChars([' ', "\t"]);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function testLintWithMultilineIndentation(): void
    {
        // Arrange
        $token = new WhitespaceToken("\n    \n\t \t", new Position(1, 0), new Position(1, 7));
        $this->configuration->setAllowedIndentationChars([' ']);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([
            [
                'key' => 'invalid_indentation_character',
                'message' => 'whitespace - Unexpected char "\t"',
                'start' => [
                    'line' => 3,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 3,
                    'column' => 4,
                ],
            ],
        ], $errors);
    }

    public function testLintWithEmptyToken(): void
    {
        // Arrange
        $token = new WhitespaceToken('', new Position(1, 0), new Position(1, 0));
        $this->configuration->setAllowedIndentationChars([' ']);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }
}
