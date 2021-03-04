<?php

namespace CssLint;

class Properties
{

    /**
     * List of existing constructor prefix
     * @var array
     */
    protected $constructors = [
        'ms' => true,
        'moz' => true,
        'webkit' => true,
        'o' => true,
    ];

    /**
     * List of existing css properties
     * https://www.w3.org/Style/CSS/all-properties.en.html
     * @var array
     */
    protected $standards = [
        'align-content' => true,
        'align-items' => true,
        'align-self' => true,
        'alignment-baseline' => true,
        'all' => true,
        'animation' => true,
        'animation-delay' => true,
        'animation-direction' => true,
        'animation-duration' => true,
        'animation-fill-mode' => true,
        'animation-iteration-count' => true,
        'animation-name' => true,
        'animation-play-state' => true,
        'animation-timing-function' => true,
        'appearance' => true,
        'azimuth' => true,
        'backface-visibility' => true,
        'background' => true,
        'background-attachment' => true,
        'background-blend-mode' => true,
        'background-clip' => true,
        'background-color' => true,
        'background-image' => true,
        'background-origin' => true,
        'background-position' => true,
        'background-repeat' => true,
        'background-size' => true,
        'baseline-shift' => true,
        'bookmark-label' => true,
        'bookmark-level' => true,
        'bookmark-state' => true,
        'border' => true,
        'border-bottom' => true,
        'border-bottom-color' => true,
        'border-bottom-left-radius' => true,
        'border-bottom-right-radius' => true,
        'border-bottom-style' => true,
        'border-bottom-width' => true,
        'border-boundary' => true,
        'border-collapse' => true,
        'border-color' => true,
        'border-image' => true,
        'border-image-outset' => true,
        'border-image-repeat' => true,
        'border-image-slice' => true,
        'border-image-source' => true,
        'border-image-width' => true,
        'border-left' => true,
        'border-left-color' => true,
        'border-left-style' => true,
        'border-left-width' => true,
        'border-radius' => true,
        'border-right' => true,
        'border-right-color' => true,
        'border-right-style' => true,
        'border-right-width' => true,
        'border-spacing' => true,
        'border-style' => true,
        'border-top' => true,
        'border-top-color' => true,
        'border-top-left-radius' => true,
        'border-top-right-radius' => true,
        'border-top-style' => true,
        'border-top-width' => true,
        'border-width' => true,
        'bottom' => true,
        'box-decoration-break' => true,
        'box-shadow' => true,
        'box-sizing' => true,
        'box-snap' => true,
        'box-suppress' => true,
        'break-after' => true,
        'break-before' => true,
        'break-inside' => true,
        'caption-side' => true,
        'caret' => true,
        'caret-animation' => true,
        'caret-color' => true,
        'caret-shape' => true,
        'chains' => true,
        'clear' => true,
        'clip' => true,
        'clip-path' => true,
        'clip-rule' => true,
        'color' => true,
        'color-adjust' => true,
        'color-interpolation-filters' => true,
        'column-count' => true,
        'column-fill' => true,
        'column-gap' => true,
        'column-rule' => true,
        'column-rule-color' => true,
        'column-rule-style' => true,
        'column-rule-width' => true,
        'column-span' => true,
        'column-width' => true,
        'columns' => true,
        'content' => true,
        'continue' => true,
        'counter-increment' => true,
        'counter-reset' => true,
        'counter-set' => true,
        'cue' => true,
        'cue-after' => true,
        'cue-before' => true,
        'cursor' => true,
        'direction' => true,
        'display' => true,
        'display-or-not' => true,
        'dominant-baseline' => true,
        'elevation' => true,
        'empty-cells' => true,
        'filter' => true,
        'flex' => true,
        'flex-basis' => true,
        'flex-direction' => true,
        'flex-flow' => true,
        'flex-grow' => true,
        'flex-shrink' => true,
        'flex-wrap' => true,
        'float' => true,
        'float-defer' => true,
        'float-offset' => true,
        'float-reference' => true,
        'flood-color' => true,
        'flood-opacity' => true,
        'flow' => true,
        'flow-from' => true,
        'flow-into' => true,
        'font' => true,
        'font-family' => true,
        'font-feature-settings' => true,
        'font-kerning' => true,
        'font-language-override' => true,
        'font-size' => true,
        'font-size-adjust' => true,
        'font-stretch' => true,
        'font-style' => true,
        'font-synthesis' => true,
        'font-variant' => true,
        'font-variant-alternates' => true,
        'font-variant-caps' => true,
        'font-variant-east-asian' => true,
        'font-variant-ligatures' => true,
        'font-variant-numeric' => true,
        'font-variant-position' => true,
        'font-weight' => true,
        'footnote-display' => true,
        'footnote-policy' => true,
        'glyph-orientation-vertical' => true,
        'grid' => true,
        'grid-area' => true,
        'grid-auto-columns' => true,
        'grid-auto-flow' => true,
        'grid-auto-rows' => true,
        'grid-column' => true,
        'grid-column-end' => true,
        'grid-column-gap' => true,
        'grid-column-start' => true,
        'grid-gap' => true,
        'grid-row' => true,
        'grid-row-end' => true,
        'grid-row-gap' => true,
        'grid-row-start' => true,
        'grid-template' => true,
        'grid-template-areas' => true,
        'grid-template-columns' => true,
        'grid-template-rows' => true,
        'hanging-punctuation' => true,
        'height' => true,
        'hyphenate-character' => true,
        'hyphenate-limit-chars' => true,
        'hyphenate-limit-last' => true,
        'hyphenate-limit-lines' => true,
        'hyphenate-limit-zone' => true,
        'hyphens' => true,
        'image-orientation' => true,
        'image-rendering' => true,
        'image-resolution' => true,
        'initial-letter' => true,
        'initial-letter-align' => true,
        'initial-letter-wrap' => true,
        'isolation' => true,
        'justify-content' => true,
        'justify-items' => true,
        'justify-self' => true,
        'left' => true,
        'letter-spacing' => true,
        'lighting-color' => true,
        'line-break' => true,
        'line-grid' => true,
        'line-height' => true,
        'line-snap' => true,
        'list-style' => true,
        'list-style-image' => true,
        'list-style-position' => true,
        'list-style-type' => true,
        'margin' => true,
        'margin-bottom' => true,
        'margin-left' => true,
        'margin-right' => true,
        'margin-top' => true,
        'marker' => true,
        'marker-end' => true,
        'marker-knockout-left' => true,
        'marker-knockout-right' => true,
        'marker-mid' => true,
        'marker-pattern' => true,
        'marker-segment' => true,
        'marker-side' => true,
        'marker-start' => true,
        'marquee-direction' => true,
        'marquee-loop' => true,
        'marquee-speed' => true,
        'marquee-style' => true,
        'mask' => true,
        'mask-border' => true,
        'mask-border-mode' => true,
        'mask-border-outset' => true,
        'mask-border-repeat' => true,
        'mask-border-slice' => true,
        'mask-border-source' => true,
        'mask-border-width' => true,
        'mask-clip' => true,
        'mask-composite' => true,
        'mask-image' => true,
        'mask-mode' => true,
        'mask-origin' => true,
        'mask-position' => true,
        'mask-repeat' => true,
        'mask-size' => true,
        'mask-type' => true,
        'max-height' => true,
        'max-lines' => true,
        'max-width' => true,
        'min-height' => true,
        'min-width' => true,
        'mix-blend-mode' => true,
        'motion' => true,
        'motion-offset' => true,
        'motion-path' => true,
        'motion-rotation' => true,
        'nav-down' => true,
        'nav-left' => true,
        'nav-right' => true,
        'nav-up' => true,
        'object-fit' => true,
        'object-position' => true,
        'offset' => true,
        'offset-after' => true,
        'offset-anchor' => true,
        'offset-before' => true,
        'offset-distance' => true,
        'offset-end' => true,
        'offset-path' => true,
        'offset-position' => true,
        'offset-rotate' => true,
        'offset-start' => true,
        'opacity' => true,
        'order' => true,
        'orphans' => true,
        'outline' => true,
        'outline-color' => true,
        'outline-offset' => true,
        'outline-style' => true,
        'outline-width' => true,
        'overflow' => true,
        'overflow-style' => true,
        'overflow-wrap' => true,
        'overflow-x' => true,
        'overflow-y' => true,
        'padding' => true,
        'padding-bottom' => true,
        'padding-left' => true,
        'padding-right' => true,
        'padding-top' => true,
        'page' => true,
        'page-break-after' => true,
        'page-break-before' => true,
        'page-break-inside' => true,
        'pause' => true,
        'pause-after' => true,
        'pause-before' => true,
        'perspective' => true,
        'perspective-origin' => true,
        'pitch' => true,
        'pitch-range' => true,
        'place-content' => true,
        'place-items' => true,
        'place-self' => true,
        'play-during' => true,
        'pointer-events' => true,
        'position' => true,
        'presentation-level' => true,
        'quotes' => true,
        'region-fragment' => true,
        'resize' => true,
        'rest' => true,
        'rest-after' => true,
        'rest-before' => true,
        'richness' => true,
        'right' => true,
        'rotation' => true,
        'rotation-point' => true,
        'ruby-align' => true,
        'ruby-merge' => true,
        'ruby-position' => true,
        'running' => true,
        'scroll-behavior' => true,
        'scroll-padding' => true,
        'scroll-padding-block' => true,
        'scroll-padding-block-end' => true,
        'scroll-padding-block-start' => true,
        'scroll-padding-bottom' => true,
        'scroll-padding-inline' => true,
        'scroll-padding-inline-end' => true,
        'scroll-padding-inline-start' => true,
        'scroll-padding-left' => true,
        'scroll-padding-right' => true,
        'scroll-padding-top' => true,
        'scroll-snap-align' => true,
        'scroll-snap-margin' => true,
        'scroll-snap-margin-block' => true,
        'scroll-snap-margin-block-end' => true,
        'scroll-snap-margin-block-start' => true,
        'scroll-snap-margin-bottom' => true,
        'scroll-snap-margin-inline' => true,
        'scroll-snap-margin-inline-end' => true,
        'scroll-snap-margin-inline-start' => true,
        'scroll-snap-margin-left' => true,
        'scroll-snap-margin-right' => true,
        'scroll-snap-margin-top' => true,
        'scroll-snap-stop' => true,
        'scroll-snap-type' => true,
        'scrollbar-gutter' => true,
        'shape-image-threshold' => true,
        'shape-inside' => true,
        'shape-margin' => true,
        'shape-outside' => true,
        'size' => true,
        'speak' => true,
        'speak-as' => true,
        'speak-header' => true,
        'speak-numeral' => true,
        'speak-punctuation' => true,
        'speech-rate' => true,
        'src' => true, // @font-face property
        'stress' => true,
        'string-set' => true,
        'stroke' => true,
        'stroke-alignment' => true,
        'stroke-dashadjust' => true,
        'stroke-dasharray' => true,
        'stroke-dashcorner' => true,
        'stroke-dashoffset' => true,
        'stroke-linecap' => true,
        'stroke-linejoin' => true,
        'stroke-miterlimit' => true,
        'stroke-opacity' => true,
        'stroke-width' => true,
        'tab-size' => true,
        'table-layout' => true,
        'text-align' => true,
        'text-align-all' => true,
        'text-align-last' => true,
        'text-combine-upright' => true,
        'text-decoration' => true,
        'text-decoration-color' => true,
        'text-decoration-line' => true,
        'text-decoration-skip' => true,
        'text-decoration-style' => true,
        'text-emphasis' => true,
        'text-emphasis-color' => true,
        'text-emphasis-position' => true,
        'text-emphasis-style' => true,
        'text-indent' => true,
        'text-justify' => true,
        'text-orientation' => true,
        'text-overflow' => true,
        'text-rendering' => true,
        'text-shadow' => true,
        'text-space-collapse' => true,
        'text-space-trim' => true,
        'text-spacing' => true,
        'text-transform' => true,
        'text-underline-position' => true,
        'text-wrap' => true,
        'top' => true,
        'touch-action' => true,
        'transform' => true,
        'transform-box' => true,
        'transform-origin' => true,
        'transform-style' => true,
        'transition' => true,
        'transition-delay' => true,
        'transition-duration' => true,
        'transition-property' => true,
        'transition-timing-function' => true,
        'unicode-bidi' => true,
        'user-select' => true,
        'vertical-align' => true,
        'visibility' => true,
        'voice-balance' => true,
        'voice-duration' => true,
        'voice-family' => true,
        'voice-pitch' => true,
        'voice-range' => true,
        'voice-rate' => true,
        'voice-stress' => true,
        'voice-volume' => true,
        'volume' => true,
        'white-space' => true,
        'widows' => true,
        'width' => true,
        'will-change' => true,
        'word-break' => true,
        'word-spacing' => true,
        'word-wrap' => true,
        'wrap-after' => true,
        'wrap-before' => true,
        'wrap-flow' => true,
        'wrap-inside' => true,
        'wrap-through' => true,
        'writing-mode' => true,
        'z-index' => true,
        'zoom' => true,
    ];

    /**
     * List of non standards properties
     * @var array
     */
    protected $nonStandards = [
        'font-smoothing' => true,
        'interpolation-mode' => true,
        'osx-font-smoothing' => true,
        'overflow-scrolling' => true,
        'tap-highlight-color' => true,
        'text-size-adjust' => true,
    ];

    /**
     * List of allowed indentation chars
     * @var array
     */
    protected $allowedIndentationChars = [' '];

    /**
     * Override default properties
     * "allowedIndentationChars" => [" "] or ["\t"]: will override current property
     * "constructors": ["property" => bool]: will merge with current property
     * "standards": ["property" => bool]: will merge with current property
     * "nonStandards": ["property" => bool]: will merge with current property
     */
    public function setOptions(array $aOptions = [])
    {
        if (isset($aOptions['allowedIndentationChars'])) {
            $this->setAllowedIndentationChars($aOptions['allowedIndentationChars']);
        }

        if (isset($aOptions['constructors'])) {
            $this->mergeConstructors($aOptions['constructors']);
        }

        if (isset($aOptions['standards'])) {
            $this->mergeStandards($aOptions['standards']);
        }

        if (isset($aOptions['nonStandards'])) {
            $this->mergeNonStandards($aOptions['nonStandards']);
        }
    }

    /**
     * Checks that the given CSS property is an existing one
     * @param string $sProperty the property to check
     * @return boolean true if the property exists, else returns false
     */
    public function propertyExists(string $sProperty): bool
    {
        if (!empty($this->standards[$sProperty])) {
            return true;
        }

        $aAllowedConstrutors = array_keys(array_filter($this->constructors));
        $sPropertyWithoutConstructor = preg_replace(
            '/^(-(' . join('|', $aAllowedConstrutors) . ')-)/',
            '',
            $sProperty
        );

        if ($sPropertyWithoutConstructor !== $sProperty) {
            if (!empty($this->standards[$sPropertyWithoutConstructor])) {
                return true;
            }
            if (!empty($this->nonStandards[$sPropertyWithoutConstructor])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve indentation chars allowed by the linter
     * @return array a list of allowed indentation chars
     */
    public function getAllowedIndentationChars(): array
    {
        return $this->allowedIndentationChars;
    }

    /**
     * Define the indentation chars allowed by the linter
     * @param array $aAllowedIndentationChars a list of allowed indentation chars
     */
    public function setAllowedIndentationChars(array $aAllowedIndentationChars)
    {
        $this->allowedIndentationChars = $aAllowedIndentationChars;
    }

    /**
     * Check if the given char is allowed as an indentation char
     * @param string $sChar the character to be checked
     * @return bool according to whether the character is allowed or not
     */
    public function isAllowedIndentationChar(string $sChar): bool
    {
        return in_array($sChar, $this->allowedIndentationChars, true);
    }

    /**
     * Merge the given constructors properties with the current ones
     * @param array $aConstructors the constructors properties to be merged
     */
    public function mergeConstructors(array $aConstructors)
    {
        $this->constructors = array_merge($this->constructors, $aConstructors);
    }

    /**
     * Merge the given standards properties with the current ones
     * @param array $aStandards the standards properties to be merged
     */
    public function mergeStandards(array $aStandards)
    {
        $this->standards = array_merge($this->standards, $aStandards);
    }

    /**
     * Merge the given non standards properties with the current ones
     * @param array $aNonStandards non the standards properties to be merged
     */
    public function mergeNonStandards(array $aNonStandards)
    {
        $this->nonStandards = array_merge($this->nonStandards, $aNonStandards);
    }
}
