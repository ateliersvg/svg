<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Shape;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <ellipse> element.
 *
 * The ellipse element defines an ellipse which is aligned with the current user coordinate system
 * based on a center point and two radii.
 *
 * @see https://www.w3.org/TR/SVG11/shapes.html#EllipseElement
 */
final class EllipseElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('ellipse');
    }

    /**
     * Creates a new ellipse with the given center and radii.
     *
     * @param string|int|float $cx The x coordinate of the center
     * @param string|int|float $cy The y coordinate of the center
     * @param string|int|float $rx The x-axis radius
     * @param string|int|float $ry The y-axis radius
     */
    public static function create(
        string|int|float $cx,
        string|int|float $cy,
        string|int|float $rx,
        string|int|float $ry,
    ): static {
        return (new static())->setCx($cx)->setCy($cy)->setRx($rx)->setRy($ry);
    }

    /**
     * Sets the x-axis coordinate of the center of the ellipse.
     *
     * @param string|int|float $cx The x coordinate
     */
    public function setCx(string|int|float $cx): static
    {
        $this->setAttribute('cx', (string) $cx);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the center of the ellipse.
     *
     * @return Length|null The x coordinate as a Length object, or null if not set
     */
    public function getCx(): ?Length
    {
        $value = $this->getAttribute('cx');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the center of the ellipse.
     *
     * @param string|int|float $cy The y coordinate
     */
    public function setCy(string|int|float $cy): static
    {
        $this->setAttribute('cy', (string) $cy);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the center of the ellipse.
     *
     * @return Length|null The y coordinate as a Length object, or null if not set
     */
    public function getCy(): ?Length
    {
        $value = $this->getAttribute('cy');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the x-axis radius of the ellipse.
     *
     * @param string|int|float $rx The x-axis radius (must be positive)
     */
    public function setRx(string|int|float $rx): static
    {
        $this->setAttribute('rx', (string) $rx);

        return $this;
    }

    /**
     * Gets the x-axis radius of the ellipse.
     *
     * @return Length|null The x-axis radius as a Length object, or null if not set
     */
    public function getRx(): ?Length
    {
        $value = $this->getAttribute('rx');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis radius of the ellipse.
     *
     * @param string|int|float $ry The y-axis radius (must be positive)
     */
    public function setRy(string|int|float $ry): static
    {
        $this->setAttribute('ry', (string) $ry);

        return $this;
    }

    /**
     * Gets the y-axis radius of the ellipse.
     *
     * @return Length|null The y-axis radius as a Length object, or null if not set
     */
    public function getRy(): ?Length
    {
        $value = $this->getAttribute('ry');

        return null !== $value ? Length::parse($value) : null;
    }
}
