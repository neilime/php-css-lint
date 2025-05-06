<?php

declare(strict_types=1);

namespace CssLint\Referential;

/**
 * @phpstan-import-type Referential from PropertiesReferential
 */
class ConstructorsReferential implements PropertiesReferential
{
    /**
     * @var Referential
     */
    public static array $referential = [
        'ms' => true,
        'moz' => true,
        'webkit' => true,
        'o' => true,
    ];

    /**
     * Get the dataset of constructors.
     *
     * @return Referential
     */
    public static function getReferential(): array
    {
        return self::$referential;
    }
}
