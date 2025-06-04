<?php

declare(strict_types=1);

namespace Tests\TestSuite\TokenLinter;

use CssLint\LintErrorKey;
use CssLint\Token\SelectorToken;
use CssLint\TokenLinter\SelectorTokenLinter;
use CssLint\Token\PropertyToken;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestSuite\TestCase;

class SelectorTokenLinterTest extends TestCase
{
    private SelectorTokenLinter $linter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linter = new SelectorTokenLinter();
    }

    public function validSelectorsProvider(): array
    {
        return [
            'simple class selector' => ['.class-name'],
            'id selector' => ['#my-id'],
            'element selector' => ['div'],
            'multiple classes' => ['.class1.class2'],
            'element with class' => ['div.class'],
            'element with id' => ['div#id'],
            'pseudo class' => ['a:hover'],
            'complex selector' => ['div.class-name:hover'],
            'kebab case class' => ['.my-class-name'],
            'underscore in class' => ['.my_class_name'],
            'numbers in selector' => ['.class123'],
            'button with pseudo class' => ['.button.dropdown::after'],
        ];
    }

    /**
     * @dataProvider validSelectorsProvider
     */
    public function testNoErrorsForValidSelectors(string $selector): void
    {
        // Arrange
        $token = new SelectorToken($selector, 1, 0, strlen($selector));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function invalidSelectorsProvider(): array
    {
        return [
            'contains space' => ['class name'],
            'contains special character @' => ['.class@name'],
            'contains special character !' => ['.class!name'],
            'contains special character $' => ['.class$name'],
            'contains special character %' => ['.class%name'],
            'contains special character ^' => ['.class^name'],
            'contains special character &' => ['.class&name'],
            'contains special character *' => ['.class*name'],
            'contains parentheses' => ['.class(name)'],
            'contains brackets' => ['.class[name]'],
            'contains braces' => ['.class{name}'],
            'double comma' => ['a,,'],
            'double hash' => ['##test'],
            'contains pipe' => ['.a|'],
            'unterminated import' => ['@import url(\''],
        ];
    }

    /**
     * @dataProvider invalidSelectorsProvider
     */
    public function testErrorsForInvalidSelectors(string $selector): void
    {
        // Arrance
        $token = new SelectorToken($selector, 1, 0, strlen($selector));

        // Act
        $errors = $this->linter->lint($token);

        $this->assertErrorsEquals(
            [
                [
                    'key' => LintErrorKey::UNEXPECTED_SELECTOR_CHARACTER->value,
                    'message' => sprintf('selector - Selector contains invalid characters: "%s"', $selector),
                    'line' => 1,
                    'start' => 0,
                    'end' => strlen($selector)
                ]
            ],
            $errors,
        );
    }

    public function testDoesNotSupportNonSelectorTokens(): void
    {
        // Arrange
        $token = new PropertyToken('color', 'red', 1, 0, 5);

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertFalse($this->linter->supports($token));
        $this->assertErrorsEquals([], $errors);
    }

    public function testThrowsExceptionForNonStringValues(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SelectorTokenLinter can only lint SelectorToken with string values');

        /** @var SelectorToken&MockObject $token */
        $token = $this->createMock(SelectorToken::class);
        $token->method('getValue')->willReturn(null);
        $token->method('getLine')->willReturn(1);
        $token->method('getStart')->willReturn(0);
        $token->method('getEnd')->willReturn(4);

        // Act
        iterator_to_array($this->linter->lint($token));
    }
}
