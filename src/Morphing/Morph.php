<?php

declare(strict_types=1);

namespace Atelier\Svg\Morphing;

use Atelier\Svg\Path\Data;

/**
 * Facade for SVG shape morphing operations.
 *
 * Provides a simple API for morphing between SVG paths with various options
 * and easing functions. This is a focused API specifically for morphing,
 * separate from the general Svg facade.
 *
 * @example Simple morphing
 * ```php
 * use Atelier\Svg\Morph;
 *
 * $midPath = Morph::between($startPath, $endPath, 0.5);
 * ```
 * @example Generate animation frames
 * ```php
 * $frames = Morph::frames($startPath, $endPath, 60, 'ease-in-out');
 * ```
 * @example Advanced usage with builder
 * ```php
 * $frames = Morph::create()
 *     ->from($startPath)
 *     ->to($endPath)
 *     ->withDuration(2000, 60)
 *     ->withEasing('ease-in-out')
 *     ->generate();
 * ```
 */
final class Morph
{
    private function __construct()
    {
    }

    /**
     * Morph between two SVG paths at a specific interpolation point.
     *
     * @param Data            $startPath Starting path
     * @param Data            $endPath   Ending path
     * @param float           $t         Interpolation value (0.0 = start, 1.0 = end)
     * @param string|callable $easing    Easing function name or callable (default: 'linear')
     *
     * @return Data Interpolated path
     *
     * @example
     * ```php
     * $midPath = Morph::between($startPath, $endPath, 0.5);
     * ```
     */
    public static function between(Data $startPath, Data $endPath, float $t, string|callable $easing = 'linear'): Data
    {
        $morpher = new ShapeMorpher();

        return $morpher->morph($startPath, $endPath, $t, $easing);
    }

    /**
     * Generate multiple frames for morphing animation between two paths.
     *
     * @param Data            $startPath  Starting path
     * @param Data            $endPath    Ending path
     * @param int             $frameCount Number of frames to generate
     * @param string|callable $easing     Easing function name or callable (default: 'linear')
     *
     * @return array<Data> Array of interpolated paths
     *
     * @example
     * ```php
     * $frames = Morph::frames($startPath, $endPath, 60, 'ease-in-out');
     * ```
     */
    public static function frames(Data $startPath, Data $endPath, int $frameCount, string|callable $easing = 'linear'): array
    {
        $morpher = new ShapeMorpher();

        return $morpher->generateFrames($startPath, $endPath, $frameCount, $easing);
    }

    /**
     * Create a morphing builder for fluent API usage.
     *
     * @return MorphingBuilder Fluent builder for morphing operations
     *
     * @example
     * ```php
     * $frames = Morph::create()
     *     ->from($startPath)
     *     ->to($endPath)
     *     ->withDuration(2000, 60)
     *     ->withEasing('ease-in-out')
     *     ->generate();
     * ```
     */
    public static function create(): MorphingBuilder
    {
        return ShapeMorpher::create();
    }
}
