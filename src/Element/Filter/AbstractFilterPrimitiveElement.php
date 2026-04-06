<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractElement;

/**
 * Base class for SVG filter primitive elements.
 *
 * Filter primitives are the building blocks of filter effects.
 * All filter primitives (feGaussianBlur, feOffset, etc.) extend this class.
 *
 * Common attributes:
 * - result: Names the output for use by other primitives
 * - in: Specifies the input graphic (SourceGraphic, SourceAlpha, or result of another primitive)
 * - x, y, width, height: Subregion for the effect
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#FilterPrimitivesOverview
 */
abstract class AbstractFilterPrimitiveElement extends AbstractElement
{
    /**
     * Set the result identifier.
     * This names the output of this filter primitive so other primitives can use it.
     */
    public function setResult(string $result): static
    {
        $this->setAttribute('result', $result);

        return $this;
    }

    /**
     * Get the result identifier.
     */
    public function getResult(): ?string
    {
        return $this->getAttribute('result');
    }

    /**
     * Set the input source.
     * Can be: SourceGraphic, SourceAlpha, BackgroundImage, BackgroundAlpha, FillPaint, StrokePaint,
     * or a result from another filter primitive.
     */
    public function setIn(string $in): static
    {
        $this->setAttribute('in', $in);

        return $this;
    }

    /**
     * Get the input source.
     */
    public function getIn(): ?string
    {
        return $this->getAttribute('in');
    }

    /**
     * Set the x coordinate of the filter primitive subregion.
     */
    public function setX(string|int|float $x): static
    {
        $this->setAttribute('x', (string) $x);

        return $this;
    }

    /**
     * Get the x coordinate.
     */
    public function getX(): ?string
    {
        return $this->getAttribute('x');
    }

    /**
     * Set the y coordinate of the filter primitive subregion.
     */
    public function setY(string|int|float $y): static
    {
        $this->setAttribute('y', (string) $y);

        return $this;
    }

    /**
     * Get the y coordinate.
     */
    public function getY(): ?string
    {
        return $this->getAttribute('y');
    }

    /**
     * Set the width of the filter primitive subregion.
     */
    public function setWidth(string|int|float $width): static
    {
        $this->setAttribute('width', (string) $width);

        return $this;
    }

    /**
     * Get the width.
     */
    public function getWidth(): ?string
    {
        return $this->getAttribute('width');
    }

    /**
     * Set the height of the filter primitive subregion.
     */
    public function setHeight(string|int|float $height): static
    {
        $this->setAttribute('height', (string) $height);

        return $this;
    }

    /**
     * Get the height.
     */
    public function getHeight(): ?string
    {
        return $this->getAttribute('height');
    }
}
