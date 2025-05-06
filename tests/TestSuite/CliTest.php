<?php

namespace Tests\TestSuite;

use CssLint\Cli;
use PHPUnit\Framework\TestCase;

class CliTest extends TestCase
{
    private $testFixturesDir;

    /**
     * @var Cli
     */
    private $cli;

    protected function setUp(): void
    {
        $this->testFixturesDir =  realpath(__DIR__ . '/../fixtures');

        $this->cli = new Cli();
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
            '# Lint CSS string...' . PHP_EOL .
                "\033[32m => CSS string is valid\033[0m" . PHP_EOL .
                PHP_EOL
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
            '# Lint CSS string...' . PHP_EOL .
                "\033[31m => CSS string is not valid:\033[0m" . PHP_EOL .
                PHP_EOL .
                "\033[31m    - Unknown CSS property \"displady\" (line: 1, char: 17)\033[0m" . PHP_EOL .
                "\033[31m    - Unexpected char \":\" (line: 3, char: 13)\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run(['php-css-lint', '.test { displady: block;
            width: 0;
            : }']));
    }

    public function testRunWithValidFileShouldReturnSuccessCode()
    {
        $fileToLint = $this->testFixturesDir . '/valid.css';
        $this->expectOutputString(
            "# Lint CSS file \"$fileToLint\"..." . PHP_EOL .
                "\033[32m => CSS file \"$fileToLint\" is valid\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(0, $this->cli->run(['php-css-lint', $fileToLint]), $this->getActualOutput());
    }

    public function testRunWithNotValidFileShouldReturnErrorCode()
    {
        $fileToLint = $this->testFixturesDir . '/not_valid.css';

        $this->expectOutputString(
            "# Lint CSS file \"$fileToLint\"..." . PHP_EOL .
                "\033[31m => CSS file \"$fileToLint\" is not valid:\033[0m" . PHP_EOL .
                PHP_EOL .
                "\033[31m    - Unknown CSS property \"bordr-top-style\" (line: 8, char: 20)\033[0m" . PHP_EOL .
                "\033[31m    - Unterminated \"selector content\" (line: 17, char: 0)\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run(['php-css-lint', $fileToLint]));
    }

    public function testRunWithGlobShouldReturnSuccessCode()
    {
        $fileToLint = $this->testFixturesDir . '/valid.css';
        $this->expectOutputString(
            "# Lint CSS file \"$fileToLint\"..." . PHP_EOL .
                "\033[32m => CSS file \"$fileToLint\" is valid\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(0, $this->cli->run(['php-css-lint', $this->testFixturesDir . '/valid*.css']));
    }

    public function testRunWithNoFilesGlobShouldReturnErrorCode()
    {
        $filesToLint = $this->testFixturesDir . '/unknown*.css';

        $this->expectOutputString(
            "\033[31m/!\ Error: No files found for glob \"$filesToLint\"\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run(['php-css-lint',  $filesToLint]));
    }


    public function testRunWithNotValidFileGlobShouldReturnErrorCode()
    {
        $fileToLint = $this->testFixturesDir . '/not_valid.css';
        $this->expectOutputString(
            "# Lint CSS file \"$fileToLint\"..." . PHP_EOL .
                "\033[31m => CSS file \"$fileToLint\" is not valid:\033[0m" . PHP_EOL .
                PHP_EOL .
                "\033[31m    - Unknown CSS property \"bordr-top-style\" (line: 8, char: 20)\033[0m" . PHP_EOL .
                "\033[31m    - Unterminated \"selector content\" (line: 17, char: 0)\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run(['php-css-lint', $this->testFixturesDir . '/not_valid*.css']));
    }

    public function testRunWithOptionsMustBeUsedByTheLinter()
    {
        $this->expectOutputString(
            "# Lint CSS string..." . PHP_EOL .
                "\033[31m => CSS string is not valid:\033[0m" . PHP_EOL .
                PHP_EOL .
                "\033[31m    - Unexpected char \" \" (line: 1, char: 8)\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run([
            'php-css-lint',
            '--options={ "allowedIndentationChars": ["\t"] }',
            '.test { display: block; }',
        ]));
    }

    public function testRunWithInvalidOptionsFormatShouldReturnAnError()
    {
        $this->expectOutputString(
            "\033[31m/!\ Error: Unable to parse option argument: Syntax error\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run([
            'php-css-lint',
            '--options={ "allowedIndentationChars":  }',
            '.test { display: block; }',
        ]));
    }
}
