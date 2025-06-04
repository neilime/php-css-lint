<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer\Parser;

use CssLint\Position;
use CssLint\Token\SelectorToken;
use CssLint\Tokenizer\Parser\SelectorParser;
use CssLint\Tokenizer\TokenizerContext;
use Tests\TestSuite\TestCase;

class SelectorParserTest extends TestCase
{
    private SelectorParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SelectorParser();
    }

    public function testGetHandledTokenClass(): void
    {
        $this->assertEquals(SelectorToken::class, $this->parser->getHandledTokenClass());
    }

    public function validSelectorsProvider(): array
    {
        return [
            'simple class selector' => ['.class-name {', '.class-name'],
            'id selector' => ['#my-id {', '#my-id'],
            'element selector' => ['div {', 'div'],
            'multiple classes' => ['.class1.class2 {', '.class1.class2'],
            'element with class' => ['div.class {', 'div.class'],
            'element with id' => ['div#id {', 'div#id'],
            'pseudo class' => ['a:hover {', 'a:hover'],
            'complex selector' => ['div.class-name:hover {', 'div.class-name:hover'],
            'kebab case class' => ['.my-class-name {', '.my-class-name'],
            'underscore in class' => ['.my_class_name {', '.my_class_name'],
            'numbers in selector' => ['.class123 {', '.class123'],
            'button with pseudo class' => ['.button.dropdown::after {', '.button.dropdown::after'],
            'attribute selector' => ['[data-test="value"] {', '[data-test="value"]'],
            'multiple selectors' => ['div, .class, #id {', 'div, .class, #id'],
            'child combinator' => ['div > p {', 'div > p'],
            'adjacent sibling' => ['div + p {', 'div + p'],
            'general sibling' => ['div ~ p {', 'div ~ p'],
        ];
    }

    /**
     * @dataProvider validSelectorsProvider
     */
    public function testParsesValidSelectors(string $content, string $expectedValue): void
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
        $this->assertInstanceOf(SelectorToken::class, $token);
        $this->assertEquals($expectedValue, $token->getValue());
    }

    public function testIgnoresNonSelectorContent(): void
    {
        // Arrange
        $content = 'not-a-selector';
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

    public function testHandlesSelectorWithSpaces(): void
    {
        // Arrange
        $content = '  .class-name  {';
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
        $this->assertInstanceOf(SelectorToken::class, $token);
        $this->assertEquals('.class-name', $token->getValue());
    }

    public function testHandlesSelectorWithNewlines(): void
    {
        // Arrange
        $content = "\n.class-name\n{";
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
        $this->assertInstanceOf(SelectorToken::class, $token);
        $this->assertEquals('.class-name', $token->getValue());
    }

    public function testHandlesSelectorWithMultipleSpaces(): void
    {
        // Arrange
        $content = 'div    p    {';
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
        $this->assertInstanceOf(SelectorToken::class, $token);
        $this->assertEquals('div    p', $token->getValue());
    }

    public function testHandlesSelectorWithParentheses(): void
    {
        // Arrange
        $content = ':not(.class) {';
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
        $this->assertInstanceOf(SelectorToken::class, $token);
        $this->assertEquals(':not(.class)', $token->getValue());
    }
}
