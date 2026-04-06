<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractContainerElement;

/**
 * Represents the <feSpecularLighting> SVG filter primitive.
 *
 * Lights a source graphic using the alpha channel as a bump map.
 * Creates specular highlights. Contains feDistantLight, fePointLight, or feSpotLight.
 *
 * Example:
 * <feSpecularLighting surfaceScale="5" specularConstant="1"
 *                     specularExponent="20" lighting-color="white">
 *   <fePointLight x="100" y="100" z="200"/>
 * </feSpecularLighting>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feSpecularLightingElement
 */
final class FeSpecularLightingElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('feSpecularLighting');
    }

    /**
     * Set the surface scale.
     *
     * Height of the surface when the alpha channel value is 1.
     */
    public function setSurfaceScale(string|int|float $surfaceScale): static
    {
        $this->setAttribute('surfaceScale', (string) $surfaceScale);

        return $this;
    }

    /**
     * Get the surface scale.
     */
    public function getSurfaceScale(): ?string
    {
        return $this->getAttribute('surfaceScale');
    }

    /**
     * Set the specular constant (ks).
     *
     * Represents the ratio of reflection of the specular lighting.
     */
    public function setSpecularConstant(string|int|float $specularConstant): static
    {
        $this->setAttribute('specularConstant', (string) $specularConstant);

        return $this;
    }

    /**
     * Get the specular constant.
     */
    public function getSpecularConstant(): ?string
    {
        return $this->getAttribute('specularConstant');
    }

    /**
     * Set the specular exponent.
     *
     * Controls the focus for the light source. Larger values = more focused.
     */
    public function setSpecularExponent(string|int|float $specularExponent): static
    {
        $this->setAttribute('specularExponent', (string) $specularExponent);

        return $this;
    }

    /**
     * Get the specular exponent.
     */
    public function getSpecularExponent(): ?string
    {
        return $this->getAttribute('specularExponent');
    }

    /**
     * Set the lighting color.
     */
    public function setLightingColor(string $lightingColor): static
    {
        $this->setAttribute('lighting-color', $lightingColor);

        return $this;
    }

    /**
     * Get the lighting color.
     */
    public function getLightingColor(): ?string
    {
        return $this->getAttribute('lighting-color');
    }

    /**
     * Set the input for the primitive.
     */
    public function setIn(string $in): static
    {
        $this->setAttribute('in', $in);

        return $this;
    }

    /**
     * Get the input.
     */
    public function getIn(): ?string
    {
        return $this->getAttribute('in');
    }

    /**
     * Set the result identifier.
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
}
