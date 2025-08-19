<?php

namespace Tests\TestSuite;

use CssLint\Cli;
use PHPUnit\Framework\TestCase;

class CliTest extends TestCase
{
    private string $tempDir;
    private string $testFixturesDir;

    /**
     * @var Cli
     */
    private $cli;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testFixturesDir =  realpath(__DIR__ . '/../fixtures');
        $this->tempDir = sys_get_temp_dir();

        $this->cli = new Cli();
    }

    protected function tearDown(): void
    {
        // Clean up any test files
        $pattern = $this->tempDir . '/test_file_output_*.txt';
        foreach (glob($pattern) as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }


    public function testRunWithoutArgumentMustReturnsErrorCode()
    {
        $this->expectOutputRegex(
            '/Usage:.*/'
        );
        $this->assertEquals(0, $this->cli->run([]));
    }

    public function testRunWithValidStringShouldReturnSuccessCode()
    {
        $this->expectOutputString(
            '# Lint CSS string...' . PHP_EOL
                . "\033[32m => Success: CSS string is valid.\033[0m" . PHP_EOL
                . PHP_EOL
        );
        $this->assertEquals(
            0,
            $this->cli->run(['php-css-lint', '.test { display: block; }']),
            $this->getActualOutput()
        );
    }

    public function testRunWithNotValidStringShouldReturnErrorCode()
    {
        $this->expectOutputString(
            '# Lint CSS string...' . PHP_EOL
                . "\033[31m    - [unexpected_character_in_block_content]: block - Unexpected character: \":\" (line 1, column 6 to line 3, column 16)\033[0m" . PHP_EOL
                . "\033[31m    - [invalid_property_declaration]: property - Unknown property \"displady\" (line 1, column 7 to line 1, column 23)\033[0m" . PHP_EOL
                . "\033[31m => Failure: CSS string is invalid CSS.\033[0m" . PHP_EOL
        );

        $this->assertEquals(1, $this->cli->run([
            'php-css-lint',
            '.test { displady: block;
            width: 0;
            : }',
        ]));
    }

    public function testRunWithNotValidFileShouldReturnErrorCode()
    {
        $fileToLint = $this->testFixturesDir . '/not_valid.css';

        $this->expectOutputString(
            "# Lint CSS file \"$fileToLint\"..." . PHP_EOL
                . "\033[31m    - [invalid_property_declaration]: property - Unknown property \"bordr-top-style\" (line 3, column 5 to line 3, column 27)\033[0m" . PHP_EOL
                . "\033[31m    - [unclosed_token]: block - Unclosed \"block\" detected (line 1, column 23 to line 6, column 2)\033[0m" . PHP_EOL
                . "\033[31m => Failure: CSS file \"$fileToLint\" is invalid CSS.\033[0m" . PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run(['php-css-lint', $fileToLint]));
    }

    public function testRunWithGlobShouldReturnSuccessCode()
    {
        $fileToLint = $this->testFixturesDir . '/valid.css';
        $this->expectOutputString(
            "# Lint CSS file \"$fileToLint\"..." . PHP_EOL
                . "\033[32m => Success: CSS file \"$fileToLint\" is valid.\033[0m" . PHP_EOL
                . PHP_EOL
        );
        $this->assertEquals(0, $this->cli->run(['php-css-lint', $this->testFixturesDir . '/valid*.css']), $this->getActualOutput());
    }

    public function testRunWithNoFilesGlobShouldReturnErrorCode()
    {
        $filesToLint = $this->testFixturesDir . '/unknown*.css';

        $this->expectOutputString(
            "\033[31m/!\ Error: $filesToLint - No files found for given glob pattern\033[0m" . PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run(['php-css-lint',  $filesToLint]));
    }

    public function testRunWithNotValidFileGlobShouldReturnErrorCode()
    {
        $fileToLint = $this->testFixturesDir . '/not_valid.css';
        $this->expectOutputString(
            "# Lint CSS file \"$fileToLint\"..." . PHP_EOL
                . "\033[31m    - [invalid_property_declaration]: property - Unknown property \"bordr-top-style\" (line 3, column 5 to line 3, column 27)\033[0m" . PHP_EOL
                . "\033[31m    - [unclosed_token]: block - Unclosed \"block\" detected (line 1, column 23 to line 6, column 2)\033[0m" . PHP_EOL
                . "\033[31m => Failure: CSS file \"$fileToLint\" is invalid CSS.\033[0m" . PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run(['php-css-lint', $this->testFixturesDir . '/not_valid*.css']));
    }

    public function testRunWithOptionsMustBeUsedByTheLinter()
    {
        $this->expectOutputString(
            "# Lint CSS string..." . PHP_EOL
                . "\033[31m    - [invalid_indentation_character]: whitespace - Unexpected char \" \" (line 2, column 1 to line 2, column 2)\033[0m" . PHP_EOL
                . "\033[31m => Failure: CSS string is invalid CSS.\033[0m" . PHP_EOL
        );

        $this->assertEquals(1, $this->cli->run([
            'php-css-lint',
            '--options={ "allowedIndentationChars": ["\t"] }',
            ".test {\n display: block; }",
        ]));
    }

    public function unvalidOptionsProvider()
    {
        return [
            'invalid json' => ['{ "allowedIndentationChars":  }', 'Unable to parse option argument: Syntax error'],
            'empty options' => ['[]', 'Unable to parse empty option argument'],
            'non array options' => ['true', 'Unable to parse option argument: must be a json object'],
            'not allowed option' => ['{ "unknownOption": true }', 'Invalid option key: "unknownOption"'],
            'invalid option "allowedIndentationChars" value' => ['{ "allowedIndentationChars": "invalid" }', 'Option "allowedIndentationChars" must be an array'],
        ];
    }

    /**
     * @dataProvider unvalidOptionsProvider
     */
    public function testRunWithInvalidOptionsFormatShouldReturnAnError(string $options, string $expectedOutput)
    {
        $this->expectOutputString(
            "\033[31m/!\ Error: $expectedOutput\033[0m" . PHP_EOL
        );

        $this->assertEquals(1, $this->cli->run([
            'php-css-lint',
            '--options=' . $options,
            '.test { display: block; }',
        ]));
    }

    public function testRunWithFormatterArgumentShouldReturnSuccessCode()
    {
        $fileToLint = $this->testFixturesDir . '/valid.css';
        $this->expectOutputString(
            "::group::Lint CSS file \"$fileToLint\"" . PHP_EOL
                . "::notice ::Success: CSS file \"$fileToLint\" is valid." . PHP_EOL
                . "::endgroup::" . PHP_EOL
        );
        $this->assertEquals(0, $this->cli->run(['php-css-lint', '--formatter=github-actions', $fileToLint]), $this->getActualOutput());
    }

    public function testRunWithFormatterAndPathArgumentShouldReturnSuccessCode()
    {
        $fileToLint = $this->testFixturesDir . '/valid.css';
        $outputFile = $this->tempDir . '/test_file_output_' . uniqid() . '.txt';

        $this->expectOutputString("");
        $this->assertEquals(0, $this->cli->run(['php-css-lint', '--formatter=gitlab-ci:' . $outputFile, $fileToLint]));

        $this->assertFileExists($outputFile);
        $this->assertStringContainsString("[]", file_get_contents($outputFile));
    }

    public function validCssFilesProvider(): array
    {
        return [
            'bootstrap.css' => ['bootstrap.css'],
            'normalize.css' => ['normalize.css'],
            'tailwind.css' => ['tailwind.css'],
        ];
    }

    /**
     * @dataProvider validCssFilesProvider
     */
    public function testRunWithValidFileShouldReturnSuccessCode(string $fileToLint)
    {
        $fileToLint = $this->testFixturesDir . '/' . $fileToLint;
        $this->expectOutputString(
            "# Lint CSS file \"$fileToLint\"..." . PHP_EOL
                . "\033[32m => Success: CSS file \"$fileToLint\" is valid.\033[0m" . PHP_EOL
                . PHP_EOL
        );
        $this->assertEquals(0, $this->cli->run(['php-css-lint', $fileToLint]), $this->getActualOutput());
    }
}
