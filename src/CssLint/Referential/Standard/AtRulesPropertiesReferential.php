<?php

declare(strict_types=1);

namespace CssLint\Referential\Standard;

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
        'counter-style' =>
        [
            'additive-symbols' => true,
            'fallback' => true,
            'negative' => true,
            'pad' => true,
            'prefix' => true,
            'range' => true,
            'speak-as' => true,
            'suffix' => true,
            'symbols' => true,
            'system' => true,
        ],
        'font-face' =>
        [
            'ascent-override' => true,
            'descent-override' => true,
            'font-display' => true,
            'font-family' => true,
            'font-feature-settings' => true,
            'font-stretch' => true,
            'font-style' => true,
            'font-variation-settings' => true,
            'font-weight' => true,
            'font-width' => true,
            'line-gap-override' => true,
            'size-adjust' => true,
            'src' => true,
            'unicode-range' => true,
        ],
        'font-feature-values' =>
        [
            'annotation' => true,
            'character-variant' => true,
            'historical-forms' => true,
            'ornaments' => true,
            'styleset' => true,
            'stylistic' => true,
            'swash' => true,
        ],
        'font-palette-values' =>
        [
            'base-palette' => true,
            'font-family' => true,
            'override-colors' => true,
        ],
        'import' =>
        [
            'layer' => true,
            'supports' => true,
        ],
        'media' =>
        [
            '-webkit-device-pixel-ratio' => true,
            '-webkit-max-device-pixel-ratio' => true,
            '-webkit-min-device-pixel-ratio' => true,
            '-webkit-transform-3d' => true,
            'any-hover' => true,
            'any-pointer' => true,
            'aspect-ratio' => true,
            'calc' => true,
            'color' => true,
            'color-gamut' => true,
            'color-index' => true,
            'device-aspect-ratio' => true,
            'device-height' => true,
            'device-posture' => true,
            'device-width' => true,
            'display-mode' => true,
            'dynamic-range' => true,
            'forced-colors' => true,
            'grid' => true,
            'height' => true,
            'horizontal-viewport-segments' => true,
            'hover' => true,
            'inverted-colors' => true,
            'monochrome' => true,
            'nested-queries' => true,
            'orientation' => true,
            'overflow-block' => true,
            'overflow-inline' => true,
            'pointer' => true,
            'prefers-color-scheme' => true,
            'prefers-contrast' => true,
            'prefers-reduced-data' => true,
            'prefers-reduced-motion' => true,
            'prefers-reduced-transparency' => true,
            'resolution' => true,
            'scripting' => true,
            'update' => true,
            'vertical-viewport-segments' => true,
            'video-dynamic-range' => true,
            'width' => true,
        ],
        'page' =>
        [
            'bottom-center' => true,
            'bottom-left' => true,
            'bottom-left-corner' => true,
            'bottom-right' => true,
            'bottom-right-corner' => true,
            'left-bottom' => true,
            'left-middle' => true,
            'left-top' => true,
            'page-orientation' => true,
            'right-bottom' => true,
            'right-middle' => true,
            'right-top' => true,
            'size' => true,
            'top-center' => true,
            'top-left' => true,
            'top-left-corner' => true,
            'top-right' => true,
            'top-right-corner' => true,
        ],
        'property' =>
        [
            'inherits' => true,
            'initial-value' => true,
            'syntax' => true,
        ],
        'supports' =>
        [
            'font-format' => true,
            'font-tech' => true,
            'selector' => true,
        ],
    ];
}
