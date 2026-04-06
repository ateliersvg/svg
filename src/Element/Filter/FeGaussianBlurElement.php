<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feGaussianBlur> SVG filter primitive.
 *
 * This filter primitive performs a Gaussian blur on the input image.
 * The Gaussian blur kernel is an approximation of the normalized two-dimensional
 * Gaussian function.
 *
 * Most common usage: creating drop shadows and blur effects.
 *
 * Example:
 * <feGaussianBlur in="SourceGraphic" stdDeviation="5"/>
 * <feGaussianBlur in="SourceAlpha" stdDeviation="3" result="blur"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feGaussianBlurElement
 */
final class FeGaussianBlurElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feGaussianBlur');
    }

    /**
     * Set the standard deviation for the blur operation.
     *
     * If two numbers are provided (space or comma separated), the first represents
     * the standard deviation value along the x-axis, the second along the y-axis.
     * If one number is provided, it is used for both axes.
     *
     * Larger values create more blur. Typical values: 1-10.
     *
     * @param string|int|float $stdDeviation Standard deviation (single value or "x y" pair)
     */
    public function setStdDeviation(string|int|float $stdDeviation): static
    {
        $this->setAttribute('stdDeviation', (string) $stdDeviation);

        return $this;
    }

    /**
     * Get the standard deviation.
     */
    public function getStdDeviation(): ?string
    {
        return $this->getAttribute('stdDeviation');
    }

    /**
     * Set the edge mode (duplicate, wrap, or none).
     *
     * Determines how to extend the input image as necessary with color values
     * so that the matrix operations can be applied when the kernel is positioned
     * at or near the edge of the input image.
     *
     * - duplicate: Use the nearest edge color (default)
     * - wrap: Wrap the image
     * - none: Use transparent black
     */
    public function setEdgeMode(string $edgeMode): static
    {
        $this->setAttribute('edgeMode', $edgeMode);

        return $this;
    }

    /**
     * Get the edge mode.
     */
    public function getEdgeMode(): ?string
    {
        return $this->getAttribute('edgeMode');
    }
}
