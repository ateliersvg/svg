<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that converts inline style attributes to presentation attributes.
 *
 * This pass converts CSS properties in style="" attributes to individual presentation
 * attributes when it results in shorter output.
 *
 * Example: style="fill: red; stroke: blue" -> fill="red" stroke="blue"
 */
final class ConvertStyleToAttrsPass extends AbstractOptimizerPass
{
    /**
     * CSS properties that can be converted to presentation attributes.
     */
    private const array PRESENTATION_ATTRIBUTES = [
        'alignment-baseline',
        'baseline-shift',
        'clip',
        'clip-path',
        'clip-rule',
        'color',
        'color-interpolation',
        'color-interpolation-filters',
        'color-profile',
        'color-rendering',
        'cursor',
        'direction',
        'display',
        'dominant-baseline',
        'enable-background',
        'fill',
        'fill-opacity',
        'fill-rule',
        'filter',
        'flood-color',
        'flood-opacity',
        'font-family',
        'font-size',
        'font-size-adjust',
        'font-stretch',
        'font-style',
        'font-variant',
        'font-weight',
        'glyph-orientation-horizontal',
        'glyph-orientation-vertical',
        'image-rendering',
        'kerning',
        'letter-spacing',
        'lighting-color',
        'marker-end',
        'marker-mid',
        'marker-start',
        'mask',
        'opacity',
        'overflow',
        'pointer-events',
        'shape-rendering',
        'stop-color',
        'stop-opacity',
        'stroke',
        'stroke-dasharray',
        'stroke-dashoffset',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'stroke-opacity',
        'stroke-width',
        'text-anchor',
        'text-decoration',
        'text-rendering',
        'unicode-bidi',
        'visibility',
        'word-spacing',
        'writing-mode',
    ];

    /**
     * Creates a new ConvertStyleToAttrsPass.
     *
     * @param bool $onlyMatchShorthand Only convert if result is shorter (default: true)
     */
    public function __construct(private readonly bool $onlyMatchShorthand = true)
    {
    }

    public function getName(): string
    {
        return 'convert-style-to-attrs';
    }

    /**
     * Processes an element to convert styles to attributes.
     */
    protected function processElement(ElementInterface $element): void
    {
        if ($element->hasAttribute('style')) {
            $this->convertStyleToAttrs($element);
        }
    }

    /**
     * Converts style attribute to presentation attributes.
     */
    private function convertStyleToAttrs(ElementInterface $element): void
    {
        $style = $element->getAttribute('style');
        if (null === $style || '' === $style) {
            return;
        }

        // Parse CSS properties
        $properties = $this->parseStyle($style);

        // Separate convertible and non-convertible properties
        $convertible = [];
        $remaining = [];

        foreach ($properties as $name => $value) {
            if (in_array($name, self::PRESENTATION_ATTRIBUTES, true)) {
                $convertible[$name] = $value;
            } else {
                $remaining[$name] = $value;
            }
        }

        if (empty($convertible)) {
            return;
        }

        // Calculate size before and after
        if ($this->onlyMatchShorthand) {
            $originalSize = strlen('style="'.$style.'"');
            $newSize = $this->calculateNewSize($convertible, $remaining);

            if ($newSize >= $originalSize) {
                // Not worth converting
                return;
            }
        }

        // Convert to attributes
        foreach ($convertible as $name => $value) {
            // Don't override existing attributes
            if (!$element->hasAttribute($name)) {
                $element->setAttribute($name, $value);
            }
        }

        // Update or remove style attribute
        if (empty($remaining)) {
            $element->removeAttribute('style');
        } else {
            $newStyle = $this->buildStyle($remaining);
            $element->setAttribute('style', $newStyle);
        }
    }

    /**
     * Parses a style attribute into properties.
     *
     * @return array<string, string>
     */
    private function parseStyle(string $style): array
    {
        $properties = [];
        $declarations = explode(';', $style);

        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if ('' === $declaration) {
                continue;
            }

            $parts = explode(':', $declaration, 2);
            if (2 === count($parts)) {
                $name = trim($parts[0]);
                $value = trim($parts[1]);
                if ('' !== $name && '' !== $value) {
                    $properties[$name] = $value;
                }
            }
        }

        return $properties;
    }

    /**
     * Builds a style attribute from properties.
     *
     * @param array<string, string> $properties
     */
    private function buildStyle(array $properties): string
    {
        $declarations = [];
        foreach ($properties as $name => $value) {
            $declarations[] = $name.': '.$value;
        }

        return implode('; ', $declarations);
    }

    /**
     * Calculates the size of the new representation.
     *
     * @param array<string, string> $convertible
     * @param array<string, string> $remaining
     */
    private function calculateNewSize(array $convertible, array $remaining): int
    {
        $size = 0;

        // Size of presentation attributes
        foreach ($convertible as $name => $value) {
            $size += strlen($name.'="'.$value.'" ');
        }

        // Size of remaining style attribute
        if (!empty($remaining)) {
            $style = $this->buildStyle($remaining);
            $size += strlen('style="'.$style.'"');
        }

        return $size;
    }
}
