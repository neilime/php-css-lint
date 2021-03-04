<?php

namespace TestSuite;

class CliTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \CssLint\Cli
     */
    protected $cli;

    /**
     * @var string
     */
    protected $phpVersion;

    protected function setUp(): void
    {
        $this->cli = new \CssLint\Cli();
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
        $this->assertEquals(0, $this->cli->run(['php-css-lint', '.test { display: block; }']));
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
        $sFileToLint = realpath(__DIR__ .  '/../_files/valid.css');
        $this->expectOutputString(
            "# Lint CSS file \"$sFileToLint\"..." . PHP_EOL .
                "\033[32m => CSS file \"$sFileToLint\" is valid\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(0, $this->cli->run(['php-css-lint', $sFileToLint]));
    }

    public function testRunWithNotValidFileShouldReturnErrorCode()
    {
        $sFileToLint = realpath(__DIR__ .  '/../_files/not_valid.css');

        $this->expectOutputString(
            "# Lint CSS file \"$sFileToLint\"..." . PHP_EOL .
                "\033[31m => CSS file \"$sFileToLint\" is not valid:\033[0m" . PHP_EOL .
                PHP_EOL .
                "\033[31m    - Unknown CSS property \"bordr-top-style\" (line: 8, char: 20)\033[0m" . PHP_EOL .
                "\033[31m    - Unterminated \"selector content\" (line: 17, char: 0)\033[0m" . PHP_EOL .
                PHP_EOL
        );
        $this->assertEquals(1, $this->cli->run(['php-css-lint', $sFileToLint]));
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
            '.test { display: block; }'
        ]));
    }
}
