<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Shape;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <rect> element.
 *
 * The rect element defines a rectangle which is axis-aligned with the current user coordinate system.
 * Rounded rectangles can be achieved by setting the rx and ry attributes.
 *
 * @see https://www.w3.org/TR/SVG11/shapes.html#RectElement
 */
final class RectElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('rect');
    }

    /**
     * Creates a new rectangle with the given position and dimensions.
     *
     * @param string|int|float $x      The x coordinate
     * @param string|int|float $y      The y coordinate
     * @param string|int|float $width  The width
     * @param string|int|float $height The height
     */
    public static function create(
        string|int|float $x,
        string|int|float $y,
        string|int|float $width,
        string|int|float $height,
    ): static {
        return (new static())->setX($x)->setY($y)->setWidth($width)->setHeight($height);
    }

    /**
     * Sets the x-axis coordinate of the side of the rectangle which has the smaller x-axis value.
     *
     * @param string|int|float $x The x coordinate
     */
    public function setX(string|int|float $x): static
    {
        $this->setAttribute('x', (string) $x);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the side of the rectangle which has the smaller x-axis value.
     *
     * @return Length|null The x coordinate as a Length object, or null if not set
     */
    public function getX(): ?Length
    {
        $value = $this->getAttribute('x');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the side of the rectangle which has the smaller y-axis value.
     *
     * @param string|int|float $y The y coordinate
     */
    public function setY(string|int|float $y): static
    {
        $this->setAttribute('y', (string) $y);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the side of the rectangle which has the smaller y-axis value.
     *
     * @return Length|null The y coordinate as a Length object, or null if not set
     */
    public function getY(): ?Length
    {
        $value = $this->getAttribute('y');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the width of the rectangle.
     *
     * @param string|int|float $width The width
     */
    public function setWidth(string|int|float $width): static
    {
        $this->setAttribute('width', (string) $width);

        return $this;
    }

    /**
     * Gets the width of the rectangle.
     *
     * @return Length|null The width as a Length object, or null if not set
     */
    public function getWidth(): ?Length
    {
        $value = $this->getAttribute('width');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the height of the rectangle.
     *
     * @param string|int|float $height The height
     */
    public function setHeight(string|int|float $height): static
    {
        $this->setAttribute('height', (string) $height);

        return $this;
    }

    /**
     * Gets the height of the rectangle.
     *
     * @return Length|null The height as a Length object, or null if not set
     */
    public function getHeight(): ?Length
    {
        $value = $this->getAttribute('height');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the x-axis radius for rounded corners.
     *
     * @param string|int|float $rx The x-axis radius
     */
    public function setRx(string|int|float $rx): static
    {
        $this->setAttribute('rx', (string) $rx);

        return $this;
    }

    /**
     * Gets the x-axis radius for rounded corners.
     *
     * @return Length|null The x-axis radius as a Length object, or null if not set
     */
    public function getRx(): ?Length
    {
        $value = $this->getAttribute('rx');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis radius for rounded corners.
     *
     * @param string|int|float $ry The y-axis radius
     */
    public function setRy(string|int|float $ry): static
    {
        $this->setAttribute('ry', (string) $ry);

        return $this;
    }

    /**
     * Gets the y-axis radius for rounded corners.
     *
     * @return Length|null The y-axis radius as a Length object, or null if not set
     */
    public function getRy(): ?Length
    {
        $value = $this->getAttribute('ry');

        return null !== $value ? Length::parse($value) : null;
    }
}
