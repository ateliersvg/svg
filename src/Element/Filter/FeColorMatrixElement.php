<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feColorMatrix> SVG filter primitive.
 *
 * This filter primitive applies a matrix transformation on the RGBA values
 * of every pixel to produce a result with a new set of RGBA values.
 *
 * Common uses:
 * - Converting to grayscale
 * - Adjusting saturation
 * - Hue rotation
 * - Color channel manipulation
 *
 * Example:
 * <feColorMatrix type="saturate" values="0.5"/>
 * <feColorMatrix type="hueRotate" values="90"/>
 * <feColorMatrix type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 1 0"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feColorMatrixElement
 */
final class FeColorMatrixElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feColorMatrix');
    }

    /**
     * Set the type of matrix operation.
     *
     * - matrix: Apply a custom 5x4 matrix
     * - saturate: Adjust color saturation (0 = grayscale, 1 = unchanged, >1 = more saturated)
     * - hueRotate: Rotate colors around the color wheel (in degrees)
     * - luminanceToAlpha: Convert luminance to alpha channel
     */
    public function setType(string $type): static
    {
        $this->setAttribute('type', $type);

        return $this;
    }

    /**
     * Get the type of matrix operation.
     */
    public function getType(): ?string
    {
        return $this->getAttribute('type');
    }

    /**
     * Set the values for the matrix operation.
     *
     * For type="matrix": 20 values (4x5 matrix in row-major order)
     * For type="saturate": 1 value (0 to 1+, default 1)
     * For type="hueRotate": 1 value (angle in degrees)
     * For type="luminanceToAlpha": not used
     *
     * @param string $values Space or comma-separated values
     */
    public function setValues(string $values): static
    {
        $this->setAttribute('values', $values);

        return $this;
    }

    /**
     * Get the values.
     */
    public function getValues(): ?string
    {
        return $this->getAttribute('values');
    }
}
