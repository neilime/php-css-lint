<?php

declare(strict_types=1);

namespace CssLint\Referential\NonStandard;

use CssLint\Referential\Referential;
use CssLint\Referential\AbstractReferential;

/**
 * @phpstan-import-type ReferentialData from Referential
 */
class AtRulesPropertiesReferential extends AbstractReferential
{
    /**
     * @var ReferentialData
     */
    public static array $referential = [
        'font-face'
        => [
            'font-variant' => true,
        ],
        'media'
        => [
            '-moz-device-pixel-ratio' => true,
            '-webkit-animation' => true,
            '-webkit-transform-2d' => true,
            '-webkit-transition' => true,
        ],
    ];
}
