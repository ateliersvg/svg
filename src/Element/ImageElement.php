<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\PreserveAspectRatio;

/**
 * Represents an SVG <image> element.
 *
 * The <image> element includes images inside SVG documents. It can display raster
 * image files or other SVG files.
 *
 * @see https://www.w3.org/TR/SVG11/struct.html#ImageElement
 */
final class ImageElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('image');
    }

    /**
     * Sets the href attribute (image source).
     *
     * @param string $href The reference to the image
     */
    public function setHref(string $href): static
    {
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

    /**
     * Sets the preserveAspectRatio attribute.
     *
     * @param string|PreserveAspectRatio $preserveAspectRatio The preserveAspectRatio value
     */
    public function setPreserveAspectRatio(string|PreserveAspectRatio $preserveAspectRatio): static
    {
        if ($preserveAspectRatio instanceof PreserveAspectRatio) {
            $this->setAttribute('preserveAspectRatio', $preserveAspectRatio->toString());
        } else {
            // Validate by parsing
            $parsed = PreserveAspectRatio::parse($preserveAspectRatio);
            $this->setAttribute('preserveAspectRatio', $parsed->toString());
        }

        return $this;
    }

    /**
     * Gets the preserveAspectRatio attribute.
     *
     * @return PreserveAspectRatio|null The preserveAspectRatio as an object, or null if not set
     */
    public function getPreserveAspectRatio(): ?PreserveAspectRatio
    {
        $par = $this->getAttribute('preserveAspectRatio');

        return null !== $par ? PreserveAspectRatio::parse($par) : null;
    }
}
