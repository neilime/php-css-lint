<?php

declare(strict_types=1);

namespace Tests\TestSuite\TokenLinter;

use CssLint\LintErrorKey;
use CssLint\Position;
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
            'complex selector with multiple classes' => [':where(select:is([multiple], [size])) optgroup'],
        ];
    }

    /**
     * @dataProvider validSelectorsProvider
     */
    public function testNoErrorsForValidSelectors(string $selector): void
    {
        // Arrange
        $token = new SelectorToken($selector, new Position(1, 0), new Position(1, strlen($selector)));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function invalidSelectorsProvider(): array
    {
        return [
            'contains special character @' => ['.class@name', 'Selector contains invalid characters'],
            'contains special character !' => ['.class!name', 'Selector contains invalid characters'],
            'contains special character $' => ['.class$name', 'Selector contains invalid characters'],
            'contains special character %' => ['.class%name', 'Selector contains invalid characters'],
            'contains special character ^' => ['.class^name', 'Selector contains invalid characters'],
            'contains special character &' => ['.class&name', 'Selector contains invalid characters'],
            'contains special character *' => ['.class*name', 'Selector contains invalid characters'],
            'contains braces' => ['.class{name}', 'Selector contains invalid characters'],
            'contains pipe' => ['.a|', 'Selector contains invalid characters'],
            'double comma' => ['a,,', 'Selector contains invalid consecutive characters'],
            'double hash' => ['##test', 'Selector contains invalid consecutive characters'],
            'unbalanced parentheses' => ['.class(name))', 'Selector contains invalid number of parentheses'],
        ];
    }

    /**
     * @dataProvider invalidSelectorsProvider
     */
    public function testErrorsForInvalidSelectors(string $selector, $message): void
    {
        // Arrance
        $token = new SelectorToken($selector, new Position(1, 0), new Position(1, strlen($selector)));

        // Act
        $errors = $this->linter->lint($token);

        $this->assertErrorsEquals(
            [
                [
                    'key' => LintErrorKey::UNEXPECTED_SELECTOR_CHARACTER->value,
                    'message' => sprintf('selector - %s: "%s"', $message, $selector),
                    'start' => [
                        'line' => 1,
                        'column' => 0,
                    ],
                    'end' => [
                        'line' => 1,
                        'column' => strlen($selector),
                    ],
                ],
            ],
            $errors,
        );
    }

    public function testDoesNotSupportNonSelectorTokens(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SelectorTokenLinter can only lint SelectorToken');

        // Arrange
        $token = new PropertyToken('color', 'red', new Position(1, 0), new Position(1, 5));

        // Act
        iterator_to_array($this->linter->lint($token), false);
    }
}
