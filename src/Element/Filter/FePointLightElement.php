<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents the <fePointLight> SVG filter primitive.
 *
 * Defines a point light source that can be positioned in 3D space.
 * Used as a child of feDiffuseLighting or feSpecularLighting.
 *
 * Example:
 * <feDiffuseLighting>
 *   <fePointLight x="100" y="100" z="50"/>
 * </feDiffuseLighting>
 *
 * @see https://www.w3.org/TR/SVG11/filters.html#fePointLightElement
 */
final class FePointLightElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('fePointLight');
    }

    /**
     * Sets the x-coordinate of the point light.
     *
     * @param string|int|float $x The x coordinate
     */
    public function setX(string|int|float $x): static
    {
        $this->setAttribute('x', (string) $x);

        return $this;
    }

    /**
     * Gets the x-coordinate of the point light.
     *
     * @return string|null The x coordinate, or null if not set
     */
    public function getX(): ?string
    {
        return $this->getAttribute('x');
    }

    /**
     * Sets the y-coordinate of the point light.
     *
     * @param string|int|float $y The y coordinate
     */
    public function setY(string|int|float $y): static
    {
        $this->setAttribute('y', (string) $y);

        return $this;
    }

    /**
     * Gets the y-coordinate of the point light.
     *
     * @return string|null The y coordinate, or null if not set
     */
    public function getY(): ?string
    {
        return $this->getAttribute('y');
    }

    /**
     * Sets the z-coordinate of the point light.
     *
     * @param string|int|float $z The z coordinate
     */
    public function setZ(string|int|float $z): static
    {
        $this->setAttribute('z', (string) $z);

        return $this;
    }

    /**
     * Gets the z-coordinate of the point light.
     *
     * @return string|null The z coordinate, or null if not set
     */
    public function getZ(): ?string
    {
        return $this->getAttribute('z');
    }

    /**
     * Sets the position of the point light in 3D space.
     *
     * @param string|int|float $x The x coordinate
     * @param string|int|float $y The y coordinate
     * @param string|int|float $z The z coordinate
     */
    public function setPosition(
        string|int|float $x,
        string|int|float $y,
        string|int|float $z,
    ): static {
        $this->setX($x);
        $this->setY($y);
        $this->setZ($z);

        return $this;
    }
}
