<?php

declare(strict_types=1);

namespace Tests\TestSuite\TokenLinter;

use CssLint\LintConfiguration;
use CssLint\LintErrorKey;
use CssLint\Position;
use CssLint\Token\AtRuleToken;
use CssLint\Token\BlockToken;
use CssLint\TokenLinter\AtRuleTokenLinter;
use InvalidArgumentException;
use Tests\TestSuite\TestCase;

class AtRuleTokenLinterTest extends TestCase
{
    private AtRuleTokenLinter $linter;
    private LintConfiguration $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configuration = new LintConfiguration();
        $this->linter = new AtRuleTokenLinter($this->configuration);
    }

    public function testSupportsOnlyAtRuleTokens(): void
    {
        $token = new AtRuleToken('media', 'screen', new Position(1, 0), new Position(1, 10));
        $this->assertTrue($this->linter->supports($token), 'Should support AtRuleToken');

        $nonAtRuleToken = new BlockToken([], new Position(1, 0), new Position(1, 4));
        $this->assertFalse($this->linter->supports($nonAtRuleToken), 'Should not support non-AtRuleToken');
    }

    public function validAtRulesProvider(): array
    {
        return [
            'simple import' => ['import', '"styles.css"'],
            'import with url()' => ['import', 'url("styles.css")'],
            'import with media query' => ['import', '"print.css" print'],
            'import with supports' => ['import', '"grid.css" supports(display: grid)'],
            'import with layer' => ['import', '"base.css" layer(base)'],
            'import with media and layer' => ['import', '"theme.css" layer(theme) screen'],
            'import rule' => ['import', 'url(\'https://fonts.googleapis.com/css2?family=Poppins&display=swap\')'],
            'complex import rule' => ['import', 'url(\'https://fonts.googleapis.com/css2?family=Comic+Neue:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap\')'],
            'charset declaration' => ['charset', '"UTF-8"'],
            'media query' => ['media', 'screen'],
            'supports query' => ['supports', '(display: grid)'],
            'layer declaration' => ['layer', 'base'],
            'keyframes' => ['keyframes', 'slide-in'],
            'font-face' => ['font-face', null],
            'page' => ['page', ':first'],
            'namespace' => ['namespace', 'svg url("http://www.w3.org/2000/svg")'],
        ];
    }

    /**
     * @dataProvider validAtRulesProvider
     */
    public function testNoErrorsForValidAtRules(string $name, ?string $value): void
    {
        // Arrange
        $token = new AtRuleToken($name, $value, new Position(1, 0), new Position(1, strlen($name) + (strlen($value ?? '') + 1)));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function invalidAtRulesProvider(): array
    {
        return [
            'empty name' => ['', null, 'At-rule name is empty'],
            'unknown at-rule' => ['unknown', null, 'Unknown at-rule "unknown"'],
            'empty import value' => ['import', null, 'Import value is empty'],
            'import with unquoted URL' => ['import', 'styles.css', 'Import URL must be a quoted string or url() function'],
            'import with invalid URL format' => ['import', 'url(styles.css', 'Import URL must be a quoted string or url() function'],
            'import with invalid media query' => ['import', '"styles.css" invalid-media', 'Invalid import conditions. Must be a valid media query, supports() condition, or layer() declaration'],
            'import with invalid supports' => ['import', '"styles.css" supports()', 'Invalid import conditions. Must be a valid media query, supports() condition, or layer() declaration'],
            'import with invalid layer' => ['import', '"styles.css" layer', 'Invalid import conditions. Must be a valid media query, supports() condition, or layer() declaration'],
            'charset without quotes' => ['charset', 'UTF-8', 'Charset value must be a quoted string'],
            'empty charset value' => ['charset', null, 'Charset value must be a quoted string'],
            'layer with invalid characters' => ['layer', '#invalid', 'Layer value is not valid: "#invalid"'],
            'layer with ending comma' => ['layer', 'invalid layer,', 'Layer value should not have a comma at the end'],
            'layer with consecutive commas' => ['layer', 'invalid,,layer', 'Layer value should not have consecutive commas'],
        ];
    }

    /**
     * @dataProvider invalidAtRulesProvider
     */
    public function testErrorsForInvalidAtRules(string $name, ?string $value, string $expectedMessage): void
    {
        // Arrange
        $token = new AtRuleToken($name, $value, new Position(1, 0), new Position(1, strlen($name) + (strlen($value ?? '') + 1)));

        // Act
        $errors = $this->linter->lint($token);

        // Assert
        $this->assertErrorsEquals(
            [
                [
                    'key' => LintErrorKey::INVALID_AT_RULE_DECLARATION->value,
                    'message' => sprintf('at-rule - %s', $expectedMessage),
                    'start' => [
                        'line' => 1,
                        'column' => 0,
                    ],
                    'end' => [
                        'line' => 1,
                        'column' => strlen($name) + (strlen($value ?? '') + 1),
                    ],
                ],
            ],
            $errors
        );
    }

    public function testThrowsExceptionForNonAtRuleToken(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('AtRuleTokenLinter can only lint AtRuleToken');

        // Arrange
        $token = new BlockToken([], new Position(1, 0), new Position(1, 4));

        // Act
        iterator_to_array($this->linter->lint($token), false);
    }
}
