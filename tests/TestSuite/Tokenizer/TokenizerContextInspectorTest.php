<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer;

use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;
use CssLint\Tokenizer\TokenizerContextInspector;
use PHPUnit\Framework\TestCase;

class TokenizerContextInspectorTest extends TestCase
{
    private TokenizerContext $context;

    private TokenizerContextInspector $inspector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = new TokenizerContext();
        $this->inspector = new TokenizerContextInspector($this->context);
    }

    public function nthLastCharsProvider(): array
    {
        return [
            'one char' => ['t', 1, 0, 't'],
            'single last char' => ['testing', 1, 0, 'g'],
            'multiple chars' => ['testing', 3, 0, 'ing'],
            'with offset' => ['testing', 2, 1, 'in'],
            'empty content' => ['', 1, 0, null],
            'length greater than content' => ['test', 5, 0, null],
            'offset beyond content' => ['test', 1, 4, null],
        ];
    }

    /**
     * @dataProvider nthLastCharsProvider
     */
    public function testNthLastChars(string $content, int $length, int $offset, ?string $expected): void
    {
        $this->context->appendCurrentContent($content);

        $this->assertSame($expected, $this->inspector->nthLastChars($length, $offset));
    }

    public function testLastChar(): void
    {
        $this->assertNull($this->inspector->lastChar());

        $this->context->appendCurrentContent('test');
        $this->assertSame('t', $this->inspector->lastChar());
    }

    public function currentTokenMatchesProvider(): array
    {
        /** @var Token $mockToken */
        $mockToken = $this->getMockBuilder(Token::class)->getMock();

        return [
            'null token and null class' => [null, null, true],
            'null token with class' => [Token::class, null, false],
            'matching token and class' => [get_class($mockToken), $mockToken, true],
            'non-matching token and class' => ['OtherTokenClass', $mockToken, false],
        ];
    }

    /**
     * @dataProvider currentTokenMatchesProvider
     */
    public function testCurrentTokenMatches(?string $tokenClass, ?Token $currentToken, bool $expected): void
    {
        if ($currentToken !== null) {
            $this->context->setCurrentToken($currentToken);
        }

        $this->assertSame($expected, $this->inspector->currentTokenMatches($tokenClass));
    }

    public function testCurrentContentEndsWith(): void
    {
        $this->context->appendCurrentContent('color: red;');

        $this->assertTrue($this->inspector->currentContentEndsWith(';'));
        $this->assertFalse($this->inspector->currentContentEndsWith('}'));
    }

    public function testLastCharIsSpace(): void
    {
        $this->assertFalse($this->inspector->lastCharIsSpace());

        $this->context->appendCurrentContent(' ');
        $this->assertTrue($this->inspector->lastCharIsSpace());
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
        $this->context->appendCurrentContent($content);

        $this->assertSame($expected, $this->inspector->hasOpenStringOrParenthesisContext());
    }
}
