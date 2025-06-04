<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer\Parser;

use CssLint\Position;
use CssLint\Token\PropertyToken;
use CssLint\Token\BlockToken;
use CssLint\Tokenizer\Parser\PropertyParser;
use CssLint\Tokenizer\TokenizerContext;
use Tests\TestSuite\TestCase;

class PropertyParserTest extends TestCase
{
    private PropertyParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new PropertyParser();
    }

    public function testGetHandledTokenClass(): void
    {
        $this->assertEquals(PropertyToken::class, $this->parser->getHandledTokenClass());
    }

    public function validPropertiesProvider(): array
    {
        return [
            'simple property' => ['color: red;', 'color', 'red'],
            'property with spaces' => ['margin : 10px;', 'margin', '10px'],
            'property with multiple values' => ['padding: 10px 20px;', 'padding', '10px 20px'],
            'property with important' => ['color: blue !important;', 'color', 'blue !important'],
            'custom property' => ['--custom-prop: value;', '--custom-prop', 'value'],
            'vendor prefix property' => ['-webkit-transform: rotate(45deg);', '-webkit-transform', 'rotate(45deg)'],
        ];
    }

    /**
     * @dataProvider validPropertiesProvider
     */
    public function testParsesValidProperties(string $content, string $expectedName, string $expectedValue): void
    {
        // Arrange
        $tokenizerContext = new TokenizerContext();
        $blockToken = new BlockToken([], new Position(1, 0), new Position(1, 0));
        $tokenizerContext->setCurrentBlockToken($blockToken);
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
        $this->assertInstanceOf(PropertyToken::class, $lastToken);
        $this->assertEquals($expectedName, $lastToken->getName());
        $this->assertEquals($expectedValue, $lastToken->getValue());
    }

    public function testIgnoresNonPropertyContent(): void
    {
        // Arrange
        $content = 'not-a-property';
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

    public function testIgnoresContentOutsideBlock(): void
    {
        // Arrange
        $content = 'color: red;';
        $tokenizerContext = new TokenizerContext();
        $tokenizerContext->setCurrentBlockToken(null);
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

    public function testHandlesPropertyEndingWithBlockEnd(): void
    {
        // Arrange
        $content = 'color: red}';
        $tokenizerContext = new TokenizerContext();
        $blockToken = new BlockToken([], new Position(1, 0), new Position(1, 0));
        $tokenizerContext->setCurrentBlockToken($blockToken);
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
        $this->assertInstanceOf(PropertyToken::class, $lastToken);
        $this->assertEquals('color', $lastToken->getName());
        $this->assertEquals('red', $lastToken->getValue());
    }
}
