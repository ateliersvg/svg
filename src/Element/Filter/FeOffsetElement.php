<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feOffset> SVG filter primitive.
 *
 * This filter primitive offsets the input image relative to its current position
 * in the image space by the specified vector.
 *
 * Primary use case: Creating drop shadows by offsetting a blurred alpha channel.
 *
 * Example:
 * <feOffset in="SourceAlpha" dx="4" dy="4" result="offsetBlur"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feOffsetElement
 */
final class FeOffsetElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feOffset');
    }

    /**
     * Set the x-axis offset.
     *
     * Positive values shift the image to the right.
     * Negative values shift it to the left.
     */
    public function setDx(string|int|float $dx): static
    {
        $this->setAttribute('dx', (string) $dx);

        return $this;
    }

    /**
     * Get the x-axis offset.
     */
    public function getDx(): ?string
    {
        return $this->getAttribute('dx');
    }

    /**
     * Set the y-axis offset.
     *
     * Positive values shift the image down.
     * Negative values shift it up.
     */
    public function setDy(string|int|float $dy): static
    {
        $this->setAttribute('dy', (string) $dy);

        return $this;
    }

    /**
     * Get the y-axis offset.
     */
    public function getDy(): ?string
    {
        return $this->getAttribute('dy');
    }
}
