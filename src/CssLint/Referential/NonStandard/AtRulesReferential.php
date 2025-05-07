<?php

declare(strict_types=1);

namespace CssLint\Referential\NonStandard;

use CssLint\Referential\Referential;
use CssLint\Referential\AbstractReferential;

/**
 * @phpstan-import-type ReferentialData from Referential
 */
class AtRulesReferential extends AbstractReferential
{
    /**
     * @var ReferentialData
     */
    public static array $referential = [
        'document' => true,
        'tailwind' => true,
        'theme' => true,
    ];
}
