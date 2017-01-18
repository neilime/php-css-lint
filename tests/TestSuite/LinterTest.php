<?php

namespace TestSuite;

class LinterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \CssLint\Linter
     */
    protected $linter;

    public function setUp()
    {
        $this->linter = new \CssLint\Linter();
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
        $this->assertSame(array(
            'Unknown CSS property "displady" (line: 2, char: 22)',
            'Unexpected char ":" (line: 4, char: 5)',
                ), $this->linter->getErrors());
    }

    public function testLintStringWithUnterminatedContext()
    {
        $this->assertFalse($this->linter->lintString('* {'));
        $this->assertSame(array(
            'Unterminated "selector content" (line: 1, char: 3)'
                ), $this->linter->getErrors());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument "$sString" expects a string, "boolean" given
     */
    public function testLintStringWithWrongTypeParam()
    {
        $this->linter->lintString(false);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument "$sFilePath" expects a string, "boolean" given
     */
    public function testLintFileWithWrongTypeParam()
    {
        $this->linter->lintFile(false);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument "$sFilePath" "wrong" is not an existing file path
     */
    public function testLintFileWithUnkownFilePathParam()
    {
        $this->linter->lintFile('wrong');
    }

    public function testLintBootstrapCssFile()
    {
        $this->assertTrue($this->linter->lintFile(getcwd() . DIRECTORY_SEPARATOR . '_files/bootstrap.css'), print_r($this->linter->getErrors(), true));
    }

    public function testLintFoundationCssFile()
    {
        $this->assertTrue($this->linter->lintFile(getcwd() . DIRECTORY_SEPARATOR . '_files/foundation.css'), print_r($this->linter->getErrors(), true));
    }

    public function testLintNotValidCssFile()
    {
        $this->assertFalse($this->linter->lintFile(getcwd() . DIRECTORY_SEPARATOR . '_files/not_valid.css'));
        $this->assertSame(array(
            'Unknown CSS property "bordr-top-style" (line: 8, char: 20)',
            'Unterminated "selector content" (line: 17, char: 0)'
                ), $this->linter->getErrors());
    }
}
