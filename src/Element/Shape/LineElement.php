<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Shape;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <line> element.
 *
 * The line element defines a line segment that starts at one point and ends at another.
 *
 * @see https://www.w3.org/TR/SVG11/shapes.html#LineElement
 */
final class LineElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('line');
    }

    /**
     * Creates a new line from one point to another.
     *
     * @param string|int|float $x1 The x coordinate of the start point
     * @param string|int|float $y1 The y coordinate of the start point
     * @param string|int|float $x2 The x coordinate of the end point
     * @param string|int|float $y2 The y coordinate of the end point
     */
    public static function create(
        string|int|float $x1,
        string|int|float $y1,
        string|int|float $x2,
        string|int|float $y2,
    ): static {
        return (new static())->setX1($x1)->setY1($y1)->setX2($x2)->setY2($y2);
    }

    /**
     * Sets the x-axis coordinate of the start of the line.
     *
     * @param string|int|float $x1 The x coordinate of the start point
     */
    public function setX1(string|int|float $x1): static
    {
        $this->setAttribute('x1', (string) $x1);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the start of the line.
     *
     * @return Length|null The x coordinate of the start point as a Length object, or null if not set
     */
    public function getX1(): ?Length
    {
        $value = $this->getAttribute('x1');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the start of the line.
     *
     * @param string|int|float $y1 The y coordinate of the start point
     */
    public function setY1(string|int|float $y1): static
    {
        $this->setAttribute('y1', (string) $y1);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the start of the line.
     *
     * @return Length|null The y coordinate of the start point as a Length object, or null if not set
     */
    public function getY1(): ?Length
    {
        $value = $this->getAttribute('y1');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the x-axis coordinate of the end of the line.
     *
     * @param string|int|float $x2 The x coordinate of the end point
     */
    public function setX2(string|int|float $x2): static
    {
        $this->setAttribute('x2', (string) $x2);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the end of the line.
     *
     * @return Length|null The x coordinate of the end point as a Length object, or null if not set
     */
    public function getX2(): ?Length
    {
        $value = $this->getAttribute('x2');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the end of the line.
     *
     * @param string|int|float $y2 The y coordinate of the end point
     */
    public function setY2(string|int|float $y2): static
    {
        $this->setAttribute('y2', (string) $y2);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the end of the line.
     *
     * @return Length|null The y coordinate of the end point as a Length object, or null if not set
     */
    public function getY2(): ?Length
    {
        $value = $this->getAttribute('y2');

        return null !== $value ? Length::parse($value) : null;
    }
}
