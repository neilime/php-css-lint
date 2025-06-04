<?php

namespace Tests\TestSuite;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use CssLint\Linter;
use CssLint\LintConfiguration;
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

    public function testLintValidString()
    {
        $errors = iterator_to_array($this->linter->lintString('.button.dropdown::after {
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
    margin-left: 0; }'));
        $this->assertEmpty($errors);
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
                'key' => 'unclosed_token',
                'message' => 'block - Unclosed block detected',
                'line' => 1,
                'start' => 24,
                'end' => 0,
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
                'message' => 'block - Unclosed block detected',
                'line' => 1,
                'start' => 2,
                'end' => 0,
            ],
        ], $errors);
    }

    public function testLintStringWithWrongTypeParam()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'CssLint\Linter::lintString(): Argument #1 ($stringValue) must be of type string, array given'
        );
        iterator_to_array($this->linter->lintString(['wrong']));
    }

    public function testLintFileWithWrongTypeParam()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'CssLint\Linter::lintFile(): Argument #1 ($filePath) must be of type string, array given'
        );
        iterator_to_array($this->linter->lintFile(['wrong']));
    }

    public function testLintFileWithUnknownFilePathParam()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "$filePath" "wrong" is not an existing file path');
        iterator_to_array($this->linter->lintFile('wrong'));
    }

    public function testLintFileWithUnreadableFilePathParam()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "$filePath" "vfs://testDir/foo.txt" is not a readable file path');

        $testFile = new vfsStreamFile('foo.txt', 0o000);
        $this->root->addChild($testFile);

        $fileToLint = $testFile->url();

        $this->assertFileIsNotReadable($fileToLint);

        iterator_to_array($this->linter->lintFile($fileToLint));
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

    public function testLintBootstrapCssFile()
    {
        // Arrange
        $fileToLint = $this->testFixturesDir . '/bootstrap.css';

        // Act
        $errors = $this->linter->lintFile($fileToLint);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function testLintNormalizeCssFile()
    {
        // Arrange
        $fileToLint = $this->testFixturesDir . '/normalize.css';

        // Act
        $errors = $this->linter->lintFile($fileToLint);

        // Assert
        $this->assertErrorsEquals([], $errors);
    }

    public function testLintTailwindCssFile()
    {
        // Arrange
        $fileToLint = $this->testFixturesDir . '/tailwind.css';

        // Act
        $errors = $this->linter->lintFile($fileToLint);

        // Assert
        $this->assertErrorsEquals([], $errors);
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
                    [
                        'key' => 'unknown_css_property',
                        'message' => 'Unknown CSS property "bordr-top-style" (line: 8, char: 20)',
                        'line' => 8,
                        'start' => 20,
                        'end' => 28,
                    ],
                ],
                [
                    'key' => 'unterminated_selector_content',
                    'message' => 'Unterminated "selector content" (line: 17, char: 0)',
                    'line' => 17,
                    'start' => 0,
                    'end' => 0,
                ],
            ],
            $errors
        );
    }
}
