<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feFlood> SVG filter primitive.
 *
 * This filter primitive creates a rectangle filled with the specified color and opacity.
 * It's often used as input to other filter primitives.
 *
 * Common uses:
 * - Creating solid color layers
 * - Background fills for drop shadows
 * - Mask fills
 *
 * Example:
 * <feFlood flood-color="red" flood-opacity="0.5" result="flood"/>
 * <feFlood flood-color="#000000" flood-opacity="1"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feFloodElement
 */
final class FeFloodElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feFlood');
    }

    /**
     * Set the flood color.
     *
     * Can be any CSS color value: named colors, hex, rgb(), etc.
     */
    public function setFloodColor(string $color): static
    {
        $this->setAttribute('flood-color', $color);

        return $this;
    }

    /**
     * Get the flood color.
     */
    public function getFloodColor(): ?string
    {
        return $this->getAttribute('flood-color');
    }

    /**
     * Set the flood opacity (0 to 1).
     */
    public function setFloodOpacity(string|int|float $opacity): static
    {
        $this->setAttribute('flood-opacity', (string) $opacity);

        return $this;
    }

    /**
     * Get the flood opacity.
     */
    public function getFloodOpacity(): ?string
    {
        return $this->getAttribute('flood-opacity');
    }
}
