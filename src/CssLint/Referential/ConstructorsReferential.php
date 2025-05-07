<?php

declare(strict_types=1);

namespace CssLint\Referential;

/**
 * @phpstan-import-type ReferentialData from Referential
 */
class ConstructorsReferential extends AbstractReferential
{
    /**
     * @var ReferentialData
     */
    public static array $referential = [
        'ms' => true,
        'moz' => true,
        'webkit' => true,
        'o' => true,
    ];
}
