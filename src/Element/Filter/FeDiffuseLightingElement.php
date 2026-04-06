<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractContainerElement;

/**
 * Represents the <feDiffuseLighting> SVG filter primitive.
 *
 * Lights an image using the alpha channel as a bump map. Can contain feDistantLight,
 * fePointLight, or feSpotLight child elements to define the light source.
 *
 * Example:
 * <feDiffuseLighting surfaceScale="5" diffuseConstant="1" lighting-color="white">
 *   <fePointLight x="100" y="100" z="50"/>
 * </feDiffuseLighting>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feDiffuseLightingElement
 */
final class FeDiffuseLightingElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('feDiffuseLighting');
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
     * Set the diffuse constant (kd).
     *
     * Reflects the ratio of the reflection of the diffuse lighting.
     */
    public function setDiffuseConstant(string|int|float $diffuseConstant): static
    {
        $this->setAttribute('diffuseConstant', (string) $diffuseConstant);

        return $this;
    }

    /**
     * Get the diffuse constant.
     */
    public function getDiffuseConstant(): ?string
    {
        return $this->getAttribute('diffuseConstant');
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
