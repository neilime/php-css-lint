<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer\Parser;

use CssLint\Position;
use CssLint\Token\CommentToken;
use CssLint\Tokenizer\Parser\CommentParser;
use CssLint\Tokenizer\TokenizerContext;
use Tests\TestSuite\TestCase;

class CommentParserTest extends TestCase
{
    private CommentParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CommentParser();
    }

    public function testGetHandledTokenClass(): void
    {
        $this->assertEquals(CommentToken::class, $this->parser->getHandledTokenClass());
    }

    public function validCommentProvider(): array
    {
        return [
            'simple comment' => ['/* comment */', 'comment'],
            'multi-line comment' => ["/*\n * multi-line\n * comment\n */", "multi-line\ncomment"],
            'empty comment' => ['/**/', ''],
            'comment with special chars' => ['/* @import "style.css"; */', '@import "style.css";'],
        ];
    }

    /**
     * @dataProvider validCommentProvider
     */
    public function testParsesValidComment(string $content, string $expectedValue): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokens = [];

        // Act
        foreach (str_split($content) as $char) {
            $tokenizerContext->appendCurrentContent($char);
            $token = $this->parser->parseCurrentContext($tokenizerContext);
            if ($token) {
                $tokens[] = $token;
            }
        }

        // Assert
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $this->assertInstanceOf(CommentToken::class, $token);
        $this->assertEquals($expectedValue, $token->getValue());
    }

    public function testIgnoresNonCommentContent(): void
    {
        // Arrange
        $content = 'not-a-comment';
        $tokenizerContext = new TokenizerContext();
        $tokens = [];

        // Act
        foreach (str_split($content) as $char) {
            $tokenizerContext->appendCurrentContent($char);
            $token = $this->parser->parseCurrentContext($tokenizerContext);
            if ($token) {
                $tokens[] = $token;
            }
        }

        // Assert
        $this->assertEmpty($tokens);
    }

    public function testHandlesCommentWithSpaces(): void
    {
        // Arrange
        $content = ' /* comment */ ';
        $tokenizerContext = new TokenizerContext();
        $tokens = [];

        // Act
        foreach (str_split($content) as $char) {
            $tokenizerContext->appendCurrentContent($char);
            $token = $this->parser->parseCurrentContext($tokenizerContext);
            if ($token) {
                $tokens[] = $token;
            }
        }

        // Assert
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $this->assertInstanceOf(CommentToken::class, $token);
        $this->assertEquals('comment', $token->getValue());
    }

    public function testHandlesUnclosedComment(): void
    {
        // Arrange
        $content = '/* unclosed comment';
        $tokenizerContext = new TokenizerContext();
        $tokens = [];

        // Act
        foreach (str_split($content) as $char) {
            $tokenizerContext->appendCurrentContent($char);
            $token = $this->parser->parseCurrentContext($tokenizerContext);
            if ($token) {
                $tokens[] = $token;
            }
        }

        // Assert
        $this->assertEmpty($tokens);
    }
}
