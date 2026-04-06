<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents the <feDistantLight> SVG filter primitive.
 *
 * Defines a distant light source (like the sun) with parallel light rays.
 * The light direction is defined by azimuth and elevation angles.
 * Used as a child of feDiffuseLighting or feSpecularLighting.
 *
 * Example:
 * <feDiffuseLighting>
 *   <feDistantLight azimuth="45" elevation="60"/>
 * </feDiffuseLighting>
 *
 * @see https://www.w3.org/TR/SVG11/filters.html#feDistantLightElement
 */
final class FeDistantLightElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('feDistantLight');
    }

    /**
     * Sets the azimuth angle (direction on the XY plane).
     *
     * The azimuth is measured in degrees counter-clockwise from the positive X axis.
     *
     * @param string|int|float $azimuth The azimuth angle in degrees
     */
    public function setAzimuth(string|int|float $azimuth): static
    {
        $this->setAttribute('azimuth', (string) $azimuth);

        return $this;
    }

    /**
     * Gets the azimuth angle.
     *
     * @return string|null The azimuth angle, or null if not set
     */
    public function getAzimuth(): ?string
    {
        return $this->getAttribute('azimuth');
    }

    /**
     * Sets the elevation angle (direction from the XY plane).
     *
     * The elevation is measured in degrees above the XY plane (0-90 degrees).
     *
     * @param string|int|float $elevation The elevation angle in degrees
     */
    public function setElevation(string|int|float $elevation): static
    {
        $this->setAttribute('elevation', (string) $elevation);

        return $this;
    }

    /**
     * Gets the elevation angle.
     *
     * @return string|null The elevation angle, or null if not set
     */
    public function getElevation(): ?string
    {
        return $this->getAttribute('elevation');
    }

    /**
     * Sets both azimuth and elevation angles for the light direction.
     *
     * @param string|int|float $azimuth   The azimuth angle in degrees
     * @param string|int|float $elevation The elevation angle in degrees
     */
    public function setDirection(
        string|int|float $azimuth,
        string|int|float $elevation,
    ): static {
        $this->setAzimuth($azimuth);
        $this->setElevation($elevation);

        return $this;
    }
}
