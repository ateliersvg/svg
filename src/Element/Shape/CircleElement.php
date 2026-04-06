<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Shape;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <circle> element.
 *
 * The circle element defines a circle based on a center point and a radius.
 *
 * @see https://www.w3.org/TR/SVG11/shapes.html#CircleElement
 */
final class CircleElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('circle');
    }

    /**
     * Creates a new circle with the given center and radius.
     *
     * @param string|int|float $cx The x coordinate of the center
     * @param string|int|float $cy The y coordinate of the center
     * @param string|int|float $r  The radius
     */
    public static function create(
        string|int|float $cx,
        string|int|float $cy,
        string|int|float $r,
    ): static {
        return (new static())->setCx($cx)->setCy($cy)->setR($r);
    }

    /**
     * Sets the x-axis coordinate of the center of the circle.
     *
     * @param string|int|float $cx The x coordinate
     */
    public function setCx(string|int|float $cx): static
    {
        $this->setAttribute('cx', (string) $cx);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the center of the circle.
     *
     * @return Length|null The x coordinate as a Length object, or null if not set
     */
    public function getCx(): ?Length
    {
        $value = $this->getAttribute('cx');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the center of the circle.
     *
     * @param string|int|float $cy The y coordinate
     */
    public function setCy(string|int|float $cy): static
    {
        $this->setAttribute('cy', (string) $cy);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the center of the circle.
     *
     * @return Length|null The y coordinate as a Length object, or null if not set
     */
    public function getCy(): ?Length
    {
        $value = $this->getAttribute('cy');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the radius of the circle.
     *
     * @param string|int|float $r The radius (must be positive)
     */
    public function setRadius(string|int|float $r): static
    {
        $this->setAttribute('r', (string) $r);

        return $this;
    }

    /**
     * Gets the radius of the circle.
     *
     * @return Length|null The radius as a Length object, or null if not set
     */
    public function getRadius(): ?Length
    {
        $value = $this->getAttribute('r');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Alias for setRadius() to match the attribute name.
     *
     * @param string|int|float $r The radius (must be positive)
     */
    public function setR(string|int|float $r): static
    {
        return $this->setRadius($r);
    }

    /**
     * Alias for getRadius() to match the attribute name.
     *
     * @return Length|null The radius as a Length object, or null if not set
     */
    public function getR(): ?Length
    {
        return $this->getRadius();
    }
}
