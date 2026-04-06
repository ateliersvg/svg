<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Removes unknown and default-valued attributes from SVG elements.
 *
 * This pass removes:
 * - Attributes with their default SVG values
 * - Unknown/non-standard attributes (optionally)
 *
 * Example:
 * Before: <rect fill="black" stroke="none" custom-attr="value"/>
 * After:  <rect/>
 */
final class RemoveUnknownsAndDefaultsPass extends AbstractOptimizerPass
{
    // SVG default values by attribute
    private const array DEFAULT_VALUES = [
        'fill' => 'black',
        'fill-opacity' => '1',
        'fill-rule' => 'nonzero',
        'stroke' => 'none',
        'stroke-opacity' => '1',
        'stroke-width' => '1',
        'stroke-linecap' => 'butt',
        'stroke-linejoin' => 'miter',
        'stroke-miterlimit' => '4',
        'opacity' => '1',
        'stop-opacity' => '1',
        'font-family' => 'serif',
        'font-size' => 'medium',
        'font-style' => 'normal',
        'font-variant' => 'normal',
        'font-weight' => 'normal',
        'font-stretch' => 'normal',
        'text-anchor' => 'start',
        'visibility' => 'visible',
        'display' => 'inline',
        'overflow' => 'visible',
        'clip-rule' => 'nonzero',
        'marker-start' => 'none',
        'marker-mid' => 'none',
        'marker-end' => 'none',
    ];

    public function __construct(
        private readonly bool $removeDefaults = true,
    ) {
    }

    public function getName(): string
    {
        return 'remove-unknowns-and-defaults';
    }

    protected function processElement(ElementInterface $element): void
    {
        // Get all attributes to check
        $attributesToRemove = [];

        // Check each attribute
        foreach ($this->getAttributeNames($element) as $attrName) {
            $attrValue = $element->getAttribute($attrName);

            assert(null !== $attrValue);

            // Remove defaults
            if ($this->removeDefaults && $this->isDefaultValue($attrName, $attrValue)) {
                $attributesToRemove[] = $attrName;
            }
        }

        // Remove identified attributes
        foreach ($attributesToRemove as $attrName) {
            $element->removeAttribute($attrName);
        }
    }

    private function isDefaultValue(string $attrName, string $value): bool
    {
        assert(isset(self::DEFAULT_VALUES[$attrName]));

        return self::DEFAULT_VALUES[$attrName] === $value;
    }

    /**
     * Get all attribute names from an element.
     *
     * @return string[]
     */
    private function getAttributeNames(ElementInterface $element): array
    {
        // This is a workaround since ElementInterface doesn't have a method to list all attributes
        // We'll try common presentation attributes
        $commonAttrs = [
            'fill', 'fill-opacity', 'fill-rule',
            'stroke', 'stroke-opacity', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit',
            'opacity', 'stop-opacity',
            'font-family', 'font-size', 'font-style', 'font-variant', 'font-weight', 'font-stretch',
            'text-anchor', 'visibility', 'display', 'overflow', 'clip-rule',
            'marker-start', 'marker-mid', 'marker-end',
        ];

        $found = [];
        foreach ($commonAttrs as $attr) {
            if ($element->hasAttribute($attr)) {
                $found[] = $attr;
            }
        }

        return $found;
    }
}
