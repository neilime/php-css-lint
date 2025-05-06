<?php

declare(strict_types=1);

namespace CssLint\Referential;

/**
 * @phpstan-type Referential array<string, bool>
 */
interface PropertiesReferential
{
    /**
     * @return Referential
     */
    public static function getReferential(): array;
}
