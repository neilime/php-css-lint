<?php

declare(strict_types=1);

namespace CssLint;

use CssLint\Referential\ConstructorsReferential;
use CssLint\Referential\NonStandard\PropertiesReferential as NonStandardPropertiesReferential;
use CssLint\Referential\Standard\PropertiesReferential as StandardPropertiesReferential;
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
class Properties
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
    protected array $standards;

    /**
     * List of non standards properties
     * @var ReferentialData
     */
    protected array $nonStandards;

    /**
     * List of allowed indentation chars
     * @var AllowedIndentationChars
     */
    protected $allowedIndentationChars = [' '];

    public function __construct()
    {
        $this->constructors = ConstructorsReferential::getReferential();
        $this->standards = StandardPropertiesReferential::getReferential();
        $this->nonStandards = NonStandardPropertiesReferential::getReferential();
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
            $this->mergeStandards($options['standards']);
        }

        if (isset($options['nonStandards'])) {
            $this->mergeNonStandards($options['nonStandards']);
        }
    }

    /**
     * Checks that the given CSS property is an existing one
     * @param string $property the property to check
     * @return boolean true if the property exists, else returns false
     */
    public function propertyExists(string $property): bool
    {
        if (!empty($this->standards[$property])) {
            return true;
        }

        if (!empty($this->nonStandards[$property])) {
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
                if (!empty($this->standards[$propertyWithoutConstructor])) {
                    return true;
                }

                if (!empty($this->nonStandards[$propertyWithoutConstructor])) {
                    return true;
                }
            }
        }

        return false;
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
    public function mergeStandards(array $standards): void
    {
        $this->standards = array_merge($this->standards, $standards);
    }

    /**
     * Merge the given non standards properties with the current ones
     * @param ReferentialData $nonStandards non the standards properties to be merged
     */
    public function mergeNonStandards(array $nonStandards): void
    {
        $this->nonStandards = array_merge($this->nonStandards, $nonStandards);
    }
}
