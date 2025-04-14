<?php

namespace TestSuite;

use CssLint\Properties;
use PHPUnit\Framework\TestCase;

class PropertiesTest extends TestCase
{
    public function testShouldReturnTrueWhenGivenStandardPropertyExists()
    {
        $properties = new Properties();
        $this->assertTrue($properties->propertyExists('align-content'));
    }

    public function testShouldReturnTrueWhenGivenConstructorStandardPropertyExists()
    {
        $properties = new Properties();
        $this->assertTrue($properties->propertyExists('-moz-align-content'));
    }

    public function testShouldReturnTrueWhenGivenConstructorNonStandardPropertyExists()
    {
        $properties = new Properties();
        $this->assertTrue($properties->propertyExists('-moz-font-smoothing'));
    }

    public function testShouldReturnTrueWhenGivenPropertyDoesNotExist()
    {
        $properties = new Properties();
        $this->assertFalse($properties->propertyExists('-wrong-font-smoothing'));
    }

    public function testGetAllowedIndentationChars()
    {
        $properties = new Properties();
        $this->assertEquals([" "], $properties->getAllowedIndentationChars());
    }

    public function testSetAllowedIndentationChars()
    {
        $properties = new Properties();
        $aAllowedIndentationChars = ["\t"];
        $properties->setAllowedIndentationChars($aAllowedIndentationChars);
        $this->assertEquals($aAllowedIndentationChars, $properties->getAllowedIndentationChars());
    }

    public function testShouldReturnTrueWhenGivenCharIsAnAllowedIndentationChar()
    {
        $properties = new Properties();
        $this->assertTrue($properties->isAllowedIndentationChar(" "));
    }

    public function testShouldReturnTrueWhenGivenCharIsNotAnAllowedIndentationChar()
    {
        $properties = new Properties();
        $this->assertFalse($properties->isAllowedIndentationChar("\t"));
    }

    public function testMergeConstructorsShouldDisableAContructor()
    {
        $properties = new Properties();
        $this->assertTrue($properties->propertyExists('-moz-font-smoothing'));

        $properties->mergeConstructors(['moz' => false]);
        $this->assertFalse($properties->propertyExists('-moz-font-smoothing'));
    }

    public function testMergeConstructorsShouldAddAContructor()
    {
        $properties = new Properties();
        $this->assertFalse($properties->propertyExists('-new-font-smoothing'));

        $properties->mergeConstructors(['new' => true]);
        $this->assertTrue($properties->propertyExists('-new-font-smoothing'));
    }

    public function testMergeStandardsShouldDisableAContructor()
    {
        $properties = new Properties();
        $this->assertTrue($properties->propertyExists('align-content'));

        $properties->mergeStandards(['align-content' => false]);
        $this->assertFalse($properties->propertyExists('align-content'));
    }

    public function testMergeStandardsShouldAddAContructor()
    {
        $properties = new Properties();
        $this->assertFalse($properties->propertyExists('new-content'));

        $properties->mergeStandards(['new-content' => true]);
        $this->assertTrue($properties->propertyExists('new-content'));
    }

    public function testMergeNonStandardsShouldDisableAContructor()
    {
        $properties = new Properties();
        $this->assertTrue($properties->propertyExists('-moz-font-smoothing'));

        $properties->mergeNonStandards(['font-smoothing' => false]);
        $this->assertFalse($properties->propertyExists('-moz-font-smoothing'));
    }

    public function testMergeNonStandardsShouldAddAContructor()
    {
        $properties = new Properties();
        $this->assertFalse($properties->propertyExists('-moz-new-content'));

        $properties->mergeNonStandards(['new-content' => true]);
        $this->assertTrue($properties->propertyExists('-moz-new-content'));
    }

    public function testSetOptionsAllowedIndentationChars()
    {
        $properties = new Properties();
        $this->assertFalse($properties->isAllowedIndentationChar("\t"));

        $properties->setOptions([
            'allowedIndentationChars' => ["\t"],
        ]);
        $this->assertTrue($properties->isAllowedIndentationChar("\t"));
    }

    public function testSetOptionsConstructors()
    {
        $properties = new Properties();
        $this->assertFalse($properties->propertyExists('-new-font-smoothing'));

        $properties->setOptions([
            'constructors' => ['new' => true],
        ]);
        $this->assertTrue($properties->propertyExists('-new-font-smoothing'));
    }

    public function testSetOptionsStandards()
    {
        $properties = new Properties();
        $this->assertFalse($properties->propertyExists('new-content'));

        $properties->setOptions([
            'standards' => ['new-content' => true],
        ]);
        $this->assertTrue($properties->propertyExists('new-content'));
    }

    public function testSetOptionsNonStandards()
    {
        $properties = new Properties();
        $this->assertFalse($properties->propertyExists('-moz-new-content'));

        $properties->setOptions([
            'nonStandards' => ['new-content' => true],
        ]);
        $this->assertTrue($properties->propertyExists('-moz-new-content'));
    }
}
