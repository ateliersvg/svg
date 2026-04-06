<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes attributes with default values.
 *
 * This pass removes attributes that have their default values according to the SVG specification,
 * reducing file size without changing the visual appearance.
 */
final class RemoveDefaultAttributesPass extends AbstractOptimizerPass
{
    /**
     * Map of element types to their default attributes.
     * '*' key applies to all elements.
     */
    private const array DEFAULT_VALUES = [
        '*' => [
            'opacity' => '1',
            'fill-opacity' => '1',
            'stroke-opacity' => '1',
            'fill-rule' => 'nonzero',
            'clip-rule' => 'nonzero',
            'stroke-linecap' => 'butt',
            'stroke-linejoin' => 'miter',
            'stroke-miterlimit' => '4',
            'stroke-dasharray' => 'none',
            'stroke-dashoffset' => '0',
            'font-style' => 'normal',
            'font-variant' => 'normal',
            'font-weight' => 'normal',
            'font-stretch' => 'normal',
            'text-anchor' => 'start',
            'writing-mode' => 'lr-tb',
            'letter-spacing' => 'normal',
            'word-spacing' => 'normal',
            'text-decoration' => 'none',
            'overflow' => 'visible',
            'visibility' => 'visible',
            'display' => 'inline',
            'mask' => 'none',
            'clip-path' => 'none',
            'filter' => 'none',
            'pointer-events' => 'visiblePainted',
        ],
        'rect' => [
            'fill' => 'black',
            'stroke' => 'none',
            'stroke-width' => '1',
            'x' => '0',
            'y' => '0',
            'rx' => '0',
            'ry' => '0',
        ],
        'circle' => [
            'fill' => 'black',
            'stroke' => 'none',
            'stroke-width' => '1',
            'cx' => '0',
            'cy' => '0',
        ],
        'ellipse' => [
            'fill' => 'black',
            'stroke' => 'none',
            'stroke-width' => '1',
            'cx' => '0',
            'cy' => '0',
        ],
        'line' => [
            'stroke' => 'none',
            'stroke-width' => '1',
            'x1' => '0',
            'y1' => '0',
            'x2' => '0',
            'y2' => '0',
        ],
        'path' => [
            'fill' => 'black',
            'stroke' => 'none',
            'stroke-width' => '1',
        ],
        'polygon' => [
            'fill' => 'black',
            'stroke' => 'none',
            'stroke-width' => '1',
        ],
        'polyline' => [
            'fill' => 'black',
            'stroke' => 'none',
            'stroke-width' => '1',
        ],
        'text' => [
            'fill' => 'black',
            'stroke' => 'none',
            'stroke-width' => '1',
            'x' => '0',
            'y' => '0',
        ],
        'tspan' => [
            'x' => '0',
            'y' => '0',
        ],
        'g' => [
            'fill' => 'black',
            'stroke' => 'none',
            'stroke-width' => '1',
        ],
        'svg' => [
            'x' => '0',
            'y' => '0',
        ],
        'image' => [
            'x' => '0',
            'y' => '0',
            'preserveAspectRatio' => 'xMidYMid meet',
        ],
        'marker' => [
            'markerUnits' => 'strokeWidth',
            'refX' => '0',
            'refY' => '0',
            'orient' => '0',
        ],
        'pattern' => [
            'x' => '0',
            'y' => '0',
            'patternUnits' => 'objectBoundingBox',
            'patternContentUnits' => 'userSpaceOnUse',
        ],
        'radialGradient' => [
            'cx' => '50%',
            'cy' => '50%',
            'r' => '50%',
            'fx' => '50%',
            'fy' => '50%',
            'gradientUnits' => 'objectBoundingBox',
            'spreadMethod' => 'pad',
        ],
        'linearGradient' => [
            'x1' => '0%',
            'y1' => '0%',
            'x2' => '100%',
            'y2' => '0%',
            'gradientUnits' => 'objectBoundingBox',
            'spreadMethod' => 'pad',
        ],
        'stop' => [
            'offset' => '0',
            'stop-opacity' => '1',
        ],
    ];

    /**
     * Creates a new RemoveDefaultAttributesPass.
     *
     * @param array<string> $removeAttrs Specific attributes to remove (empty = all defaults)
     */
    public function __construct(private readonly array $removeAttrs = [])
    {
    }

    public function getName(): string
    {
        return 'remove-default-attributes';
    }

    protected function processElement(ElementInterface $element): void
    {
        $tagName = $element->getTagName();
        $defaults = $this->getDefaultsForElement($tagName);

        foreach ($defaults as $attrName => $defaultValue) {
            if (!$this->shouldRemoveAttribute($attrName)) {
                continue;
            }

            if (!$element->hasAttribute($attrName)) {
                continue;
            }

            $attrValue = $element->getAttribute($attrName);
            assert(null !== $attrValue);

            // Normalize values for comparison
            if ($this->normalizeValue($attrValue) === $this->normalizeValue($defaultValue)) {
                $element->removeAttribute($attrName);
            }
        }
    }

    /**
     * Gets the default attributes for an element.
     *
     * @return array<string, string>
     */
    private function getDefaultsForElement(string $tagName): array
    {
        // @phpstan-ignore-next-line
        $defaults = self::DEFAULT_VALUES['*'] ?? [];

        if (isset(self::DEFAULT_VALUES[$tagName])) {
            $defaults = array_merge($defaults, self::DEFAULT_VALUES[$tagName]);
        }

        return $defaults;
    }

    /**
     * Checks if an attribute should be removed.
     */
    private function shouldRemoveAttribute(string $attrName): bool
    {
        // If specific attributes are configured, only remove those
        if (!empty($this->removeAttrs)) {
            return in_array($attrName, $this->removeAttrs, true);
        }

        // Otherwise remove all defaults
        return true;
    }

    /**
     * Normalizes a value for comparison.
     */
    private function normalizeValue(string $value): string
    {
        $value = trim($value);
        $value = strtolower($value);

        // Normalize numeric values
        if (is_numeric($value)) {
            // Remove trailing zeros and decimal point if not needed
            $value = rtrim(rtrim($value, '0'), '.');
        }

        return $value;
    }
}
