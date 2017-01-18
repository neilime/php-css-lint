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
            'Unexpected char ":" (line: 15, char: 5)'
                ), $this->linter->getErrors());
    }

    public function testLintString()
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
}
