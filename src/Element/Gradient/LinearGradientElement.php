<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Gradient;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <linearGradient> element.
 *
 * The linearGradient element defines a linear gradient that can be applied
 * to fill or stroke of graphical elements. It contains stop elements that
 * define the gradient colors.
 *
 * @see https://www.w3.org/TR/SVG11/pservers.html#LinearGradientElement
 */
final class LinearGradientElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('linearGradient');
    }

    /**
     * Sets the x-axis coordinate of the gradient start point.
     *
     * @param string|int|float $x1 The x1 coordinate
     */
    public function setX1(string|int|float $x1): static
    {
        $this->setAttribute('x1', (string) $x1);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the gradient start point.
     *
     * @return Length|null The x1 coordinate as a Length object, or null if not set
     */
    public function getX1(): ?Length
    {
        $value = $this->getAttribute('x1');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the gradient start point.
     *
     * @param string|int|float $y1 The y1 coordinate
     */
    public function setY1(string|int|float $y1): static
    {
        $this->setAttribute('y1', (string) $y1);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the gradient start point.
     *
     * @return Length|null The y1 coordinate as a Length object, or null if not set
     */
    public function getY1(): ?Length
    {
        $value = $this->getAttribute('y1');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the x-axis coordinate of the gradient end point.
     *
     * @param string|int|float $x2 The x2 coordinate
     */
    public function setX2(string|int|float $x2): static
    {
        $this->setAttribute('x2', (string) $x2);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the gradient end point.
     *
     * @return Length|null The x2 coordinate as a Length object, or null if not set
     */
    public function getX2(): ?Length
    {
        $value = $this->getAttribute('x2');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the gradient end point.
     *
     * @param string|int|float $y2 The y2 coordinate
     */
    public function setY2(string|int|float $y2): static
    {
        $this->setAttribute('y2', (string) $y2);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the gradient end point.
     *
     * @return Length|null The y2 coordinate as a Length object, or null if not set
     */
    public function getY2(): ?Length
    {
        $value = $this->getAttribute('y2');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the coordinate system for the gradient.
     *
     * @param string $gradientUnits The units ('userSpaceOnUse' or 'objectBoundingBox')
     */
    public function setGradientUnits(string $gradientUnits): static
    {
        $this->setAttribute('gradientUnits', $gradientUnits);

        return $this;
    }

    /**
     * Gets the coordinate system for the gradient.
     *
     * @return string|null The gradient units, or null if not set
     */
    public function getGradientUnits(): ?string
    {
        return $this->getAttribute('gradientUnits');
    }

    /**
     * Sets the transformation to apply to the gradient.
     *
     * @param string $gradientTransform The transformation string
     */
    public function setGradientTransform(string $gradientTransform): static
    {
        $this->setAttribute('gradientTransform', $gradientTransform);

        return $this;
    }

    /**
     * Gets the transformation to apply to the gradient.
     *
     * @return string|null The gradient transform, or null if not set
     */
    public function getGradientTransform(): ?string
    {
        return $this->getAttribute('gradientTransform');
    }

    /**
     * Sets how the gradient behaves outside its defined region.
     *
     * @param string $spreadMethod The spread method ('pad', 'reflect', or 'repeat')
     */
    public function setSpreadMethod(string $spreadMethod): static
    {
        $this->setAttribute('spreadMethod', $spreadMethod);

        return $this;
    }

    /**
     * Gets how the gradient behaves outside its defined region.
     *
     * @return string|null The spread method, or null if not set
     */
    public function getSpreadMethod(): ?string
    {
        return $this->getAttribute('spreadMethod');
    }

    /**
     * Sets the gradient direction using start and end coordinates.
     *
     * @param string|int|float $x1 The x1 coordinate
     * @param string|int|float $y1 The y1 coordinate
     * @param string|int|float $x2 The x2 coordinate
     * @param string|int|float $y2 The y2 coordinate
     */
    public function setDirection(
        string|int|float $x1,
        string|int|float $y1,
        string|int|float $x2,
        string|int|float $y2,
    ): static {
        $this->setX1($x1);
        $this->setY1($y1);
        $this->setX2($x2);
        $this->setY2($y2);

        return $this;
    }
}
