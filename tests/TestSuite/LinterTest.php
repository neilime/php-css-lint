<?php

namespace TestSuite;

class LinterTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \CssLint\Linter
     */
    protected $linter;

    protected function setUp(): void
    {
        $this->linter = new \CssLint\Linter();
    }

    public function testConstructWithCustomCssLintProperties()
    {
        $oProperties = new \CssLint\Properties();
        $oLinter = new \CssLint\Linter($oProperties);
        $this->assertSame($oProperties, $oLinter->getCssLintProperties());
    }

    public function testLintValidString()
    {
        $this->assertTrue($this->linter->lintString('.button.dropdown::after {
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
    }

    public function testLintNotValidString()
    {
        $this->assertFalse($this->linter->lintString('.button.dropdown::after {
             displady: block;
    width: 0;
    :
            '));
        $this->assertSame([
            'Unknown CSS property "displady" (line: 2, char: 22)',
            'Unexpected char ":" (line: 4, char: 5)',
        ], $this->linter->getErrors());
    }

    public function testLintStringWithUnterminatedContext()
    {
        $this->assertFalse($this->linter->lintString('* {'));
        $this->assertSame([
            'Unterminated "selector content" (line: 1, char: 3)'
        ], $this->linter->getErrors());
    }

    public function testLintStringWithWrongSelectorDoubleComma()
    {
        $this->assertFalse($this->linter->lintString('a,, {}'));
        $this->assertSame([
            'Selector token "," cannot be preceded by "a," (line: 1, char: 3)'
        ], $this->linter->getErrors());
    }

    public function testLintStringWithWrongSelectorDoubleHash()
    {
        $this->assertFalse($this->linter->lintString('## {}'));
        $this->assertSame([
            'Selector token "#" cannot be preceded by "#" (line: 1, char: 2)'
        ], $this->linter->getErrors());
    }

    public function testLintStringWithWrongPropertyNameUnexpectedToken()
    {
        $this->assertFalse($this->linter->lintString('.test {
     test~: true;
}'));
        $this->assertSame([
            'Unexpected property name token "~" (line: 2, char: 10)',
            'Unknown CSS property "test~" (line: 2, char: 11)'
        ], $this->linter->getErrors());
    }

    public function testLintStringWithWrongSelectorUnexpectedToken()
    {
        $this->assertFalse($this->linter->lintString('.a| {}'));
        $this->assertSame([
            'Unexpected selector token "|" (line: 1, char: 3)'
        ], $this->linter->getErrors());
    }

    public function testLintStringWithWrongTypeParam()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to CssLint\Linter::lintString() must be of the type string, array given'
        );

        $this->linter->lintString(['wrong']);
        $this->assertFalse(false);
    }

    public function testLintFileWithWrongTypeParam()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to CssLint\Linter::lintFile() must be of the type string, array given'
        );
        $this->linter->lintFile(['wrong']);
    }

    public function testLintFileWithUnknownFilePathParam()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "$sFilePath" "wrong" is not an existing file path');
        $this->linter->lintFile('wrong');
    }

    public function testLintBootstrapCssFile()
    {
        $this->assertTrue(
            $this->linter->lintFile(__DIR__ .  '/../_files/bootstrap.css'),
            print_r($this->linter->getErrors(), true)
        );
    }

    public function testLintFoundationCssFile()
    {
        $this->assertTrue(
            $this->linter->lintFile(__DIR__ .  '/../_files/foundation.css'),
            print_r($this->linter->getErrors(), true)
        );
    }

    public function testLintNotValidCssFile()
    {
        $this->assertFalse($this->linter->lintFile(__DIR__ .  '/../_files/not_valid.css'));
        $this->assertSame([
            'Unknown CSS property "bordr-top-style" (line: 8, char: 20)',
            'Unterminated "selector content" (line: 17, char: 0)'
        ], $this->linter->getErrors());
    }
}
