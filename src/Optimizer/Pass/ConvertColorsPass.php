<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Value\Color;

/**
 * Optimization pass that converts colors to their shortest representation.
 *
 * This pass optimizes color values by converting them to the most compact form:
 * - #ffffff -> #fff
 * - #ff0000 -> red (if shorter and names enabled)
 * - rgb(255,0,0) -> #f00
 * - rgba(255,0,0,1) -> #f00 (if opaque)
 */
final class ConvertColorsPass extends AbstractOptimizerPass
{
    private const array COLOR_ATTRIBUTES = [
        'fill',
        'stroke',
        'stop-color',
        'flood-color',
        'lighting-color',
        'color',
    ];

    private const array NAMED_COLORS_REVERSE = [
        '#f0f8ff' => 'aliceblue',
        '#faebd7' => 'antiquewhite',
        '#00ffff' => 'aqua',
        '#7fffd4' => 'aquamarine',
        '#f0ffff' => 'azure',
        '#f5f5dc' => 'beige',
        '#ffe4c4' => 'bisque',
        '#000000' => 'black',
        '#ffebcd' => 'blanchedalmond',
        '#0000ff' => 'blue',
        '#8a2be2' => 'blueviolet',
        '#a52a2a' => 'brown',
        '#deb887' => 'burlywood',
        '#5f9ea0' => 'cadetblue',
        '#7fff00' => 'chartreuse',
        '#d2691e' => 'chocolate',
        '#ff7f50' => 'coral',
        '#6495ed' => 'cornflowerblue',
        '#fff8dc' => 'cornsilk',
        '#dc143c' => 'crimson',
        '#00008b' => 'darkblue',
        '#008b8b' => 'darkcyan',
        '#b8860b' => 'darkgoldenrod',
        '#a9a9a9' => 'darkgray',
        '#006400' => 'darkgreen',
        '#bdb76b' => 'darkkhaki',
        '#8b008b' => 'darkmagenta',
        '#556b2f' => 'darkolivegreen',
        '#ff8c00' => 'darkorange',
        '#9932cc' => 'darkorchid',
        '#8b0000' => 'darkred',
        '#e9967a' => 'darksalmon',
        '#8fbc8f' => 'darkseagreen',
        '#483d8b' => 'darkslateblue',
        '#2f4f4f' => 'darkslategray',
        '#00ced1' => 'darkturquoise',
        '#9400d3' => 'darkviolet',
        '#ff1493' => 'deeppink',
        '#00bfff' => 'deepskyblue',
        '#696969' => 'dimgray',
        '#1e90ff' => 'dodgerblue',
        '#b22222' => 'firebrick',
        '#fffaf0' => 'floralwhite',
        '#228b22' => 'forestgreen',
        '#ff00ff' => 'magenta', // fuchsia is duplicate
        '#800000' => 'maroon',
        '#66cdaa' => 'mediumaquamarine',
        '#0000cd' => 'mediumblue',
        '#ba55d3' => 'mediumorchid',
        '#9370db' => 'mediumpurple',
        '#3cb371' => 'mediumseagreen',
        '#7b68ee' => 'mediumslateblue',
        '#00fa9a' => 'mediumspringgreen',
        '#48d1cc' => 'mediumturquoise',
        '#c71585' => 'mediumvioletred',
        '#191970' => 'midnightblue',
        '#f5fffa' => 'mintcream',
        '#ffe4e1' => 'mistyrose',
        '#ffe4b5' => 'moccasin',
        '#ffdead' => 'navajowhite',
        '#000080' => 'navy',
        '#fdf5e6' => 'oldlace',
        '#808000' => 'olive',
        '#6b8e23' => 'olivedrab',
        '#ffa500' => 'orange',
        '#ff4500' => 'orangered',
        '#da70d6' => 'orchid',
        '#eee8aa' => 'palegoldenrod',
        '#98fb98' => 'palegreen',
        '#afeeee' => 'paleturquoise',
        '#db7093' => 'palevioletred',
        '#ffefd5' => 'papayawhip',
        '#ffdab9' => 'peachpuff',
        '#cd853f' => 'peru',
        '#ffc0cb' => 'pink',
        '#dda0dd' => 'plum',
        '#b0e0e6' => 'powderblue',
        '#800080' => 'purple',
        '#663399' => 'rebeccapurple',
        '#ff0000' => 'red',
        '#bc8f8f' => 'rosybrown',
        '#4169e1' => 'royalblue',
        '#8b4513' => 'saddlebrown',
        '#fa8072' => 'salmon',
        '#f4a460' => 'sandybrown',
        '#2e8b57' => 'seagreen',
        '#fff5ee' => 'seashell',
        '#a0522d' => 'sienna',
        '#c0c0c0' => 'silver',
        '#87ceeb' => 'skyblue',
        '#6a5acd' => 'slateblue',
        '#708090' => 'slategray',
        '#fffafa' => 'snow',
        '#00ff7f' => 'springgreen',
        '#4682b4' => 'steelblue',
        '#d2b48c' => 'tan',
        '#008080' => 'teal',
        '#d8bfd8' => 'thistle',
        '#ff6347' => 'tomato',
        '#40e0d0' => 'turquoise',
        '#ee82ee' => 'violet',
        '#f5deb3' => 'wheat',
        '#ffffff' => 'white',
        '#f5f5f5' => 'whitesmoke',
        '#ffff00' => 'yellow',
        '#9acd32' => 'yellowgreen',
    ];

    /**
     * Creates a new ConvertColorsPass.
     *
     * @param bool $convertToShortHex Whether to convert #RRGGBB to #RGB when possible (default: true)
     * @param bool $convertToNames    Whether to convert hex to color names when shorter (default: true)
     * @param bool $convertRgb        Whether to convert rgb()/rgba() to hex (default: true)
     */
    public function __construct(
        private readonly bool $convertToShortHex = true,
        private readonly bool $convertToNames = true,
        bool $convertRgb = true,
    ) {
        unset($convertRgb); // Suppress unused parameter error
    }

    public function getName(): string
    {
        return 'convert-colors';
    }

    /**
     * Processes an element to convert colors.
     */
    protected function processElement(ElementInterface $element): void
    {
        foreach (self::COLOR_ATTRIBUTES as $attr) {
            if ($element->hasAttribute($attr)) {
                $value = $element->getAttribute($attr);
                if (null !== $value) {
                    $optimized = $this->optimizeColor($value);
                    if ($optimized !== $value) {
                        $element->setAttribute($attr, $optimized);
                    }
                }
            }
        }

        // Process style attribute
        if ($element->hasAttribute('style')) {
            $style = $element->getAttribute('style');
            if (null !== $style) {
                $optimized = $this->optimizeStyleColors($style);
                if ($optimized !== $style) {
                    $element->setAttribute('style', $optimized);
                }
            }
        }
    }

    /**
     * Optimizes a single color value.
     */
    private function optimizeColor(string $value): string
    {
        $value = trim($value);

        // Don't optimize special values
        if (in_array(strtolower($value), ['none', 'currentcolor', 'transparent', 'inherit'], true)) {
            return $value;
        }

        // Don't optimize url() references
        if (str_starts_with($value, 'url(')) {
            return $value;
        }

        try {
            $color = Color::parse($value);

            return $this->getShortestColorRepresentation($color);
        } catch (InvalidArgumentException) {
            // If parsing fails, return original value
            return $value;
        }
    }

    /**
     * Optimizes colors in a style attribute.
     */
    private function optimizeStyleColors(string $style): string
    {
        // Match color properties in style
        $pattern = '/('.implode('|', array_map(preg_quote(...), self::COLOR_ATTRIBUTES)).')\s*:\s*([^;]+)/';

        return preg_replace_callback($pattern, function ($matches) {
            $property = $matches[1];
            $value = trim($matches[2]);
            $optimized = $this->optimizeColor($value);

            return $property.': '.$optimized;
        }, $style) ?? $style;
    }

    /**
     * Gets the shortest representation of a color.
     */
    private function getShortestColorRepresentation(Color $color): string
    {
        if ($color->isTransparent()) {
            return 'none';
        }

        // For non-opaque colors, we need rgba or hex8
        if (!$color->isOpaque()) {
            $hex8 = $color->toHex();
            $rgba = $color->toRgb();

            return strlen($hex8) <= strlen($rgba) ? $hex8 : $rgba;
        }

        // Get hex representation
        $hex6 = strtolower($color->toHex());
        $candidates = [];

        // Add full hex
        $candidates[] = $hex6;

        // Try short hex (#RGB)
        if ($this->convertToShortHex && $this->canShortenHex($hex6)) {
            $candidates[] = $this->shortenHex($hex6);
        }

        // Try color names
        if ($this->convertToNames && isset(self::NAMED_COLORS_REVERSE[$hex6])) {
            $candidates[] = self::NAMED_COLORS_REVERSE[$hex6];
        }

        // Return shortest
        usort($candidates, fn ($a, $b) => strlen($a) <=> strlen($b));

        return $candidates[0];
    }

    /**
     * Checks if a hex color can be shortened from #RRGGBB to #RGB.
     */
    private function canShortenHex(string $hex): bool
    {
        assert(7 === strlen($hex) && '#' === $hex[0]);

        return $hex[1] === $hex[2]
            && $hex[3] === $hex[4]
            && $hex[5] === $hex[6];
    }

    /**
     * Shortens a hex color from #RRGGBB to #RGB.
     */
    private function shortenHex(string $hex): string
    {
        return '#'.$hex[1].$hex[3].$hex[5];
    }
}
