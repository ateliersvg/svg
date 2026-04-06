<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Gradient;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\Viewbox;

/**
 * Represents an SVG <pattern> element.
 *
 * The pattern element defines a graphics object which can be redrawn at
 * repeated x and y coordinate intervals ("tiled") to cover an area.
 * The pattern is referenced by the fill and/or stroke properties of other
 * graphics elements to fill or stroke those elements with the pattern.
 *
 * @see https://www.w3.org/TR/SVG11/pservers.html#PatternElement
 */
final class PatternElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('pattern');
    }

    /**
     * Sets the x-axis coordinate of the pattern tile.
     *
     * @param string|int|float $x The x coordinate
     */
    public function setX(string|int|float $x): static
    {
        $this->setAttribute('x', (string) $x);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the pattern tile.
     *
     * @return Length|null The x coordinate as a Length object, or null if not set
     */
    public function getX(): ?Length
    {
        $value = $this->getAttribute('x');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the pattern tile.
     *
     * @param string|int|float $y The y coordinate
     */
    public function setY(string|int|float $y): static
    {
        $this->setAttribute('y', (string) $y);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the pattern tile.
     *
     * @return Length|null The y coordinate as a Length object, or null if not set
     */
    public function getY(): ?Length
    {
        $value = $this->getAttribute('y');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the width of the pattern tile.
     *
     * @param string|int|float $width The width
     */
    public function setWidth(string|int|float $width): static
    {
        $this->setAttribute('width', (string) $width);

        return $this;
    }

    /**
     * Gets the width of the pattern tile.
     *
     * @return Length|null The width as a Length object, or null if not set
     */
    public function getWidth(): ?Length
    {
        $value = $this->getAttribute('width');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the height of the pattern tile.
     *
     * @param string|int|float $height The height
     */
    public function setHeight(string|int|float $height): static
    {
        $this->setAttribute('height', (string) $height);

        return $this;
    }

    /**
     * Gets the height of the pattern tile.
     *
     * @return Length|null The height as a Length object, or null if not set
     */
    public function getHeight(): ?Length
    {
        $value = $this->getAttribute('height');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the coordinate system for attributes x, y, width, and height.
     *
     * @param string $patternUnits The units ('userSpaceOnUse' or 'objectBoundingBox')
     */
    public function setPatternUnits(string $patternUnits): static
    {
        $this->setAttribute('patternUnits', $patternUnits);

        return $this;
    }

    /**
     * Gets the coordinate system for attributes x, y, width, and height.
     *
     * @return string|null The pattern units, or null if not set
     */
    public function getPatternUnits(): ?string
    {
        return $this->getAttribute('patternUnits');
    }

    /**
     * Sets the coordinate system for the contents of the pattern.
     *
     * @param string $patternContentUnits The units ('userSpaceOnUse' or 'objectBoundingBox')
     */
    public function setPatternContentUnits(string $patternContentUnits): static
    {
        $this->setAttribute('patternContentUnits', $patternContentUnits);

        return $this;
    }

    /**
     * Gets the coordinate system for the contents of the pattern.
     *
     * @return string|null The pattern content units, or null if not set
     */
    public function getPatternContentUnits(): ?string
    {
        return $this->getAttribute('patternContentUnits');
    }

    /**
     * Sets the transformation to apply to the pattern.
     *
     * @param string $patternTransform The transformation string
     */
    public function setPatternTransform(string $patternTransform): static
    {
        $this->setAttribute('patternTransform', $patternTransform);

        return $this;
    }

    /**
     * Gets the transformation to apply to the pattern.
     *
     * @return string|null The pattern transform, or null if not set
     */
    public function getPatternTransform(): ?string
    {
        return $this->getAttribute('patternTransform');
    }

    /**
     * Sets the viewBox attribute which defines the coordinate system
     * for the pattern content.
     *
     * @param string|Viewbox $viewBox The viewBox as a string or Viewbox object
     */
    public function setViewBox(string|Viewbox $viewBox): static
    {
        $this->setAttribute('viewBox', (string) $viewBox);

        return $this;
    }

    /**
     * Gets the viewBox attribute.
     *
     * @return Viewbox|null The viewBox as a Viewbox object, or null if not set
     */
    public function getViewBox(): ?Viewbox
    {
        $value = $this->getAttribute('viewBox');

        return null !== $value ? Viewbox::parse($value) : null;
    }

    /**
     * Sets the preserveAspectRatio attribute which defines how the pattern
     * should scale if the viewBox aspect ratio doesn't match.
     *
     * @param string $preserveAspectRatio The preserveAspectRatio value
     */
    public function setPreserveAspectRatio(string $preserveAspectRatio): static
    {
        $this->setAttribute('preserveAspectRatio', $preserveAspectRatio);

        return $this;
    }

    /**
     * Gets the preserveAspectRatio attribute.
     *
     * @return string|null The preserveAspectRatio value, or null if not set
     */
    public function getPreserveAspectRatio(): ?string
    {
        return $this->getAttribute('preserveAspectRatio');
    }

    /**
     * Sets the position and dimensions of the pattern tile.
     *
     * @param string|int|float $x      The x coordinate
     * @param string|int|float $y      The y coordinate
     * @param string|int|float $width  The width
     * @param string|int|float $height The height
     */
    public function setBounds(
        string|int|float $x,
        string|int|float $y,
        string|int|float $width,
        string|int|float $height,
    ): static {
        $this->setX($x);
        $this->setY($y);
        $this->setWidth($width);
        $this->setHeight($height);

        return $this;
    }
}
