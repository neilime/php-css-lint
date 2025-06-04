<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer;

use CssLint\Position;
use CssLint\Token\Token;
use CssLint\Tokenizer\TokenizerContext;
use PHPUnit\Framework\TestCase;

class TokenizerContextTest extends TestCase
{
    private TokenizerContext $context;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = new TokenizerContext();
    }

    public function testInitialState(): void
    {
        $this->assertEmpty($this->context->getCurrentContent());
        $this->assertNull($this->context->getCurrentToken());
        $this->assertEquals(new Position(1, 1), $this->context->getCurrentPosition());
    }

    public function contentManipulationProvider(): array
    {
        return [
            'simple content' => ['test', 'ing', 'testing'],
            'with spaces' => ['hello ', 'world', 'hello world'],
            'empty append' => ['content', '', 'content'],
            'special chars' => ['test:', '123', 'test:123'],
        ];
    }

    /**
     * @dataProvider contentManipulationProvider
     */
    public function testContentManipulation(string $content, string $appendContent, string $expectedResult): void
    {
        $this->context->appendCurrentContent($content);
        $this->assertEquals($content, $this->context->getCurrentContent());

        $this->context->appendCurrentContent($appendContent);
        $this->assertEquals($expectedResult, $this->context->getCurrentContent());

        $this->context->resetCurrentContent();
        $this->assertEmpty($this->context->getCurrentContent());
    }

    public function getNthLastCharsProvider(): array
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
     * @dataProvider getNthLastCharsProvider
     */
    public function testGetNthLastChars(string $content, int $length, int $offset, ?string $expected): void
    {
        $this->context->appendCurrentContent($content);
        $this->assertEquals($content, $this->context->getCurrentContent());
        $this->assertEquals($expected, $this->context->getNthLastChars($length, $offset));
    }

    public function testGetLastChar(): void
    {
        $this->assertNull($this->context->getLastChar());

        $this->context->appendCurrentContent('test');
        $this->assertEquals('t', $this->context->getLastChar());

        $this->context->resetCurrentContent();
        $this->assertNull($this->context->getLastChar());
    }

    public function testTokenManipulation(): void
    {
        /** @var Token $mockToken */
        $mockToken = $this->getMockBuilder(Token::class)->getMock();

        $this->assertNull($this->context->getCurrentToken());

        $this->context->setCurrentToken($mockToken);
        $this->assertSame($mockToken, $this->context->getCurrentToken());

        $this->context->resetCurrentToken();
        $this->assertNull($this->context->getCurrentToken());
    }

    /**
     * @dataProvider tokenAssertionProvider
     */
    public function testAssertCurrentToken(?string $tokenClass, ?Token $currentToken, bool $expected): void
    {
        if ($currentToken !== null) {
            $this->context->setCurrentToken($currentToken);
        }

        $this->assertEquals($expected, $this->context->assertCurrentToken($tokenClass));
    }

    public function tokenAssertionProvider(): array
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

    public function testCurrentPosition(): void
    {
        $initialPosition = $this->context->getCurrentPosition();
        $this->assertInstanceOf(Position::class, $initialPosition);
        $this->assertEquals(1, $initialPosition->getLine());
        $this->assertEquals(1, $initialPosition->getColumn());
    }
}
