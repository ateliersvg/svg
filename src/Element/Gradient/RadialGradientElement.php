<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Gradient;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <radialGradient> element.
 *
 * The radialGradient element defines a radial gradient that can be applied
 * to fill or stroke of graphical elements. It contains stop elements that
 * define the gradient colors.
 *
 * @see https://www.w3.org/TR/SVG11/pservers.html#RadialGradientElement
 */
final class RadialGradientElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('radialGradient');
    }

    /**
     * Sets the x-axis coordinate of the center of the gradient circle.
     *
     * @param string|int|float $cx The cx coordinate
     */
    public function setCx(string|int|float $cx): static
    {
        $this->setAttribute('cx', (string) $cx);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the center of the gradient circle.
     *
     * @return Length|null The cx coordinate as a Length object, or null if not set
     */
    public function getCx(): ?Length
    {
        $value = $this->getAttribute('cx');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the center of the gradient circle.
     *
     * @param string|int|float $cy The cy coordinate
     */
    public function setCy(string|int|float $cy): static
    {
        $this->setAttribute('cy', (string) $cy);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the center of the gradient circle.
     *
     * @return Length|null The cy coordinate as a Length object, or null if not set
     */
    public function getCy(): ?Length
    {
        $value = $this->getAttribute('cy');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the radius of the gradient circle.
     *
     * @param string|int|float $r The radius
     */
    public function setR(string|int|float $r): static
    {
        $this->setAttribute('r', (string) $r);

        return $this;
    }

    /**
     * Gets the radius of the gradient circle.
     *
     * @return Length|null The radius as a Length object, or null if not set
     */
    public function getR(): ?Length
    {
        $value = $this->getAttribute('r');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the x-axis coordinate of the focal point of the gradient.
     *
     * @param string|int|float $fx The fx coordinate
     */
    public function setFx(string|int|float $fx): static
    {
        $this->setAttribute('fx', (string) $fx);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the focal point of the gradient.
     *
     * @return Length|null The fx coordinate as a Length object, or null if not set
     */
    public function getFx(): ?Length
    {
        $value = $this->getAttribute('fx');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the focal point of the gradient.
     *
     * @param string|int|float $fy The fy coordinate
     */
    public function setFy(string|int|float $fy): static
    {
        $this->setAttribute('fy', (string) $fy);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the focal point of the gradient.
     *
     * @return Length|null The fy coordinate as a Length object, or null if not set
     */
    public function getFy(): ?Length
    {
        $value = $this->getAttribute('fy');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the radius of the focal point of the gradient.
     *
     * @param string|int|float $fr The focal radius
     */
    public function setFr(string|int|float $fr): static
    {
        $this->setAttribute('fr', (string) $fr);

        return $this;
    }

    /**
     * Gets the radius of the focal point of the gradient.
     *
     * @return Length|null The focal radius as a Length object, or null if not set
     */
    public function getFr(): ?Length
    {
        $value = $this->getAttribute('fr');

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
     * Sets the center of the gradient circle.
     *
     * @param string|int|float $cx The cx coordinate
     * @param string|int|float $cy The cy coordinate
     */
    public function setCenter(string|int|float $cx, string|int|float $cy): static
    {
        $this->setCx($cx);
        $this->setCy($cy);

        return $this;
    }

    /**
     * Sets the focal point of the gradient.
     *
     * @param string|int|float $fx The fx coordinate
     * @param string|int|float $fy The fy coordinate
     */
    public function setFocalPoint(string|int|float $fx, string|int|float $fy): static
    {
        $this->setFx($fx);
        $this->setFy($fy);

        return $this;
    }
}
