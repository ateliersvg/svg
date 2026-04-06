<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Animation;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents the <animateTransform> SVG element for animating transformations.
 *
 * This element animates a transformation attribute on a target element, thereby
 * allowing animations to control translation, scaling, rotation and/or skewing.
 *
 * Example:
 * <animateTransform attributeName="transform" type="rotate" from="0" to="360" dur="2s"/>
 *
 * @see https://www.w3.org/TR/SVG11/animate.html#AnimateTransformElement
 */
final class AnimateTransformElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('animateTransform');
    }

    /**
     * Set the type of transformation to animate.
     *
     * @param string $type translate, scale, rotate, skewX, or skewY
     */
    public function setType(string $type): static
    {
        $this->setAttribute('type', $type);

        return $this;
    }

    /**
     * Set the name of the attribute to animate (usually 'transform').
     */
    public function setAttributeName(string $attributeName): static
    {
        $this->setAttribute('attributeName', $attributeName);

        return $this;
    }

    /**
     * Set the starting value of the animation.
     */
    public function setFrom(string|int|float $from): static
    {
        $this->setAttribute('from', (string) $from);

        return $this;
    }

    /**
     * Set the ending value of the animation.
     */
    public function setTo(string|int|float $to): static
    {
        $this->setAttribute('to', (string) $to);

        return $this;
    }

    /**
     * Set the duration of the animation.
     */
    public function setDur(string $dur): static
    {
        $this->setAttribute('dur', $dur);

        return $this;
    }

    /**
     * Set the repeat count.
     *
     * @param string|int $repeatCount Number of times to repeat, or 'indefinite'
     */
    public function setRepeatCount(string|int $repeatCount): static
    {
        $this->setAttribute('repeatCount', (string) $repeatCount);

        return $this;
    }

    /**
     * Set the fill behavior (freeze or remove).
     */
    #[\Override]
    public function setFill(string $fill): static
    {
        $this->setAttribute('fill', $fill);

        return $this;
    }

    /**
     * Set whether values are additive.
     */
    public function setAdditive(string $additive): static
    {
        $this->setAttribute('additive', $additive);

        return $this;
    }
}
