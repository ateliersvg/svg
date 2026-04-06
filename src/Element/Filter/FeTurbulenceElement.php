<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feTurbulence> SVG filter primitive.
 *
 * Creates image using Perlin turbulence function. Useful for creating textures,
 * clouds, marble, and other organic patterns.
 *
 * Example:
 * <feTurbulence type="turbulence" baseFrequency="0.05" numOctaves="2"/>
 * <feTurbulence type="fractalNoise" baseFrequency="0.1"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feTurbulenceElement
 */
final class FeTurbulenceElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feTurbulence');
    }

    public function setBaseFrequency(string|float $frequency): static
    {
        $this->setAttribute('baseFrequency', (string) $frequency);

        return $this;
    }

    public function getBaseFrequency(): ?string
    {
        return $this->getAttribute('baseFrequency');
    }

    public function setNumOctaves(string|int $octaves): static
    {
        $this->setAttribute('numOctaves', (string) $octaves);

        return $this;
    }

    public function getNumOctaves(): ?string
    {
        return $this->getAttribute('numOctaves');
    }

    public function setType(string $type): static
    {
        $this->setAttribute('type', $type);

        return $this;
    }

    public function getType(): ?string
    {
        return $this->getAttribute('type');
    }

    public function setSeed(string|int $seed): static
    {
        $this->setAttribute('seed', (string) $seed);

        return $this;
    }

    public function getSeed(): ?string
    {
        return $this->getAttribute('seed');
    }
}
