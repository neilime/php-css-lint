<?php

declare(strict_types=1);

namespace CssLint;

use CssLint\Referential\ConstructorsReferential;
use CssLint\Referential\NonStandard\PropertiesReferential as NonStandardPropertiesReferential;
use CssLint\Referential\Standard\PropertiesReferential as StandardPropertiesReferential;
use CssLint\Referential\NonStandard\AtRulesReferential as NonStandardAtRulesReferential;
use CssLint\Referential\Standard\AtRulesReferential as StandardAtRulesReferential;
use CssLint\Referential\Referential;

/**
 * @phpstan-import-type ReferentialData from Referential
 * @phpstan-type AllowedIndentationChars array<string>
 * @phpstan-type PropertiesOptions array{
 *  allowedIndentationChars?: AllowedIndentationChars,
 *  constructors?: ReferentialData,
 *  standards?: ReferentialData,
 *  nonStandards?: ReferentialData
 * }
 */
class LintConfiguration
{
    /**
     * List of existing constructor prefix
     * @var ReferentialData
     */
    protected array $constructors;

    /**
     * List of standards properties
     * @var ReferentialData
     */
    protected array $propertiesStandards;

    /**
     * List of non standards properties
     * @var ReferentialData
     */
    protected array $propertiesNonStandards;

    /**
     * List of standards at-rules
     * @var ReferentialData
     */
    protected array $atRulesStandards;

    /**
     * List of non standards at-rules
     * @var ReferentialData
     */
    protected array $atRulesNonStandards;

    /**
     * List of allowed indentation chars
     * @var AllowedIndentationChars
     */
    protected $allowedIndentationChars = [' '];

    public function __construct()
    {
        $this->constructors = ConstructorsReferential::getReferential();
        $this->propertiesStandards = StandardPropertiesReferential::getReferential();
        $this->propertiesNonStandards = NonStandardPropertiesReferential::getReferential();
        $this->atRulesStandards = StandardAtRulesReferential::getReferential();
        $this->atRulesNonStandards = NonStandardAtRulesReferential::getReferential();
    }

    /**
     * @param PropertiesOptions $options Override default properties
     * "allowedIndentationChars" => [" "] or ["\t"]: will override current property
     * "constructors": ["property" => bool]: will merge with current property
     * "standards": ["property" => bool]: will merge with current property
     * "nonStandards": ["property" => bool]: will merge with current property
     */
    public function setOptions(array $options = []): void
    {
        if (isset($options['allowedIndentationChars'])) {
            $this->setAllowedIndentationChars($options['allowedIndentationChars']);
        }

        if (isset($options['constructors'])) {
            $this->mergeConstructors($options['constructors']);
        }

        if (isset($options['standards'])) {
            $this->mergePropertiesStandards($options['standards']);
        }

        if (isset($options['nonStandards'])) {
            $this->mergePropertiesNonStandards($options['nonStandards']);
        }
    }

    /**
     * Checks that the given CSS property is an existing one
     * @param string $property the property to check
     * @return boolean true if the property exists, else returns false
     */
    public function propertyExists(string $property): bool
    {
        if (!empty($this->propertiesStandards[$property])) {
            return true;
        }

        if (!empty($this->propertiesNonStandards[$property])) {
            return true;
        }

        $allowedConstrutors = array_keys(array_filter($this->constructors));

        foreach ($allowedConstrutors as $allowedConstrutor) {
            $propertyWithoutConstructor = preg_replace(
                '/^(-' . preg_quote($allowedConstrutor) . '-)/',
                '',
                $property
            );

            if ($propertyWithoutConstructor !== $property) {
                if (!empty($this->propertiesStandards[$propertyWithoutConstructor])) {
                    return true;
                }

                if (!empty($this->propertiesNonStandards[$propertyWithoutConstructor])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function atRuleExists(string $atRule): bool
    {
        if (!empty($this->atRulesStandards[$atRule])) {
            return true;
        }

        return !empty($this->atRulesNonStandards[$atRule]);
    }

    /**
     * Retrieve indentation chars allowed by the linter
     * @return AllowedIndentationChars a list of allowed indentation chars
     */
    public function getAllowedIndentationChars(): array
    {
        return $this->allowedIndentationChars;
    }

    /**
     * Define the indentation chars allowed by the linter
     * @param AllowedIndentationChars $allowedIndentationChars a list of allowed indentation chars
     */
    public function setAllowedIndentationChars(array $allowedIndentationChars): void
    {
        $this->allowedIndentationChars = $allowedIndentationChars;
    }

    /**
     * Check if the given char is allowed as an indentation char
     * @param string $charValue the character to be checked
     * @return bool according to whether the character is allowed or not
     */
    public function isAllowedIndentationChar(string $charValue): bool
    {
        return in_array($charValue, $this->allowedIndentationChars, true);
    }

    /**
     * Merge the given constructors properties with the current ones
     * @param ReferentialData $constructors the constructors properties to be merged
     */
    public function mergeConstructors(array $constructors): void
    {
        $this->constructors = array_merge($this->constructors, $constructors);
    }

    /**
     * Merge the given standards properties with the current ones
     * @param ReferentialData $standards the standards properties to be merged
     */
    public function mergePropertiesStandards(array $standards): void
    {
        $this->propertiesStandards = array_merge($this->propertiesStandards, $standards);
    }

    /**
     * Merge the given non standards properties with the current ones
     * @param ReferentialData $nonStandards non the standards properties to be merged
     */
    public function mergePropertiesNonStandards(array $nonStandards): void
    {
        $this->propertiesNonStandards = array_merge($this->propertiesNonStandards, $nonStandards);
    }

    /**
     * Merge the given standards at-rules with the current ones
     * @param ReferentialData $standards the standards at-rules to be merged
     */
    public function mergeAtRulesStandards(array $standards): void
    {
        $this->atRulesStandards = array_merge($this->atRulesStandards, $standards);
    }

    /**
     * Merge the given non standards atrules with the current ones
     * @param ReferentialData $nonStandards non the standards atrules to be merged
     */
    public function mergeAtRulesNonStandards(array $nonStandards): void
    {
        $this->atRulesNonStandards = array_merge($this->atRulesNonStandards, $nonStandards);
    }
}
