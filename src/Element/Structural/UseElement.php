<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Structural;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <use> element.
 *
 * The <use> element takes nodes from within the SVG document, and duplicates them
 * somewhere else. The effect is the same as if the nodes were deeply cloned into a
 * non-exposed DOM, then pasted where the use element is.
 *
 * @see https://www.w3.org/TR/SVG11/struct.html#UseElement
 */
final class UseElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('use');
    }

    /**
     * Sets the href attribute (or xlink:href for backwards compatibility).
     *
     * @param string $href The reference to the element to use
     */
    public function setHref(string $href): static
    {
        // Use href (SVG 2.0) by default, but support xlink:href for legacy
        $this->setAttribute('href', $href);

        return $this;
    }

    /**
     * Gets the href attribute.
     *
     * @return string|null The href value, checking both href and xlink:href
     */
    public function getHref(): ?string
    {
        return $this->getAttribute('href') ?? $this->getAttribute('xlink:href');
    }

    /**
     * Sets the x-axis coordinate.
     *
     * @param string|int|float $x The x coordinate
     */
    public function setX(string|int|float $x): static
    {
        $this->setAttribute('x', (string) $x);

        return $this;
    }

    /**
     * Gets the x-axis coordinate.
     *
     * @return Length|null The x coordinate as a Length object, or null if not set
     */
    public function getX(): ?Length
    {
        $value = $this->getAttribute('x');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate.
     *
     * @param string|int|float $y The y coordinate
     */
    public function setY(string|int|float $y): static
    {
        $this->setAttribute('y', (string) $y);

        return $this;
    }

    /**
     * Gets the y-axis coordinate.
     *
     * @return Length|null The y coordinate as a Length object, or null if not set
     */
    public function getY(): ?Length
    {
        $value = $this->getAttribute('y');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the width.
     *
     * @param string|int|float $width The width
     */
    public function setWidth(string|int|float $width): static
    {
        $this->setAttribute('width', (string) $width);

        return $this;
    }

    /**
     * Gets the width.
     *
     * @return Length|null The width as a Length object, or null if not set
     */
    public function getWidth(): ?Length
    {
        $value = $this->getAttribute('width');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the height.
     *
     * @param string|int|float $height The height
     */
    public function setHeight(string|int|float $height): static
    {
        $this->setAttribute('height', (string) $height);

        return $this;
    }

    /**
     * Gets the height.
     *
     * @return Length|null The height as a Length object, or null if not set
     */
    public function getHeight(): ?Length
    {
        $value = $this->getAttribute('height');

        return null !== $value ? Length::parse($value) : null;
    }
}
