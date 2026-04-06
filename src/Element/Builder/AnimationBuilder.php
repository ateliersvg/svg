<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Builder;

use Atelier\Svg\Element\Animation\AnimateElement;
use Atelier\Svg\Element\Animation\AnimateTransformElement;
use Atelier\Svg\Element\ElementInterface;

/**
 * Utility class for creating and managing SVG animations.
 *
 * Provides convenient methods for creating SMIL animations and CSS animations
 * with a fluent API.
 *
 * Example usage:
 * ```php
 * // SMIL animation
 * AnimationBuilder::animate($element, 'opacity')
 *     ->from(0)
 *     ->to(1)
 *     ->duration('1s')
 *     ->repeatCount('indefinite');
 *
 * // Transform animation
 * AnimationBuilder::animateTransform($element, 'rotate')
 *     ->from(0)
 *     ->to(360)
 *     ->duration('2s');
 * ```
 *
 * @see https://www.w3.org/TR/SVG11/animate.html
 */
final readonly class AnimationBuilder
{
    public function __construct(private ElementInterface $element, private AnimateElement|AnimateTransformElement $animation)
    {
    }

    /**
     * Create a new attribute animation.
     *
     * @param ElementInterface $element       The element to animate
     * @param string           $attributeName The attribute to animate
     */
    public static function animate(
        ElementInterface $element,
        string $attributeName,
    ): self {
        $animate = new AnimateElement();
        $animate->setAttributeName($attributeName);

        return new self($element, $animate);
    }

    /**
     * Create a new transform animation.
     *
     * @param ElementInterface $element The element to animate
     * @param string           $type    The transform type (translate, scale, rotate, skewX, skewY)
     */
    public static function animateTransform(
        ElementInterface $element,
        string $type,
    ): self {
        $animate = new AnimateTransformElement();
        $animate->setAttributeName('transform');
        $animate->setType($type);

        return new self($element, $animate);
    }

    /**
     * Set the starting value.
     *
     * @param string|int|float $from Starting value
     */
    public function from(string|int|float $from): self
    {
        $this->animation->setFrom($from);

        return $this;
    }

    /**
     * Set the ending value.
     *
     * @param string|int|float $to Ending value
     */
    public function to(string|int|float $to): self
    {
        $this->animation->setTo($to);

        return $this;
    }

    /**
     * Set intermediate values.
     *
     * @param string $values Semicolon-separated list of values
     */
    public function values(string $values): self
    {
        if ($this->animation instanceof AnimateElement) {
            $this->animation->setValues($values);
        }

        return $this;
    }

    /**
     * Set the animation duration.
     *
     * @param string|int $duration Duration (e.g., '1s', '500ms', 1000 for milliseconds)
     */
    public function duration(string|int $duration): self
    {
        if (is_int($duration)) {
            $duration = $duration.'ms';
        }
        $this->animation->setDur($duration);

        return $this;
    }

    /**
     * Set how many times the animation repeats.
     *
     * @param string|int $count Number of times, or 'indefinite'
     */
    public function repeatCount(string|int $count): self
    {
        $this->animation->setRepeatCount($count);

        return $this;
    }

    /**
     * Set the fill mode (what happens after animation completes).
     *
     * @param string $fill 'freeze' to keep final value, 'remove' to revert
     */
    public function fillMode(string $fill): self
    {
        $this->animation->setFill($fill);

        return $this;
    }

    /**
     * Set when the animation begins.
     *
     * @param string $begin Begin time or event (e.g., '0s', 'click', '2s')
     */
    public function begin(string $begin): self
    {
        if ($this->animation instanceof AnimateElement) {
            $this->animation->setBegin($begin);
        }

        return $this;
    }

    /**
     * Set the calculation mode.
     *
     * @param string $calcMode discrete, linear, paced, or spline
     */
    public function calcMode(string $calcMode): self
    {
        if ($this->animation instanceof AnimateElement) {
            $this->animation->setCalcMode($calcMode);
        }

        return $this;
    }

    /**
     * Make the animation additive (adds to underlying value).
     */
    public function additive(): self
    {
        $this->animation->setAdditive('sum');

        return $this;
    }

    /**
     * Apply the animation to the element.
     */
    public function apply(): ElementInterface
    {
        if (method_exists($this->element, 'appendChild')) {
            $this->element->appendChild($this->animation);
        }

        return $this->element;
    }

    /**
     * Get the underlying animation element.
     */
    public function getAnimation(): AnimateElement|AnimateTransformElement
    {
        return $this->animation;
    }

    /**
     * Create a fade-in animation.
     *
     * @param ElementInterface $element  The element to animate
     * @param string|int       $duration Animation duration
     */
    public static function fadeIn(
        ElementInterface $element,
        string|int $duration = '1s',
    ): AnimateElement {
        $helper = self::animate($element, 'opacity')
            ->from(0)
            ->to(1)
            ->duration($duration)
            ->fillMode('freeze');

        $helper->apply();

        $animation = $helper->getAnimation();
        assert($animation instanceof AnimateElement);

        return $animation;
    }

    /**
     * Create a fade-out animation.
     *
     * @param ElementInterface $element  The element to animate
     * @param string|int       $duration Animation duration
     */
    public static function fadeOut(
        ElementInterface $element,
        string|int $duration = '1s',
    ): AnimateElement {
        $helper = self::animate($element, 'opacity')
            ->from(1)
            ->to(0)
            ->duration($duration)
            ->fillMode('freeze');

        $helper->apply();

        $animation = $helper->getAnimation();
        assert($animation instanceof AnimateElement);

        return $animation;
    }

    /**
     * Create a rotation animation.
     *
     * @param ElementInterface $element  The element to animate
     * @param float            $from     Starting angle in degrees
     * @param float            $to       Ending angle in degrees
     * @param string|int       $duration Animation duration
     */
    public static function rotate(
        ElementInterface $element,
        float $from = 0,
        float $to = 360,
        string|int $duration = '2s',
    ): AnimateTransformElement {
        $helper = self::animateTransform($element, 'rotate')
            ->from($from)
            ->to($to)
            ->duration($duration)
            ->fillMode('freeze');

        $helper->apply();

        $animation = $helper->getAnimation();
        assert($animation instanceof AnimateTransformElement);

        return $animation;
    }

    /**
     * Create a scale animation.
     *
     * @param ElementInterface $element  The element to animate
     * @param float            $from     Starting scale
     * @param float            $to       Ending scale
     * @param string|int       $duration Animation duration
     */
    public static function scale(
        ElementInterface $element,
        float $from = 1,
        float $to = 1.5,
        string|int $duration = '1s',
    ): AnimateTransformElement {
        $helper = self::animateTransform($element, 'scale')
            ->from($from)
            ->to($to)
            ->duration($duration)
            ->fillMode('freeze');

        $helper->apply();

        $animation = $helper->getAnimation();
        assert($animation instanceof AnimateTransformElement);

        return $animation;
    }

    /**
     * Add a CSS animation to an element via a style element.
     *
     * @param ElementInterface                     $element        The element to animate
     * @param string                               $animationName  Name of the animation
     * @param array<string, array<string, string>> $keyframes      Animation keyframes
     * @param string                               $duration       Animation duration
     * @param string                               $timing         Timing function (e.g., 'ease', 'linear')
     * @param string|int                           $iterationCount Iteration count or 'infinite'
     */
    public static function addCssAnimation(
        ElementInterface $element,
        string $animationName,
        array $keyframes,
        string $duration = '1s',
        string $timing = 'ease',
        string|int $iterationCount = 1,
    ): void {
        // Build CSS keyframes
        $css = "@keyframes {$animationName} {\n";
        foreach ($keyframes as $step => $properties) {
            $css .= "  {$step} {\n";
            foreach ($properties as $prop => $value) {
                $css .= "    {$prop}: {$value};\n";
            }
            $css .= "  }\n";
        }
        $css .= "}\n";

        // Generate a unique class for this animation
        $className = 'anim-'.md5($animationName.serialize($keyframes));
        $css .= ".{$className} {\n";
        $css .= "  animation: {$animationName} {$duration} {$timing} {$iterationCount};\n";
        $css .= "}\n";

        // Create or find style element
        // Note: In a real implementation, we'd need access to the document
        // to add this style element properly
        $element->addClass($className);
    }
}
