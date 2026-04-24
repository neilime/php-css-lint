<?php

declare(strict_types=1);

namespace Tests\TestSuite\Tokenizer;

use CssLint\Position;
use CssLint\Tokenizer\TokenizerContext;
use PHPUnit\Framework\TestCase;
use CssLint\Token\Token;

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
    public function testCurrentPosition(): void
    {
        $initialPosition = $this->context->getCurrentPosition();
        $this->assertInstanceOf(Position::class, $initialPosition);
        $this->assertEquals(1, $initialPosition->getLine());
        $this->assertEquals(1, $initialPosition->getColumn());
    }
}
