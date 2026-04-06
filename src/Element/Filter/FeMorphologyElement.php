<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feMorphology> SVG filter primitive.
 *
 * "Fattens" or "thins" artwork using dilation and erosion operators.
 *
 * Common uses:
 * - Thickening/thinning text or shapes
 * - Creating outline effects
 * - Preprocessing for other effects
 *
 * Example:
 * <feMorphology operator="dilate" radius="2"/>
 * <feMorphology operator="erode" radius="1"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feMorphologyElement
 */
final class FeMorphologyElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feMorphology');
    }

    /**
     * Set the morphology operator.
     *
     * - erode: Thins the source graphic
     * - dilate: Fattens the source graphic
     */
    public function setOperator(string $operator): static
    {
        $this->setAttribute('operator', $operator);

        return $this;
    }

    /**
     * Get the morphology operator.
     */
    public function getOperator(): ?string
    {
        return $this->getAttribute('operator');
    }

    /**
     * Set the radius (single value or x/y pair).
     *
     * Indicates the radius (radii) for the operation.
     * If two numbers are provided, the first represents the x-radius, second the y-radius.
     */
    public function setRadius(string|int|float $radius): static
    {
        $this->setAttribute('radius', (string) $radius);

        return $this;
    }

    /**
     * Get the radius.
     */
    public function getRadius(): ?string
    {
        return $this->getAttribute('radius');
    }
}
