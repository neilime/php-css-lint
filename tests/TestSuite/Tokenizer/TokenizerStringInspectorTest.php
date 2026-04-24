<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer;

use CssLint\Tokenizer\TokenizerStringInspector;
use PHPUnit\Framework\TestCase;

class TokenizerStringInspectorTest extends TestCase
{
    public function testIsSpace(): void
    {
        $this->assertTrue(TokenizerStringInspector::isSpace(' '));
        $this->assertTrue(TokenizerStringInspector::isSpace("\t"));
        $this->assertFalse(TokenizerStringInspector::isSpace('a'));
    }

    public function isWhitespaceProvider(): array
    {
        return [
            'single space' => [' ', true],
            'multiple spaces' => ['   ', true],
            'tab and space' => ["\t ", true],
            'empty string' => ['', false],
            'non whitespace' => [' a', false],
        ];
    }

    /**
     * @dataProvider isWhitespaceProvider
     */
    public function testIsWhitespace(string $content, bool $expected): void
    {
        $this->assertSame($expected, TokenizerStringInspector::isWhitespace($content));
    }

    public function openStringOrParenthesisContextProvider(): array
    {
        return [
            'closed value' => ['color: red;', false],
            'open parenthesis' => ['background: url(', true],
            'closed parenthesis' => ['background: url(test)', false],
            'open double quote' => ['content: "test', true],
            'escaped quote inside string' => ['content: "a\\";b"', false],
            'open single quote' => ["content: 'test", true],
        ];
    }

    /**
     * @dataProvider openStringOrParenthesisContextProvider
     */
    public function testHasOpenStringOrParenthesisContext(string $content, bool $expected): void
    {
        $this->assertSame($expected, TokenizerStringInspector::hasOpenStringOrParenthesisContext($content));
    }

    public function openParenthesisContextProvider(): array
    {
        return [
            'closed value' => ['color: red;', false],
            'open parenthesis' => ['background: url(', true],
            'closed parenthesis' => ['background: url(test)', false],
            'parenthesis inside open quote' => ['content: "test(', false],
            'parenthesis inside quoted string' => ['content: "test()"', false],
        ];
    }

    /**
     * @dataProvider openParenthesisContextProvider
     */
    public function testHasOpenParenthesisContext(string $content, bool $expected): void
    {
        $this->assertSame($expected, TokenizerStringInspector::hasOpenParenthesisContext($content));
    }
}
