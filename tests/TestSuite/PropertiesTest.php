<?php

namespace TestSuite;

class PropertiesTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnTrueWhenGivenStandardPropertyExists()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertTrue($oProperties->propertyExists('align-content'));
    }

    public function testShouldReturnTrueWhenGivenConstructorStandardPropertyExists()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertTrue($oProperties->propertyExists('-moz-align-content'));
    }

    public function testShouldReturnTrueWhenGivenConstructorNonStandardPropertyExists()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertTrue($oProperties->propertyExists('-moz-font-smoothing'));
    }

    public function testShouldReturnTrueWhenGivenPropertyDoesNotExist()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertFalse($oProperties->propertyExists('-wrong-font-smoothing'));
    }

    public function testGetAllowedIndentationChars()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertEquals([" "], $oProperties->getAllowedIndentationChars());
    }

    public function testSetAllowedIndentationChars()
    {
        $oProperties = new \CssLint\Properties();
        $aAllowedIndentationChars = ["\t"];
        $oProperties->setAllowedIndentationChars($aAllowedIndentationChars);
        $this->assertEquals($aAllowedIndentationChars, $oProperties->getAllowedIndentationChars());
    }

    public function testShouldReturnTrueWhenGivenCharIsAnAllowedIndentationChar()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertTrue($oProperties->isAllowedIndentationChar(" "));
    }

    public function testShouldReturnTrueWhenGivenCharIsNotAnAllowedIndentationChar()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertFalse($oProperties->isAllowedIndentationChar("\t"));
    }

    public function testMergeConstructorsShouldDisableAContructor()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertTrue($oProperties->propertyExists('-moz-font-smoothing'));

        $oProperties->mergeConstructors(['moz' => false]);
        $this->assertFalse($oProperties->propertyExists('-moz-font-smoothing'));
    }

    public function testMergeConstructorsShouldAddAContructor()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertFalse($oProperties->propertyExists('-new-font-smoothing'));

        $oProperties->mergeConstructors(['new' => true]);
        $this->assertTrue($oProperties->propertyExists('-new-font-smoothing'));
    }

    public function testMergeStandardsShouldDisableAContructor()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertTrue($oProperties->propertyExists('align-content'));

        $oProperties->mergeStandards(['align-content' => false]);
        $this->assertFalse($oProperties->propertyExists('align-content'));
    }

    public function testMergeStandardsShouldAddAContructor()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertFalse($oProperties->propertyExists('new-content'));

        $oProperties->mergeStandards(['new-content' => true]);
        $this->assertTrue($oProperties->propertyExists('new-content'));
    }

    public function testMergeNonStandardsShouldDisableAContructor()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertTrue($oProperties->propertyExists('-moz-font-smoothing'));

        $oProperties->mergeNonStandards(['font-smoothing' => false]);
        $this->assertFalse($oProperties->propertyExists('-moz-font-smoothing'));
    }

    public function testMergeNonStandardsShouldAddAContructor()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertFalse($oProperties->propertyExists('-moz-new-content'));

        $oProperties->mergeNonStandards(['new-content' => true]);
        $this->assertTrue($oProperties->propertyExists('-moz-new-content'));
    }

    public function testSetOptionsAllowedIndentationChars()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertFalse($oProperties->isAllowedIndentationChar("\t"));

        $oProperties->setOptions([
            'allowedIndentationChars' => ["\t"]
        ]);
        $this->assertTrue($oProperties->isAllowedIndentationChar("\t"));
    }

    public function testSetOptionsConstructors()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertFalse($oProperties->propertyExists('-new-font-smoothing'));

        $oProperties->setOptions([
            'constructors' => ['new' => true]
        ]);
        $this->assertTrue($oProperties->propertyExists('-new-font-smoothing'));
    }

    public function testSetOptionsStandards()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertFalse($oProperties->propertyExists('new-content'));

        $oProperties->setOptions([
            'standards' => ['new-content' => true]
        ]);
        $this->assertTrue($oProperties->propertyExists('new-content'));
    }

    public function testSetOptionsNonStandards()
    {
        $oProperties = new \CssLint\Properties();
        $this->assertFalse($oProperties->propertyExists('-moz-new-content'));

        $oProperties->setOptions([
            'nonStandards' => ['new-content' => true]
        ]);
        $this->assertTrue($oProperties->propertyExists('-moz-new-content'));
    }
}
