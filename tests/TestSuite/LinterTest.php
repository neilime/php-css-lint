<?php

namespace Tests\TestSuite;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use CssLint\Linter;
use CssLint\LintConfiguration;
use CssLint\Tokenizer\Tokenizer;
use InvalidArgumentException;
use TypeError;

class LinterTest extends TestCase
{
    private $testFixturesDir;

    /**
     * @var Linter
     */
    protected $linter;

    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    protected function setUp(): void
    {
        $this->testFixturesDir =  realpath(__DIR__ . '/../fixtures');

        $this->linter = new Linter();

        $this->root = vfsStream::setup('testDir');
    }

    public function testConstructWithCustomCssLintProperties()
    {
        $lintConfiguration = new LintConfiguration();
        $linter = new Linter($lintConfiguration);
        $this->assertSame($lintConfiguration, $linter->getLintConfiguration());
    }

    public function testConstructWithCustomTokenizer()
    {
        $tokenizer = new Tokenizer();
        $linter = new Linter(null, $tokenizer);
        $this->assertSame($tokenizer, $linter->getTokenizer());
    }

    public function testSetTokenizer()
    {
        $tokenizer = new Tokenizer();
        $this->linter->setTokenizer($tokenizer);
        $this->assertSame($tokenizer, $this->linter->getTokenizer());
    }

    public function testLintValidString()
    {
        $errors = iterator_to_array(
            $this->linter->lintString('.button.dropdown::after {
    display: block;
    width: 0;
    height: 0;
    border: inset 0.4em;
    content: "";
    border-bottom-width: 0;
    border-top-style: solid;
    border-color: #fefefe transparent transparent;
    position: relative;
    top: 0.4em;
    display: inline-block;
    float: right;
    margin-left: 1em; }
  .button.arrow-only::after {
    top: -0.1em;
    float: none;
    margin-left: 0; }'),
            false
        );
        $this->assertEmpty($errors, json_encode($errors, JSON_PRETTY_PRINT));
    }

    public function testLintNotValidString()
    {
        // Act
        $errors = $this->linter->lintString('.button.dropdown::after {
             displady: block;
    width: 0;
    :
            ');

        // Assert
        $this->assertErrorsEquals([
            [
                'key' => 'invalid_property_declaration',
                'message' => 'property - Unknown property "displady"',
                'start' => [
                    'line' => 2,
                    'column' => 14,
                ],
                'end' => [
                    'line' => 2,
                    'column' => 29,
                ],
            ],
            [
                'key' => 'unclosed_token',
                'message' => 'block - Unclosed "block" detected',
                'start' => [
                    'line' => 1,
                    'column' => 24,
                ],
                'end' => [
                    'line' => 5,
                    'column' => 14,
                ],
            ],
            [
                'key' => 'unexpected_character_end_of_content',
                'message' => 'Unexpected character at end of content: ":"',
                'start' => [
                    'line' => 5,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 5,
                    'column' => 14,
                ],
            ],
        ], $errors);
    }

    public function testLintStringWithUnterminatedContext()
    {
        // Act
        $errors = $this->linter->lintString('* {');

        // Assert
        $this->assertErrorsEquals([
            [
                'key' => 'unclosed_token',
                'message' => 'block - Unclosed "block" detected',
                'start' => [
                    'line' => 1,
                    'column' => 1,
                ],
                'end' => [
                    'line' => 1,
                    'column' => 4,
                ],
            ],
        ], $errors);
    }

    public function testLintStringWithWrongTypeParam()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'CssLint\Linter::lintString(): Argument #1 ($stringValue) must be of type string, array given'
        );
        iterator_to_array($this->linter->lintString(['wrong']), false);
    }

    public function testLintFileWithWrongTypeParam()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'CssLint\Linter::lintFile(): Argument #1 ($filePath) must be of type string, array given'
        );
        iterator_to_array($this->linter->lintFile(['wrong']), false);
    }

    public function testLintFileWithUnknownFilePathParam()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "$filePath" "wrong" is not an existing file path');
        iterator_to_array($this->linter->lintFile('wrong'), false);
    }

    public function testLintFileWithUnreadableFilePathParam()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "$filePath" "vfs://testDir/foo.txt" is not a readable file path');

        $testFile = new vfsStreamFile('foo.txt', 0o000);
        $this->root->addChild($testFile);

        $fileToLint = $testFile->url();

        $this->assertFileIsNotReadable($fileToLint);

        iterator_to_array($this->linter->lintFile($fileToLint), false);
    }

    public function testLintComment()
    {
        // Act
        $errors =
            $this->linter->lintString(
                "/*" . PHP_EOL .
                    " * This is a comment" . PHP_EOL .
                    "*/" . PHP_EOL .
                    ".test { }"
            );

        // Assert
        $this->assertErrorsEquals([], $errors, json_encode($errors, JSON_PRETTY_PRINT));
    }

    public function testLintNotValidCssFile()
    {
        // Arrange
        $fileToLint = $this->testFixturesDir . '/not_valid.css';

        // Act
        $errors = $this->linter->lintFile($fileToLint);

        // Assert
        $this->assertErrorsEquals(
            [
                [
                    'key' => 'invalid_property_declaration',
                    'message' => 'property - Unknown property "bordr-top-style"',
                    'start' => [
                        'line' => 3,
                        'column' => 5,
                    ],
                    'end' => [
                        'line' => 3,
                        'column' => 27,
                    ],
                ],
                [
                    'key' => 'unclosed_token',
                    'message' => 'block - Unclosed "block" detected',
                    'start' => [
                        'line' => 1,
                        'column' => 23,
                    ],
                    'end' => [
                        'line' => 6,
                        'column' => 2,
                    ],
                ],
            ],
            $errors
        );
    }
}
