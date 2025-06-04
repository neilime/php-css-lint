<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer\Parser;

use CssLint\Token\AtRuleToken;
use CssLint\Token\BlockToken;
use CssLint\Tokenizer\Parser\AtRuleParser;
use CssLint\Tokenizer\TokenizerContext;
use Tests\TestSuite\TestCase;

class AtRuleParserTest extends TestCase
{
    private AtRuleParser $parser;
    private TokenizerContext $tokenizerContext;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new AtRuleParser();
        $this->tokenizerContext = new TokenizerContext();
    }

    /**
     * @dataProvider validAtRuleProvider
     */
    public function testParsesValidAtRule(string $content, string $expectedName, ?string $expectedValue): void
    {
        // Arrange
        $this->tokenizerContext->appendCurrentContent($content);

        // Act
        $token = $this->parser->parseCurrentContext($this->tokenizerContext);

        // Assert
        $this->assertInstanceOf(AtRuleToken::class, $token);
        $this->assertEquals($expectedName, $token->getName());
        $this->assertEquals($expectedValue, $token->getValue());
    }

    public function testIgnoresNonAtRuleContent(): void
    {
        // Arrange
        $this->tokenizerContext->appendCurrentContent('not-an-at-rule');

        // Act
        $token = $this->parser->parseCurrentContext($this->tokenizerContext);

        // Assert
        $this->assertNull($token);
    }

    public function testHandlesAtRuleInBlock(): void
    {
        // Arrange
        $blockToken = new BlockToken([], 1, 0, 0);
        $this->tokenizerContext->setCurrentToken($blockToken);
        $this->tokenizerContext->appendCurrentContent('@media screen {');

        // Act
        $token = $this->parser->parseCurrentContext($this->tokenizerContext);

        // Assert
        $this->assertInstanceOf(AtRuleToken::class, $token);
        $this->assertEquals('media', $token->getName());
        $this->assertEquals('screen', $token->getValue());
    }

    public function testHandlesAtRuleWithBlockAndProperties(): void
    {
        // Arrange
        $this->tokenizerContext->appendCurrentContent('@font-face {');

        // Act
        $token = $this->parser->parseCurrentContext($this->tokenizerContext);

        // Assert
        $this->assertInstanceOf(AtRuleToken::class, $token);
        $this->assertEquals('font-face', $token->getName());
        $this->assertNull($token->getValue());
    }

    public function testIgnoresWhitespace(): void
    {
        // Arrange
        $this->tokenizerContext->appendCurrentContent('  ');

        // Act
        $token = $this->parser->parseCurrentContext($this->tokenizerContext);

        // Assert
        $this->assertNull($token);
    }

    public function validAtRuleProvider(): array
    {
        return [
            'simple media query' => ['@media screen;', 'media', 'screen'],
            'charset rule' => ['@charset "UTF-8";', 'charset', '"UTF-8"'],
            'import rule' => ['@import url("styles.css");', 'import', 'url("styles.css")'],
            'keyframes rule' => ['@keyframes slide-in {', 'keyframes', 'slide-in'],
            'font-face rule' => ['@font-face {', 'font-face', null],
            'supports rule' => ['@supports (display: grid) {', 'supports', '(display: grid)'],
            'page rule' => ['@page :first {', 'page', ':first'],
            'namespace rule' => ['@namespace svg url("http://www.w3.org/2000/svg");', 'namespace', 'svg url("http://www.w3.org/2000/svg")'],
        ];
    }
}
