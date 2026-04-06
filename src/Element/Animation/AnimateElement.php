<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Animation;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents the <animate> SVG element for SMIL animations.
 *
 * The animate element provides a way to animate a single attribute or property
 * over time.
 *
 * Example:
 * <animate attributeName="opacity" from="0" to="1" dur="1s" repeatCount="indefinite"/>
 *
 * @see https://www.w3.org/TR/SVG11/animate.html#AnimateElement
 */
final class AnimateElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('animate');
    }

    /**
     * Set the name of the attribute to animate.
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
     * Set the intermediate values for the animation.
     */
    public function setValues(string $values): static
    {
        $this->setAttribute('values', $values);

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
     * Set the begin time or event.
     */
    public function setBegin(string $begin): static
    {
        $this->setAttribute('begin', $begin);

        return $this;
    }

    /**
     * Set the animation calculation mode.
     *
     * @param string $calcMode discrete, linear, paced, or spline
     */
    public function setCalcMode(string $calcMode): static
    {
        $this->setAttribute('calcMode', $calcMode);

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
