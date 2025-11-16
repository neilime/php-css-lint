<?php

declare(strict_types=1);

namespace CssLint\Referential\Standard;

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
        'charset' => true,
        'container' => true,
        'counter-style' => true,
        'custom-media' => true,
        'font-face' => true,
        'font-feature-values' => true,
        'font-palette-values' => true,
        'function' => true,
        'import' => true,
        'keyframes' => true,
        'layer' => true,
        'media' => true,
        'namespace' => true,
        'page' => true,
        'position-try' => true,
        'property' => true,
        'scope' => true,
        'starting-style' => true,
        'supports' => true,
        'view-transition' => true,
    ];
}
