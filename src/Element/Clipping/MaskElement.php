<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Clipping;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <mask> element.
 *
 * The mask element defines an alpha mask for compositing the current object
 * into the background. A mask is used/referenced using the mask property.
 *
 * @see https://www.w3.org/TR/SVG11/masking.html#MaskElement
 */
final class MaskElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('mask');
    }

    /**
     * Sets the x-axis coordinate of the mask.
     *
     * @param string|int|float $x The x coordinate
     */
    public function setX(string|int|float $x): static
    {
        $this->setAttribute('x', (string) $x);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the mask.
     *
     * @return Length|null The x coordinate as a Length object, or null if not set
     */
    public function getX(): ?Length
    {
        $value = $this->getAttribute('x');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the mask.
     *
     * @param string|int|float $y The y coordinate
     */
    public function setY(string|int|float $y): static
    {
        $this->setAttribute('y', (string) $y);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the mask.
     *
     * @return Length|null The y coordinate as a Length object, or null if not set
     */
    public function getY(): ?Length
    {
        $value = $this->getAttribute('y');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the width of the mask.
     *
     * @param string|int|float $width The width
     */
    public function setWidth(string|int|float $width): static
    {
        $this->setAttribute('width', (string) $width);

        return $this;
    }

    /**
     * Gets the width of the mask.
     *
     * @return Length|null The width as a Length object, or null if not set
     */
    public function getWidth(): ?Length
    {
        $value = $this->getAttribute('width');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the height of the mask.
     *
     * @param string|int|float $height The height
     */
    public function setHeight(string|int|float $height): static
    {
        $this->setAttribute('height', (string) $height);

        return $this;
    }

    /**
     * Gets the height of the mask.
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
     * @param string $maskUnits The units ('userSpaceOnUse' or 'objectBoundingBox')
     */
    public function setMaskUnits(string $maskUnits): static
    {
        $this->setAttribute('maskUnits', $maskUnits);

        return $this;
    }

    /**
     * Gets the coordinate system for attributes x, y, width, and height.
     *
     * @return string|null The mask units, or null if not set
     */
    public function getMaskUnits(): ?string
    {
        return $this->getAttribute('maskUnits');
    }

    /**
     * Sets the coordinate system for the contents of the mask.
     *
     * @param string $maskContentUnits The units ('userSpaceOnUse' or 'objectBoundingBox')
     */
    public function setMaskContentUnits(string $maskContentUnits): static
    {
        $this->setAttribute('maskContentUnits', $maskContentUnits);

        return $this;
    }

    /**
     * Gets the coordinate system for the contents of the mask.
     *
     * @return string|null The mask content units, or null if not set
     */
    public function getMaskContentUnits(): ?string
    {
        return $this->getAttribute('maskContentUnits');
    }

    /**
     * Sets the position and dimensions of the mask region.
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
