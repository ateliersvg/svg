<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feBlend> SVG filter primitive.
 *
 * This filter primitive composes two input images together using commonly used
 * imaging software blending modes.
 *
 * Common uses:
 * - Compositing multiple filter effects
 * - Creating Photoshop-style blend modes
 * - Layering graphics
 *
 * Example:
 * <feBlend in="SourceGraphic" in2="BackgroundImage" mode="multiply"/>
 * <feBlend in="blur1" in2="blur2" mode="screen" result="combined"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feBlendElement
 */
final class FeBlendElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feBlend');
    }

    /**
     * Set the second input graphic.
     *
     * The first input is specified via the inherited 'in' attribute.
     * This is the second input to blend with the first.
     */
    public function setIn2(string $in2): static
    {
        $this->setAttribute('in2', $in2);

        return $this;
    }

    /**
     * Get the second input graphic.
     */
    public function getIn2(): ?string
    {
        return $this->getAttribute('in2');
    }

    /**
     * Set the blend mode.
     *
     * Supported modes:
     * - normal: Default, standard alpha compositing
     * - multiply: Multiply blend mode
     * - screen: Screen blend mode
     * - darken: Darken blend mode
     * - lighten: Lighten blend mode
     * - overlay: Overlay blend mode
     * - color-dodge: Color dodge blend mode
     * - color-burn: Color burn blend mode
     * - hard-light: Hard light blend mode
     * - soft-light: Soft light blend mode
     * - difference: Difference blend mode
     * - exclusion: Exclusion blend mode
     * - hue: Hue blend mode (CSS Compositing)
     * - saturation: Saturation blend mode (CSS Compositing)
     * - color: Color blend mode (CSS Compositing)
     * - luminosity: Luminosity blend mode (CSS Compositing)
     */
    public function setMode(string $mode): static
    {
        $this->setAttribute('mode', $mode);

        return $this;
    }

    /**
     * Get the blend mode.
     */
    public function getMode(): ?string
    {
        return $this->getAttribute('mode');
    }
}
