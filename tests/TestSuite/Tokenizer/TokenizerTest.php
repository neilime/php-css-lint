<?php

namespace Tests\TestSuite\Tokenizer;

use CssLint\LintConfiguration;
use CssLint\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    /**
     * @var Tokenizer
     */
    protected $tokenizer;

    protected function setUp(): void
    {
        $this->tokenizer = new Tokenizer(new LintConfiguration());
    }

    public function testTokenizeInvalidUnclosedBlock()
    {
        // Arrange
        $stream = $this->getStream('.button {');

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream), false);

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'selector',
                'value' => '.button',
                'start' => [
                    'line' => 1,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 8,
                ],
            ],
            [
                "type" => "block",
                "value" => [],
                "start" => [
                    "line" => 1,
                    "column" => 8,
                ],
                "end" => [
                    "line" => 1,
                    "column" => 10,
                ],
            ],
            [
                'key' => 'unclosed_token',
                'message' => 'block - Unclosed "block" detected',
                'start' => [
                    'line' => 1,
                    'column' => 8,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 10,
                ],
            ],
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    public function testTokenizeInvalidUnexpectedBlockChar()
    {
        // Arrange
        $stream = $this->getStream('.button { } }');

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream), false);

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'selector',
                'value' => '.button',
                'start' => [
                    'line' => 1,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 8,
                ],
            ],
            [
                'type' => 'block',
                'value' => [],
                'start' => [
                    'line' => 1,
                    'column' => 8,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 11,
                ],
            ],
            [
                'key' => 'unexpected_character_end_of_content',
                'message' => "Unexpected character at end of content: \"}\"",
                'start' => [
                    'line' => 1,
                    'column' => 12,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 14,
                ],
            ],
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    public function testTokenizeValidSelectorWithBlock()
    {
        // Arrange
        $stream = $this->getStream('.button.dropdown::after { display: block; width: 10px; }');

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream), false);

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'selector',
                'value' => '.button.dropdown::after',
                'start' => [
                    'line' => 1,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 24,
                ],
            ],
            [
                'type' => 'block',
                'value' => [
                    [
                        'type' => 'property',
                        'value' => [
                            'name' => 'display',
                            'value' => 'block',
                        ],
                        'start' => [
                            'line' => 1,
                            'column' => 25,
                        ],
                        'end' => [
                            'line' => 1,
                            'column' => 40,
                        ],
                    ],
                    [
                        'type' => 'property',
                        'value' => [
                            'name' => 'width',
                            'value' => '10px',
                        ],
                        'start' => [
                            'line' => 1,
                            'column' => 41,
                        ],
                        'end' => [
                            'line' => 1,
                            'column' => 53,
                        ],
                    ],
                ],
                'start' => [
                    'line' => 1,
                    'column' => 24,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 56,
                ],
            ],
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    public function testTokenizeWithMultilineComments()
    {
        // Arrange
        $stream = $this->getStream("/**\nThis is a comment\nThis is an another comment\n**/\n.button { display: block; }");

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream), false);

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'comment',
                'value' => "This is a comment\nThis is an another comment\n",
                'start' => [
                    'line' => 1,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 4,
                    'column' => 4,
                ],
            ],
            [
                'type' => 'selector',
                'value' => '.button',
                'start' => [
                    'line' => 5,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 5,
                    'column' => 9,
                ],
            ],
            [
                'type' => 'block',
                'value' => [
                    [
                        'type' => 'property',
                        'value' => [
                            'name' => 'display',
                            'value' => 'block',
                        ],
                        'start' => [
                            'line' => 5,
                            'column' => 10,
                        ],
                        'end' => [
                            'line' => 5,
                            'column' => 25,
                        ],
                    ],
                ],
                'start' => [
                    'line' => 5,
                    'column' => 9,
                ],
                'end' => [
                    'line' => 5,
                    'column' => 28,
                ],
            ],
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    public function testTokenizeWithAtRuleProperties()
    {
        // Arrange
        $stream = $this->getStream("@font-face{font-family:'Open Sans';src: url('open-sans.woff2');}");

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream), false);

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'at-rule',
                'value' => [
                    'name' => 'font-face',
                    'value' => null,
                    'isBlock' => true,
                ],
                'start' => [
                    'line' => 1,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 10,
                ],
            ],
            [
                'type' => 'block',
                'value' => [
                    [
                        'type' => 'property',
                        'value' => [
                            'name' => 'font-family',
                            'value' => "'Open Sans'",

                        ],
                        'start' => [
                            'line' => 1,
                            'column' => 11,
                        ],
                        'end' => [
                            'line' => 1,
                            'column' => 34,
                        ],
                    ],
                    [
                        'type' => 'property',
                        'value' => [
                            'name' => 'src',
                            'value' => "url('open-sans.woff2')",
                        ],
                        'start' => [
                            'line' => 1,
                            'column' => 35,
                        ],
                        'end' => [
                            'line' => 1,
                            'column' => 62,
                        ],
                    ],
                ],
                'start' => [
                    'line' => 1,
                    'column' => 10,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 64,
                ],
            ],
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    public function testTokenizeWithAtRuleInBlock()
    {
        // Arrange
        $stream = $this->getStream("@layer utilities {\n  @test utilities;\n}");

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream), false);

        // Assert
        $expectedTokensOrErrors = [
            [
                "type" => "at-rule",
                "value" => [
                    "name" => "layer",
                    "value" => "utilities",
                    'isBlock' => true,
                ],
                "start" => [
                    "line" => 1,
                    "column" => 1,
                ],
                "end" => [
                    "line" => 1,
                    "column" => 17,
                ],
            ],
            [
                'type' => 'block',
                'value' => [
                    [
                        'type' => 'whitespace',
                        'value' => '  ',
                        'start' => [
                            'line' => 2,
                            'column' => 1,
                        ],
                        'end' => [
                            'line' => 2,
                            'column' => 3,
                        ],
                    ],
                    [
                        'type' => 'at-rule',
                        'value' => [
                            'name' => 'test',
                            'value' => 'utilities',
                            'isBlock' => false,
                        ],
                        'start' => [
                            'line' => 2,
                            'column' => 3,
                        ],
                        'end' => [
                            'line' => 2,
                            'column' => 18,
                        ],
                    ],
                ],
                'start' => [
                    'line' => 1,
                    'column' => 17,
                ],
                'end' => [
                    'line' => 3,
                    'column' => 2,
                ],
            ],
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    public function testTokenizeWithNestedBlocks()
    {
        // Arrange
        $stream = $this->getStream('@layer utilities { .button { display: block; } }');

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream), false);

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'at-rule',
                'value' => ['name' => 'layer', 'value' => 'utilities', 'isBlock' => true],
                'start' => [
                    'line' => 1,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 17,
                ],
            ],
            [
                'type' => 'block',
                'value' => [
                    [
                        'type' => 'selector',
                        'value' => '.button',
                        'start' => [
                            'line' => 1,
                            'column' => 18,
                        ],
                        'end' => [
                            'line' => 1,
                            'column' => 27,
                        ],
                    ],
                    [
                        'type' => 'block',
                        'value' => [
                            [
                                'type' => 'property',
                                'value' => [
                                    'name' => 'display',
                                    'value' => 'block',
                                ],
                                'start' => [
                                    'line' => 1,
                                    'column' => 28,
                                ],
                                'end' => [
                                    'line' => 1,
                                    'column' => 43,
                                ],
                            ],
                        ],
                        'start' => [
                            'line' => 1,
                            'column' => 27,
                        ],
                        'end' => [
                            'line' => 1,
                            'column' => 46,
                        ],
                    ],
                ],
                'start' => [
                    'line' => 1,
                    'column' => 17,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 48,
                ],
            ],
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    private function getStream(string $css): mixed
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $css);
        rewind($stream);
        return $stream;
    }

    private function assertTokensOrErrorsEquals(array $expected, array $actual)
    {
        $this->assertCount(count($expected), $actual, json_encode($actual, JSON_PRETTY_PRINT));
        foreach ($actual as $key => $tokenOrError) {
            $this->assertEquals(
                $expected[$key],
                $tokenOrError->jsonSerialize(),
                "Token or error at index $key does not match expected value."
            );
        }
    }
}
