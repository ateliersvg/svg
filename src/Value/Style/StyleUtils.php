<?php

declare(strict_types=1);

namespace Atelier\Svg\Value\Style;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Value\Style;

/**
 * Helper for managing styles on SVG elements.
 *
 * Provides utilities for extracting styles from attributes to inline styles,
 * batch style operations, and style precedence handling.
 */
final class StyleUtils
{
    /**
     * Presentation attributes that can be converted to CSS properties.
     */
    private const array PRESENTATION_ATTRIBUTES = [
        'fill', 'fill-opacity', 'fill-rule',
        'stroke', 'stroke-width', 'stroke-opacity', 'stroke-linecap', 'stroke-linejoin',
        'stroke-dasharray', 'stroke-dashoffset', 'stroke-miterlimit',
        'opacity',
        'color',
        'font-family', 'font-size', 'font-weight', 'font-style',
        'text-anchor', 'text-decoration',
        'display', 'visibility',
        'clip-path', 'mask',
        'filter',
        'transform',
    ];

    /**
     * Extracts all presentation attributes from an element and converts them to inline styles.
     */
    public static function attributesToStyles(AbstractElement $element): Style
    {
        $style = Style::parse($element->getAttribute('style'));

        foreach (self::PRESENTATION_ATTRIBUTES as $attr) {
            $value = $element->getAttribute($attr);
            if (null !== $value && !$style->has($attr)) {
                $style->set($attr, $value);
            }
        }

        return $style;
    }

    /**
     * Converts inline styles to presentation attributes where possible.
     */
    public static function stylesToAttributes(AbstractElement $element): void
    {
        $style = Style::parse($element->getAttribute('style'));
        $remainingStyles = Style::fromArray([]);

        foreach ($style->getAll() as $property => $value) {
            if (in_array($property, self::PRESENTATION_ATTRIBUTES, true)) {
                // Move to attribute
                $element->setAttribute($property, $value);
            } else {
                // Keep in style
                $remainingStyles->set($property, $value);
            }
        }

        if ($remainingStyles->isEmpty()) {
            $element->removeAttribute('style');
        } else {
            $element->setAttribute('style', $remainingStyles->toString());
        }
    }

    /**
     * Merges multiple style objects, with later styles overriding earlier ones.
     */
    public static function mergeStyles(Style ...$styles): Style
    {
        $merged = Style::fromArray([]);

        foreach ($styles as $style) {
            $merged->merge($style);
        }

        return $merged;
    }

    /**
     * Extracts all styles from an element (both inline and presentation attributes).
     */
    public static function getAllStyles(AbstractElement $element): Style
    {
        return self::attributesToStyles($element);
    }

    /**
     * Sets multiple styles on an element at once.
     *
     * @param array<string, string> $styles
     */
    public static function setStyles(AbstractElement $element, array $styles): void
    {
        $style = Style::parse($element->getAttribute('style'));

        foreach ($styles as $property => $value) {
            $style->set($property, $value);
        }

        $element->setAttribute('style', $style->toString());
    }

    /**
     * Gets a style property value from an element.
     * Checks inline styles first, then presentation attributes.
     */
    public static function getStyle(AbstractElement $element, string $property): ?string
    {
        $style = Style::parse($element->getAttribute('style'));

        if ($style->has($property)) {
            return $style->get($property);
        }

        // Fall back to presentation attribute
        if (in_array($property, self::PRESENTATION_ATTRIBUTES, true)) {
            return $element->getAttribute($property);
        }

        return null;
    }

    /**
     * Removes a style property from an element.
     * Removes from both inline styles and presentation attributes.
     */
    public static function removeStyle(AbstractElement $element, string $property): void
    {
        // Remove from inline styles
        $style = Style::parse($element->getAttribute('style'));
        $style->remove($property);

        if ($style->isEmpty()) {
            $element->removeAttribute('style');
        } else {
            $element->setAttribute('style', $style->toString());
        }

        // Remove presentation attribute if it exists
        if (in_array($property, self::PRESENTATION_ATTRIBUTES, true)) {
            $element->removeAttribute($property);
        }
    }

    /**
     * Normalizes color values to a consistent format.
     */
    public static function normalizeColor(?string $color): string
    {
        if (null === $color) {
            return 'none';
        }

        // Named colors to hex
        $namedColors = [
            'black' => '#000000',
            'white' => '#ffffff',
            'red' => '#ff0000',
            'green' => '#008000',
            'blue' => '#0000ff',
            // Add more as needed
        ];

        $color = strtolower(trim($color));

        if (isset($namedColors[$color])) {
            return $namedColors[$color];
        }

        // Expand 3-digit hex to 6-digit
        if (preg_match('/^#([0-9a-f]{3})$/i', $color, $matches)) {
            $hex = $matches[1];

            return '#'.$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return $color;
    }

    /**
     * Minifies a color value (converts to shortest form).
     */
    public static function minifyColor(?string $color): string
    {
        if (null === $color) {
            return 'none';
        }

        // Convert 6-digit hex to 3-digit if possible
        if (preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $color, $matches)) {
            [$_, $r, $g, $b] = $matches;

            if ($r[0] === $r[1] && $g[0] === $g[1] && $b[0] === $b[1]) {
                return '#'.$r[0].$g[0].$b[0];
            }
        }

        return $color;
    }
}
