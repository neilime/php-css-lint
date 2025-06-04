<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer\Parser;

use CssLint\Position;
use CssLint\Token\WhitespaceToken;
use CssLint\Tokenizer\Parser\EndOfLineParser;
use CssLint\Tokenizer\TokenizerContext;
use Tests\TestSuite\TestCase;

class EndOfLineParserTest extends TestCase
{
    private EndOfLineParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new EndOfLineParser();
    }

    public function testGetHandledTokenClass(): void
    {
        $this->assertEquals(WhitespaceToken::class, $this->parser->getHandledTokenClass());
    }

    public function endOfLineProvider(): array
    {
        return [
            'newline' => ["\n", true],
            'carriage return' => ["\r", false],
            'carriage return newline' => ["\r\n", true],
            'non end of line' => ['a', false],
            'space' => [' ', false],
            'tab' => ["\t", false],
        ];
    }

    /**
     * @dataProvider endOfLineProvider
     */
    public function testParseCurrentContext(string $char, bool $shouldIncrementLine): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $initialLine = $tokenizerContext->getCurrentPosition()->getLine();

        // Act
        $tokenizerContext->appendCurrentContent($char);
        $this->parser->parseCurrentContext($tokenizerContext);

        // Assert
        $expectedLine = $initialLine + ($shouldIncrementLine ? 1 : 0);
        $this->assertEquals($expectedLine, $tokenizerContext->getCurrentPosition()->getLine());
    }

    public function testParseCurrentContextReturnsNull(): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->appendCurrentContent("\n");

        // Act
        $result = $this->parser->parseCurrentContext($tokenizerContext);

        // Assert
        $this->assertNull($result);
    }
}
