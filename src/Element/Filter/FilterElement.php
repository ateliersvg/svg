<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractContainerElement;

/**
 * Represents the <filter> SVG element.
 *
 * Container for filter primitives that define a filter effect.
 * Can be referenced by elements using the filter attribute.
 *
 * Example:
 * <filter id="blur">
 *   <feGaussianBlur in="SourceGraphic" stdDeviation="5"/>
 * </filter>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#FilterElement
 */
final class FilterElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('filter');
    }

    /**
     * Set the filter region x coordinate.
     */
    public function setX(string|int|float $x): static
    {
        $this->setAttribute('x', (string) $x);

        return $this;
    }

    /**
     * Get the filter region x coordinate.
     */
    public function getX(): ?string
    {
        return $this->getAttribute('x');
    }

    /**
     * Set the filter region y coordinate.
     */
    public function setY(string|int|float $y): static
    {
        $this->setAttribute('y', (string) $y);

        return $this;
    }

    /**
     * Get the filter region y coordinate.
     */
    public function getY(): ?string
    {
        return $this->getAttribute('y');
    }

    /**
     * Set the filter region width.
     */
    public function setWidth(string|int|float $width): static
    {
        $this->setAttribute('width', (string) $width);

        return $this;
    }

    /**
     * Get the filter region width.
     */
    public function getWidth(): ?string
    {
        return $this->getAttribute('width');
    }

    /**
     * Set the filter region height.
     */
    public function setHeight(string|int|float $height): static
    {
        $this->setAttribute('height', (string) $height);

        return $this;
    }

    /**
     * Get the filter region height.
     */
    public function getHeight(): ?string
    {
        return $this->getAttribute('height');
    }

    /**
     * Set the coordinate system for x, y, width, height.
     *
     * - userSpaceOnUse: Values in current user coordinate system
     * - objectBoundingBox: Values as fractions/percentages of bounding box (default)
     */
    public function setFilterUnits(string $filterUnits): static
    {
        $this->setAttribute('filterUnits', $filterUnits);

        return $this;
    }

    /**
     * Get the filterUnits value.
     */
    public function getFilterUnits(): ?string
    {
        return $this->getAttribute('filterUnits');
    }

    /**
     * Set the coordinate system for filter primitive subregion.
     *
     * - userSpaceOnUse: Values in current user coordinate system (default)
     * - objectBoundingBox: Values as fractions/percentages of bounding box
     */
    public function setPrimitiveUnits(string $primitiveUnits): static
    {
        $this->setAttribute('primitiveUnits', $primitiveUnits);

        return $this;
    }

    /**
     * Get the primitiveUnits value.
     */
    public function getPrimitiveUnits(): ?string
    {
        return $this->getAttribute('primitiveUnits');
    }

    /**
     * Set reference to another filter element.
     */
    public function setHref(string $href): static
    {
        $this->setAttribute('href', $href);

        return $this;
    }

    /**
     * Get the href reference.
     */
    public function getHref(): ?string
    {
        return $this->getAttribute('href');
    }
}
