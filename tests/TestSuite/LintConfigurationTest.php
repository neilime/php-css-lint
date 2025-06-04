<?php

namespace Tests\TestSuite;

use CssLint\LintConfiguration;
use PHPUnit\Framework\TestCase;

class LintConfigurationTest extends TestCase
{
    public function testShouldReturnTrueWhenGivenStandardPropertyExists()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->propertyExists('align-content'));
    }

    public function testShouldReturnTrueWhenGivenConstructorStandardPropertyExists()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->propertyExists('-moz-align-content'));
    }

    public function testShouldReturnTrueWhenGivenConstructorNonStandardPropertyExists()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->propertyExists('-moz-animation-trigger'));
    }

    public function testShouldReturnTrueWhenGivenPropertyDoesNotExist()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->propertyExists('-wrong-animation-trigger'));
    }

    public function testGetAllowedIndentationChars()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertEquals([" "], $lintConfiguration->getAllowedIndentationChars());
    }

    public function testSetAllowedIndentationChars()
    {
        $lintConfiguration = new LintConfiguration();
        $aAllowedIndentationChars = ["\t"];
        $lintConfiguration->setAllowedIndentationChars($aAllowedIndentationChars);
        $this->assertEquals($aAllowedIndentationChars, $lintConfiguration->getAllowedIndentationChars());
    }

    public function testShouldReturnTrueWhenGivenCharIsAnAllowedIndentationChar()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->isAllowedIndentationChar(" "));
    }

    public function testShouldReturnTrueWhenGivenCharIsNotAnAllowedIndentationChar()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->isAllowedIndentationChar("\t"));
    }

    public function testMergeConstructorsShouldDisableAContructor()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->propertyExists('-moz-animation-trigger'));

        $lintConfiguration->mergeConstructors(['moz' => false]);
        $this->assertFalse($lintConfiguration->propertyExists('-moz-animation-trigger'));
    }

    public function testMergeConstructorsShouldAddAContructor()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->propertyExists('-new-animation-trigger'));

        $lintConfiguration->mergeConstructors(['new' => true]);
        $this->assertTrue($lintConfiguration->propertyExists('-new-animation-trigger'));
    }

    public function testMergePropertiesStandardsShouldDisableAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->propertyExists('align-content'));

        $lintConfiguration->mergePropertiesStandards(['align-content' => false]);
        $this->assertFalse($lintConfiguration->propertyExists('align-content'));
    }

    public function testMergePropertiesStandardsShouldAddAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->propertyExists('new-content'));

        $lintConfiguration->mergePropertiesStandards(['new-content' => true]);
        $this->assertTrue($lintConfiguration->propertyExists('new-content'));
    }

    public function testMergePropertiesNonStandardsShouldDisableAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->propertyExists('-moz-animation-trigger'));

        $lintConfiguration->mergePropertiesNonStandards(['animation-trigger' => false]);
        $this->assertFalse($lintConfiguration->propertyExists('-moz-animation-trigger'));
    }

    public function testMergePropertiesNonStandardsShouldAddAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->propertyExists('-moz-new-content'));

        $lintConfiguration->mergePropertiesNonStandards(['new-content' => true]);
        $this->assertTrue($lintConfiguration->propertyExists('-moz-new-content'));
    }

    public function testMergeAtRulesStandardsShouldDisableAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->atRuleExists('charset'));

        $lintConfiguration->mergeAtRulesStandards(['charset' => false]);
        $this->assertFalse($lintConfiguration->atRuleExists('charset'));
    }

    public function testMergeAtRulesStandardsShouldAddAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->atRuleExists('new-at-rule'));

        $lintConfiguration->mergeAtRulesStandards(['new-at-rule' => true]);
        $this->assertTrue($lintConfiguration->atRuleExists('new-at-rule'));
    }

    public function testMergeAtRulesNonStandardsShouldDisableAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->atRuleExists('document'));

        $lintConfiguration->mergeAtRulesNonStandards(['document' => false]);
        $this->assertFalse($lintConfiguration->atRuleExists('document'));
    }

    public function testMergeAtRulesNonStandardsShouldAddAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->atRuleExists('new-at-rule'));

        $lintConfiguration->mergeAtRulesNonStandards(['new-at-rule' => true]);
        $this->assertTrue($lintConfiguration->atRuleExists('new-at-rule'));
    }

    public function testMergeAtRulesPropertiesStandardsShouldDisableAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->atRulePropertyExists('font-face', 'font-display'));

        $lintConfiguration->mergeAtRulesPropertiesStandards(['font-face' => ['font-display' => false]]);
        $this->assertFalse($lintConfiguration->atRulePropertyExists('font-face', 'font-display'));
    }

    public function testMergeAtRulesPropertiesStandardsShouldAddAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->atRulePropertyExists('font-face', 'new-at-rule'));

        $lintConfiguration->mergeAtRulesPropertiesStandards(['font-face' => ['new-at-rule' => true]]);
        $this->assertTrue($lintConfiguration->atRulePropertyExists('font-face', 'new-at-rule'));
    }

    public function testMergeAtRulesPropertiesNonStandardsShouldDisableAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertTrue($lintConfiguration->atRulePropertyExists('font-face', 'font-variant'));

        $lintConfiguration->mergeAtRulesPropertiesNonStandards(['font-face' => ['font-variant' => false]]);
        $this->assertFalse($lintConfiguration->atRulePropertyExists('font-face', 'font-variant'));
    }

    public function testMergeAtRulesPropertiesNonStandardsShouldAddAProperty()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->atRulePropertyExists('font-face', 'new-at-rule'));

        $lintConfiguration->mergeAtRulesPropertiesNonStandards(['font-face' => ['new-at-rule' => true]]);
        $this->assertTrue($lintConfiguration->atRulePropertyExists('font-face', 'new-at-rule'));
    }

    public function testSetOptionsAllowedIndentationChars()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->isAllowedIndentationChar("\t"));

        $lintConfiguration->setOptions([
            'allowedIndentationChars' => ["\t"],
        ]);
        $this->assertTrue($lintConfiguration->isAllowedIndentationChar("\t"));
    }

    public function testSetOptionsConstructors()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->propertyExists('-new-animation-trigger'));

        $lintConfiguration->setOptions([
            'constructors' => ['new' => true],
        ]);
        $this->assertTrue($lintConfiguration->propertyExists('-new-animation-trigger'));
    }

    public function testSetOptionsStandards()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->propertyExists('new-content'));

        $lintConfiguration->setOptions([
            'standards' => ['new-content' => true],
        ]);
        $this->assertTrue($lintConfiguration->propertyExists('new-content'));
    }

    public function testSetOptionsNonStandards()
    {
        $lintConfiguration = new LintConfiguration();
        $this->assertFalse($lintConfiguration->propertyExists('-moz-new-content'));

        $lintConfiguration->setOptions([
            'nonStandards' => ['new-content' => true],
        ]);
        $this->assertTrue($lintConfiguration->propertyExists('-moz-new-content'));
    }
}
