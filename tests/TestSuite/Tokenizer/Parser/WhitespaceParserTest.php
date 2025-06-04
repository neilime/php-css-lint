<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer\Parser;

use CssLint\Position;
use CssLint\Token\WhitespaceToken;
use CssLint\Tokenizer\Parser\WhitespaceParser;
use CssLint\Tokenizer\TokenizerContext;
use Tests\TestSuite\TestCase;

class WhitespaceParserTest extends TestCase
{
    private WhitespaceParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new WhitespaceParser();
    }

    public function whitespaceProvider(): array
    {
        return [
            'single space' => ["\n f", ' ', new Position(2, 1), new Position(2, 2)],
            'multiple spaces' => ["\n   f", '   ', new Position(2, 1), new Position(2, 2)],
        ];
    }

    /**
     * @dataProvider whitespaceProvider
     */
    public function testParsesWhitespace(string $content, string $expectedValue, Position $expectedStart, Position $expectedEnd): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->incrementLine();
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
        $this->assertNotEmpty($tokens);
        $lastToken = end($tokens);
        $this->assertInstanceOf(WhitespaceToken::class, $lastToken);
        $this->assertEquals($expectedValue, $lastToken->getValue());

        $this->assertEquals($expectedStart, $lastToken->getStart());
        $this->assertEquals($expectedEnd, $lastToken->getEnd());
    }

    public function testIgnoresNonWhitespaceContent(): void
    {
        // Arrange
        $content = 'abc123';
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
