<?php

declare(strict_types=1);

namespace CssLint\Referential;

/**
 * @phpstan-type ReferentialData array<string, bool|array<string, bool>>
 */
interface Referential
{
    /**
     * @return ReferentialData
     */
    public static function getReferential(): array;
}
