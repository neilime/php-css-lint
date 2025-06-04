<?php

namespace Tests\TestSuite;

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
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream));

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'selector',
                'value' => '.button',
                'line' => 1,
                'start' => 0,
                'end' => 8
            ],
            [
                'key' => 'unclosed_token',
                'message' => 'block - Unclosed block detected',
                'line' => 1,
                'start' => 8,
                'end' => 0
            ]
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    public function testTokenizeInvalidUnexpectedBlockChar()
    {
        // Arrange
        $stream = $this->getStream('.button { } }');

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream));

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'selector',
                'value' => '.button',
                'line' => 1,
                'start' => 0,
                'end' => 8
            ],
            [
                'type' => 'block',
                'value' => [
                    [
                        'type' => 'whitespace',
                        'value' => " ",
                        'line' => 1,
                        'start' => 9,
                        'end' => 0
                    ]
                ],
                'line' => 1,
                'start' => 8,
                'end' => 10
            ],
            [
                'type' => 'whitespace',
                'value' => " ",
                'line' => 1,
                'start' => 11,
                'end' => 12
            ],
            [
                'key' => 'unexpected_character_end_of_content',
                'message' => "Unexpected character at end of content: \"}\"",
                'line' => 1,
                'start' => 13,
                'end' => 14
            ]
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }


    public function testTokenizeValidSelector()
    {
        // Arrange
        $stream = $this->getStream('.button.dropdown::after {}');

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream));

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'selector',
                'value' => '.button.dropdown::after',
                'line' => 1,
                'start' => 0,
                'end' => 24
            ],
            [
                'type' => 'block',
                'value' => [],
                'line' => 1,
                'start' => 24,
                'end' => 25
            ]
        ];
        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    public function testTokenizeValidBlock()
    {
        // Arrange
        $stream = $this->getStream('.button { display: block; width: 10px; }');

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream));

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'selector',
                'value' => '.button',
                'line' => 1,
                'start' => 0,
                'end' => 8
            ],
            [
                'type' => 'block',
                'value' => [
                    [
                        'type' => 'whitespace',
                        'value' => " ",
                        'line' => 1,
                        'start' => 9,
                        'end' => 10
                    ],
                    [
                        'type' => 'property',
                        'value' => [
                            'name' => 'display',
                            'value' => 'block',
                        ],
                        'line' => 1,
                        'start' => 11,
                        'end' => 24,
                    ],
                    [
                        'type' => 'whitespace',
                        'value' => " ",
                        'line' => 1,
                        'start' => 25,
                        'end' => 26
                    ],
                    [
                        'type' => 'property',
                        'value' => [
                            'name' => 'width',
                            'value' => '10px',
                        ],
                        'line' => 1,
                        'start' => 27,
                        'end' => 37,
                    ],
                    [
                        'type' => 'whitespace',
                        'value' => " ",
                        'line' => 1,
                        'start' => 38,
                        'end' => 0
                    ],
                ],
                'line' => 1,
                'start' => 8,
                'end' => 39
            ]
        ];

        $this->assertTokensOrErrorsEquals($expectedTokensOrErrors, $tokensOrErrors);
    }

    public function testTokenizeWithComments()
    {
        // Arrange
        $stream = $this->getStream('/* This is a comment */ .button { display: block; }');

        // Act
        $tokensOrErrors = iterator_to_array($this->tokenizer->tokenize($stream));

        // Assert
        $expectedTokensOrErrors = [
            [
                'type' => 'comment',
                'value' => '/* This is a comment */',
                'line' => 1,
                'start' => 0,
                'end' => 22
            ],
            [
                'type' => 'whitespace',
                'value' => ' ',
                'line' => 1,
                'start' => 23,
                'end' => 24
            ],
            [
                'type' => 'selector',
                'value' => '.button',
                'line' => 1,
                'start' => 24,
                'end' => 32
            ],
            [
                'type' => 'block',
                'value' => [
                    [
                        'type' => 'whitespace',
                        'value' => ' ',
                        'line' => 1,
                        'start' => 33,
                        'end' => 34
                    ],
                    [
                        'type' => 'property',
                        'value' => [
                            'name' => 'display',
                            'value' => 'block',
                        ],
                        'line' => 1,
                        'start' => 35,
                        'end' => 48,
                    ],
                    [
                        'type' => 'whitespace',
                        'value' => ' ',
                        'line' => 1,
                        'start' => 49,
                        'end' => 0
                    ],
                ],
                'line' => 1,
                'start' => 32,
                'end' => 50
            ]
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

        $this->assertCount(count($expected), $actual, print_r($actual, true));
        foreach ($actual as $key => $tokenOrError) {
            $this->assertEquals(
                $expected[$key],
                $tokenOrError->jsonSerialize(),
                "Token or error at index $key does not match expected value."
            );
        }
    }
}
