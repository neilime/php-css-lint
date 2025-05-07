<?php

declare(strict_types=1);

namespace CssLint\Referential;

/**
 * @phpstan-import-type ReferentialData from Referential
 */
abstract class AbstractReferential implements Referential
{
    /**
     * @var ReferentialData
     */
    public static array $referential;

    /**
     * Get the referential dataset.
     *
     * @return ReferentialData
     */
    public static function getReferential(): array
    {
        return static::$referential;
    }
}
