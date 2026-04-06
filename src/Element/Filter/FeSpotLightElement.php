<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents the <feSpotLight> SVG filter primitive.
 *
 * Defines a spotlight source that can be positioned and aimed in 3D space.
 * Used as a child of feDiffuseLighting or feSpecularLighting.
 *
 * Example:
 * <feDiffuseLighting>
 *   <feSpotLight x="100" y="100" z="50" pointsAtX="0" pointsAtY="0" pointsAtZ="0"
 *                specularExponent="1" limitingConeAngle="45"/>
 * </feDiffuseLighting>
 *
 * @see https://www.w3.org/TR/SVG11/filters.html#feSpotLightElement
 */
final class FeSpotLightElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('feSpotLight');
    }

    /**
     * Sets the x-coordinate of the spotlight position.
     *
     * @param string|int|float $x The x coordinate
     */
    public function setX(string|int|float $x): static
    {
        $this->setAttribute('x', (string) $x);

        return $this;
    }

    /**
     * Gets the x-coordinate of the spotlight position.
     *
     * @return string|null The x coordinate, or null if not set
     */
    public function getX(): ?string
    {
        return $this->getAttribute('x');
    }

    /**
     * Sets the y-coordinate of the spotlight position.
     *
     * @param string|int|float $y The y coordinate
     */
    public function setY(string|int|float $y): static
    {
        $this->setAttribute('y', (string) $y);

        return $this;
    }

    /**
     * Gets the y-coordinate of the spotlight position.
     *
     * @return string|null The y coordinate, or null if not set
     */
    public function getY(): ?string
    {
        return $this->getAttribute('y');
    }

    /**
     * Sets the z-coordinate of the spotlight position.
     *
     * @param string|int|float $z The z coordinate
     */
    public function setZ(string|int|float $z): static
    {
        $this->setAttribute('z', (string) $z);

        return $this;
    }

    /**
     * Gets the z-coordinate of the spotlight position.
     *
     * @return string|null The z coordinate, or null if not set
     */
    public function getZ(): ?string
    {
        return $this->getAttribute('z');
    }

    /**
     * Sets the x-coordinate of the point the spotlight is pointing at.
     *
     * @param string|int|float $pointsAtX The x coordinate
     */
    public function setPointsAtX(string|int|float $pointsAtX): static
    {
        $this->setAttribute('pointsAtX', (string) $pointsAtX);

        return $this;
    }

    /**
     * Gets the x-coordinate of the point the spotlight is pointing at.
     *
     * @return string|null The pointsAtX coordinate, or null if not set
     */
    public function getPointsAtX(): ?string
    {
        return $this->getAttribute('pointsAtX');
    }

    /**
     * Sets the y-coordinate of the point the spotlight is pointing at.
     *
     * @param string|int|float $pointsAtY The y coordinate
     */
    public function setPointsAtY(string|int|float $pointsAtY): static
    {
        $this->setAttribute('pointsAtY', (string) $pointsAtY);

        return $this;
    }

    /**
     * Gets the y-coordinate of the point the spotlight is pointing at.
     *
     * @return string|null The pointsAtY coordinate, or null if not set
     */
    public function getPointsAtY(): ?string
    {
        return $this->getAttribute('pointsAtY');
    }

    /**
     * Sets the z-coordinate of the point the spotlight is pointing at.
     *
     * @param string|int|float $pointsAtZ The z coordinate
     */
    public function setPointsAtZ(string|int|float $pointsAtZ): static
    {
        $this->setAttribute('pointsAtZ', (string) $pointsAtZ);

        return $this;
    }

    /**
     * Gets the z-coordinate of the point the spotlight is pointing at.
     *
     * @return string|null The pointsAtZ coordinate, or null if not set
     */
    public function getPointsAtZ(): ?string
    {
        return $this->getAttribute('pointsAtZ');
    }

    /**
     * Sets the specular exponent (focus of the spotlight, 1-128).
     *
     * @param string|int|float $specularExponent The specular exponent
     */
    public function setSpecularExponent(string|int|float $specularExponent): static
    {
        $this->setAttribute('specularExponent', (string) $specularExponent);

        return $this;
    }

    /**
     * Gets the specular exponent.
     *
     * @return string|null The specular exponent, or null if not set
     */
    public function getSpecularExponent(): ?string
    {
        return $this->getAttribute('specularExponent');
    }

    /**
     * Sets the limiting cone angle in degrees.
     *
     * @param string|int|float $limitingConeAngle The cone angle (0-90 degrees)
     */
    public function setLimitingConeAngle(string|int|float $limitingConeAngle): static
    {
        $this->setAttribute('limitingConeAngle', (string) $limitingConeAngle);

        return $this;
    }

    /**
     * Gets the limiting cone angle.
     *
     * @return string|null The limiting cone angle, or null if not set
     */
    public function getLimitingConeAngle(): ?string
    {
        return $this->getAttribute('limitingConeAngle');
    }

    /**
     * Sets the position of the spotlight in 3D space.
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

    /**
     * Sets the point where the spotlight is aimed.
     *
     * @param string|int|float $x The x coordinate
     * @param string|int|float $y The y coordinate
     * @param string|int|float $z The z coordinate
     */
    public function setPointsAt(
        string|int|float $x,
        string|int|float $y,
        string|int|float $z,
    ): static {
        $this->setPointsAtX($x);
        $this->setPointsAtY($y);
        $this->setPointsAtZ($z);

        return $this;
    }
}
