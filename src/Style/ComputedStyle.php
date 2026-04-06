<?php

declare(strict_types=1);

namespace Atelier\Svg\Style;

use Atelier\Svg\Element\ElementInterface;

final class ComputedStyle
{
    private const array INHERITABLE_ATTRIBUTES = [
        'clip-rule',
        'color',
        'color-interpolation',
        'color-interpolation-filters',
        'cursor',
        'direction',
        'dominant-baseline',
        'fill',
        'fill-opacity',
        'fill-rule',
        'font',
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
        'letter-spacing',
        'marker',
        'marker-end',
        'marker-mid',
        'marker-start',
        'opacity',
        'overflow',
        'paint-order',
        'pointer-events',
        'shape-rendering',
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
        'visibility',
        'word-spacing',
        'writing-mode',
    ];

    /**
     * @param array<string, string> $properties
     */
    private function __construct(private array $properties)
    {
    }

    /**
     * Resolves the computed style for an element by walking up the tree.
     *
     * For inheritable SVG presentation attributes, the value is resolved
     * by checking the element itself, then its ancestors.
     */
    public static function of(ElementInterface $element): self
    {
        $properties = [];

        // Collect own attributes
        foreach ($element->getAttributes() as $name => $value) {
            $properties[$name] = $value;
        }

        // Walk up the tree for inheritable attributes not set on this element
        $parent = $element->getParent();
        while (null !== $parent) {
            foreach (self::INHERITABLE_ATTRIBUTES as $attr) {
                if (isset($properties[$attr])) {
                    continue;
                }

                $value = $parent->getAttribute($attr);
                if (null !== $value) {
                    $properties[$attr] = $value;
                }
            }

            $parent = $parent->getParent();
        }

        return new self($properties);
    }

    public function get(string $property): ?string
    {
        return $this->properties[$property] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->properties;
    }

    public static function isInheritable(string $attribute): bool
    {
        return in_array($attribute, self::INHERITABLE_ATTRIBUTES, true);
    }

    /**
     * @return list<string>
     */
    public static function getInheritableAttributes(): array
    {
        return self::INHERITABLE_ATTRIBUTES;
    }
}
