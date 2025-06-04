<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer\Parser;

use CssLint\Token\BlockToken;
use CssLint\Tokenizer\Parser\BlockParser;
use CssLint\Tokenizer\TokenizerContext;
use Tests\TestSuite\TestCase;

class BlockParserTest extends TestCase
{
    private BlockParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new BlockParser();
    }

    public function testGetHandledTokenClass(): void
    {
        $this->assertEquals(BlockToken::class, $this->parser->getHandledTokenClass());
    }

    public function blockStartProvider(): array
    {
        return [
            'simple block start' => ['{', true],
            'block start with content' => ['test {', true],
            'block start with quotes' => ['"test" {', true],
            'block start with escaped quotes' => ['"test\" {', true],
            'block start with single quotes' => ["'test' {", true],
            'block start with escaped single quotes' => ["'test\\' {", true],
            'invalid block start' => ['test', false],
            'invalid block start with quotes' => ['"test {', false],
            'invalid block start with single quotes' => ["'test {", false],
        ];
    }

    /**
     * @dataProvider blockStartProvider
     */
    public function testIsBlockStart(string $content, bool $expected): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->appendCurrentContent($content);

        // Act
        $result = BlockParser::isBlockStart($tokenizerContext);

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function blockEndProvider(): array
    {
        return [
            'simple block end' => ['}', true],
            'block end with content' => ['test }', true],
            'block end with spaces' => ['  }', true],
            'invalid block end' => ['test', false],
            'invalid block end with similar char' => ['test ]', false],
        ];
    }

    /**
     * @dataProvider blockEndProvider
     */
    public function testIsBlockEnd(string $content, bool $expected): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->appendCurrentContent($content);

        // Act
        $result = BlockParser::isBlockEnd($tokenizerContext);

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function blockContentProvider(): array
    {
        return [
            'empty block' => ['{}', ''],
            'block with content' => ['{test}', 'test'],
            'block with spaces' => ['{ test }', 'test'],
            'block with multiple lines' => ["{\n  test\n}", "test"],
        ];
    }

    /**
     * @dataProvider blockContentProvider
     */
    public function testGetBlockContent(string $content, string $expected): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->appendCurrentContent($content);

        // Act
        $result = BlockParser::getBlockContent($tokenizerContext);

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function testParseCurrentContextWithBlockStart(): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->appendCurrentContent('{');

        // Act
        $result = $this->parser->parseCurrentContext($tokenizerContext);

        // Assert
        $this->assertNull($result);
        $this->assertInstanceOf(BlockToken::class, $tokenizerContext->getCurrentBlockToken());
    }

    public function testParseCurrentContextWithBlockEnd(): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->appendCurrentContent('{');
        $this->parser->parseCurrentContext($tokenizerContext);
        $tokenizerContext->appendCurrentContent('}');

        // Act
        $result = $this->parser->parseCurrentContext($tokenizerContext);

        // Assert
        $this->assertInstanceOf(BlockToken::class, $result);
        $this->assertNull($tokenizerContext->getCurrentBlockToken());
    }

    public function testParseCurrentContextWithNestedBlocks(): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->appendCurrentContent('{');
        $this->parser->parseCurrentContext($tokenizerContext);
        $tokenizerContext->appendCurrentContent('{');
        $this->parser->parseCurrentContext($tokenizerContext);
        $tokenizerContext->appendCurrentContent('}');

        // Act
        $result = $this->parser->parseCurrentContext($tokenizerContext);

        // Assert
        $this->assertInstanceOf(BlockToken::class, $result);
        $this->assertInstanceOf(BlockToken::class, $tokenizerContext->getCurrentBlockToken());
    }

    public function testParseCurrentContextWithSpace(): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->appendCurrentContent(' ');

        // Act
        $result = $this->parser->parseCurrentContext($tokenizerContext);

        // Assert
        $this->assertNull($result);
    }

    public function testParseCurrentContextWithInvalidContent(): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->appendCurrentContent('test');

        // Act
        $result = $this->parser->parseCurrentContext($tokenizerContext);

        // Assert
        $this->assertNull($result);
    }
}
