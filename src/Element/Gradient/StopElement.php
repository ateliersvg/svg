<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Gradient;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents an SVG <stop> element.
 *
 * The stop element defines a color and position for a gradient.
 * It is used within linearGradient and radialGradient elements.
 *
 * @see https://www.w3.org/TR/SVG11/pservers.html#StopElement
 */
final class StopElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('stop');
    }

    /**
     * Sets the offset of the gradient stop.
     *
     * @param string|int|float $offset The offset (0 to 1 or percentage)
     */
    public function setOffset(string|int|float $offset): static
    {
        $this->setAttribute('offset', (string) $offset);

        return $this;
    }

    /**
     * Gets the offset of the gradient stop.
     *
     * @return string|null The offset value, or null if not set
     */
    public function getOffset(): ?string
    {
        return $this->getAttribute('offset');
    }

    /**
     * Sets the color of the gradient stop.
     *
     * @param string $stopColor The stop color
     */
    public function setStopColor(string $stopColor): static
    {
        $this->setAttribute('stop-color', $stopColor);

        return $this;
    }

    /**
     * Gets the color of the gradient stop.
     *
     * @return string|null The stop color, or null if not set
     */
    public function getStopColor(): ?string
    {
        return $this->getAttribute('stop-color');
    }

    /**
     * Sets the opacity of the gradient stop.
     *
     * @param string|int|float $stopOpacity The stop opacity (0 to 1)
     */
    public function setStopOpacity(string|int|float $stopOpacity): static
    {
        $this->setAttribute('stop-opacity', (string) $stopOpacity);

        return $this;
    }

    /**
     * Gets the opacity of the gradient stop.
     *
     * @return string|null The stop opacity, or null if not set
     */
    public function getStopOpacity(): ?string
    {
        return $this->getAttribute('stop-opacity');
    }
}
