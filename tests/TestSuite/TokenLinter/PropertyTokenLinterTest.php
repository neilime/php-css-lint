<?php

declare(strict_types=1);

namespace Tests\TestSuite\TokenLinter;

use CssLint\LintConfiguration;
use CssLint\LintErrorKey;
use CssLint\Position;
use CssLint\Token\PropertyToken;
use CssLint\Token\BlockToken;
use CssLint\TokenLinter\PropertyTokenLinter;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestSuite\TestCase;

class PropertyTokenLinterTest extends TestCase
{
    private PropertyTokenLinter $linter;
    private LintConfiguration $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configuration = new LintConfiguration();
        $this->linter = new PropertyTokenLinter($this->configuration);
    }

    public function testSupportsOnlyPropertyTokens(): void
    {
        $token = new PropertyToken('color', 'red', new Position(1, 0), new Position(1, 10));
        $this->assertTrue($this->linter->supports($token), 'Should support PropertyToken');

        $nonPropertyToken = new BlockToken([], new Position(1, 0), new Position(1, 4));
        $this->assertFalse($this->linter->supports($nonPropertyToken), 'Should not support non-PropertyToken');
    }

    public function validPropertiesProvider(): array
    {
        return [
            'standard property' => ['color', 'red'],
            'property with value' => ['width', '100px'],
            'property with multiple values' => ['margin', '10px 20px'],
            'property with important' => ['color', 'blue !important'],
            'font family' => ['font-family', 'Arial, sans-serif'],
        ];
    }

    /**
     * @dataProvider validPropertiesProvider
     */
    public function testNoErrorsForValidProperties(string $name, string $value): void
    {
        // Arrange
        $token = new PropertyToken($name, $value, new Position(1, 0), new Position(1, strlen($name) + strlen($value) + 1));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function invalidPropertiesProvider(): array
    {
        return [
            'unknown property' => ['unknown-prop', 'value', 'Unknown property "unknown-prop"'],
        ];
    }

    /**
     * @dataProvider invalidPropertiesProvider
     */
    public function testErrorsForInvalidProperties(string $name, string $value, string $expectedMessage): void
    {
        // Arrange
        $token = new PropertyToken($name, $value, new Position(1, 0), new Position(1, strlen($name) + strlen($value) + 1));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals(
            [
                [
                    'key' => LintErrorKey::INVALID_PROPERTY_DECLARATION->value,
                    'message' => sprintf('property - %s', $expectedMessage),
                    'start' => [
                        'line' => 1,
                        'column' => 0,
                    ],
                    'end' => [
                        'line' => 1,
                        'column' => strlen($name) + strlen($value) + 1,
                    ],
                ],
            ],
            $errors
        );
    }

    public function validVariablesProvider(): array
    {
        return [
            'simple variable' => ['--primary-color', 'blue'],
            'variable with numbers' => ['--size-1', '10px'],
            'variable with multiple dashes' => ['--my-custom-var', 'value'],
        ];
    }

    /**
     * @dataProvider validVariablesProvider
     */
    public function testNoErrorsForValidVariables(string $name, string $value): void
    {
        // Arrange
        $token = new PropertyToken($name, $value, new Position(1, 0), new Position(1, strlen($name) + strlen($value) + 1));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function invalidVariablesProvider(): array
    {
        return [
            'single dash' => ['-invalid', 'value', 'Unknown property "-invalid"'],
            'no dash prefix' => ['invalid', 'value', 'Unknown property "invalid"'],
            'special characters' => ['--invalid@var', 'value', 'Invalid variable format: "--invalid@var"'],
            'space in name' => ['--invalid var', 'value', 'Invalid variable format: "--invalid var"'],
        ];
    }

    /**
     * @dataProvider invalidVariablesProvider
     */
    public function testErrorsForInvalidVariables(string $name, string $value, string $expectedMessage): void
    {
        // Arrange
        $token = new PropertyToken($name, $value, new Position(1, 0), new Position(1, strlen($name) + strlen($value) + 1));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals(
            [
                [
                    'key' => LintErrorKey::INVALID_PROPERTY_DECLARATION->value,
                    'message' => 'property - ' . $expectedMessage,
                    'start' => [
                        'line' => 1,
                        'column' => 0,
                    ],
                    'end' => [
                        'line' => 1,
                        'column' => strlen($name) + strlen($value) + 1,
                    ],
                ],
            ],
            $errors
        );
    }

    public function testErrorsForEmptyPropertyName(): void
    {
        // Arrange
        $token = new PropertyToken('', 'value', new Position(1, 0), new Position(1, 5));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals(
            [
                [
                    'key' => LintErrorKey::INVALID_PROPERTY_DECLARATION->value,
                    'message' => 'property - Property name is empty',
                    'start' => [
                        'line' => 1,
                        'column' => 0,
                    ],
                    'end' => [
                        'line' => 1,
                        'column' => 5,
                    ],
                ],
            ],
            $errors,
        );
    }

    public function testErrorsForEmptyPropertyValue(): void
    {
        // Arrange
        $token = new PropertyToken('color', null, new Position(1, 0), new Position(1, 5));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals(
            [
                [
                    'key' => LintErrorKey::INVALID_PROPERTY_DECLARATION->value,
                    'message' => 'property - Property value is empty',
                    'start' => [
                        'line' => 1,
                        'column' => 0,
                    ],
                    'end' => [
                        'line' => 1,
                        'column' => 5,
                    ],
                ],
            ],
            $errors,
        );
    }
}
