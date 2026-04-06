<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feDisplacementMap> SVG filter primitive.
 *
 * Uses the pixel values from one input to spatially displace another input.
 * Creates distortion effects.
 *
 * Example:
 * <feDisplacementMap in="SourceGraphic" in2="displacement" scale="20"
 *                    xChannelSelector="R" yChannelSelector="G"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feDisplacementMapElement
 */
final class FeDisplacementMapElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feDisplacementMap');
    }

    public function setIn2(string $in2): static
    {
        $this->setAttribute('in2', $in2);

        return $this;
    }

    public function getIn2(): ?string
    {
        return $this->getAttribute('in2');
    }

    public function setScaleAttribute(string|int|float $scale): static
    {
        $this->setAttribute('scale', (string) $scale);

        return $this;
    }

    public function getScaleAttribute(): ?string
    {
        return $this->getAttribute('scale');
    }

    public function setXChannelSelector(string $channel): static
    {
        $this->setAttribute('xChannelSelector', $channel);

        return $this;
    }

    public function getXChannelSelector(): ?string
    {
        return $this->getAttribute('xChannelSelector');
    }

    public function setYChannelSelector(string $channel): static
    {
        $this->setAttribute('yChannelSelector', $channel);

        return $this;
    }

    public function getYChannelSelector(): ?string
    {
        return $this->getAttribute('yChannelSelector');
    }
}
