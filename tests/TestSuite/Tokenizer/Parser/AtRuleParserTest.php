<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer\Parser;

use CssLint\Position;
use CssLint\Token\AtRuleToken;
use CssLint\Token\BlockToken;
use CssLint\Tokenizer\Parser\AtRuleParser;
use CssLint\Tokenizer\TokenizerContext;
use Tests\TestSuite\TestCase;

class AtRuleParserTest extends TestCase
{
    private AtRuleParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new AtRuleParser();
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

    /**
     * @dataProvider validAtRuleProvider
     */
    public function testParsesValidAtRule(string $content, string $expectedName, ?string $expectedValue): void
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
        $this->assertInstanceOf(AtRuleToken::class, $token);
        $this->assertEquals($expectedName, $token->getName());
        $this->assertEquals($expectedValue, $token->getValue());
    }

    public function testIgnoresNonAtRuleContent(): void
    {
        // Arrange
        $content = 'not-an-at-rule';
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

    public function testHandlesAtRuleInBlock(): void
    {
        // Arrange
        $content = '@media screen;';
        $tokenizerContext = new TokenizerContext();
        $blockToken = new BlockToken([], new Position(1, 0));
        $tokenizerContext = new TokenizerContext();
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
        $this->assertCount(1, $tokens);

        $blockTokenTokens = $blockToken->getValue();
        $this->assertCount(1, $blockTokenTokens, json_encode($blockTokenTokens, JSON_PRETTY_PRINT));
        $this->assertSame($tokens[0], $blockTokenTokens[0]);
        $token = $blockTokenTokens[0];

        $this->assertInstanceOf(AtRuleToken::class, $token);

        /** @var AtRuleToken $token */
        $this->assertTrue($token->isComplete());
        $this->assertEquals('media', $token->getName());
        $this->assertEquals('screen', $token->getValue());
    }

    public function testHandlesAtRuleBlockInBlock(): void
    {
        // Arrange
        $content = '@media screen {';
        $blockToken = new BlockToken([], new Position(1, 0), new Position(1, 0));
        $tokenizerContext = new TokenizerContext();
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
        $this->assertCount(1, $tokens);

        $blockTokenTokens = $blockToken->getValue();
        $this->assertCount(1, $blockTokenTokens);
        $this->assertSame($tokens[0], $blockTokenTokens[0]);
        $token = $blockTokenTokens[0];

        $this->assertInstanceOf(AtRuleToken::class, $token);
        /** @var AtRuleToken $token */
        $this->assertEquals('media', $token->getName());
        $this->assertEquals('screen', $token->getValue());
    }

    public function testHandlesAtRuleWithBlockAndProperties(): void
    {
        // Arrange
        $content = '@font-face {';
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
        $this->assertInstanceOf(AtRuleToken::class, $token);
        $this->assertEquals('font-face', $token->getName());
        $this->assertEquals('', $token->getValue());
    }

    public function testIgnoresWhitespace(): void
    {
        // Arrange
        $content = '  ';
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
