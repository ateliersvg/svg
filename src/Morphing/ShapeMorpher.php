<?php

declare(strict_types=1);

namespace Atelier\Svg\Morphing;

use Atelier\Svg\Path\Data;

/**
 * Main facade for shape morphing operations.
 *
 * Provides a simple API for morphing between SVG paths with various options
 * and easing functions.
 *
 * Example usage:
 * ```php
 * $morpher = new ShapeMorpher();
 * $result = $morpher->morph($startPath, $endPath, 0.5);
 *
 * // Or with builder pattern:
 * $frames = ShapeMorpher::create()
 *     ->from($path1)
 *     ->to($path2)
 *     ->withFrames(60)
 *     ->withEasing('ease-in-out')
 *     ->generate();
 * ```
 */
final readonly class ShapeMorpher
{
    private PathNormalizer $normalizer;
    private PathMatcher $matcher;
    private MorphingInterpolator $interpolator;

    public function __construct()
    {
        $this->normalizer = new PathNormalizer();
        $this->matcher = new PathMatcher();
        $this->interpolator = new MorphingInterpolator();
    }

    /**
     * Creates a new builder for fluent API.
     */
    public static function create(): MorphingBuilder
    {
        return new MorphingBuilder();
    }

    /**
     * Morphs between two paths at parameter t.
     *
     * @param Data                 $startPath The starting path
     * @param Data                 $endPath   The ending path
     * @param float                $t         Interpolation parameter (0 = start, 1 = end)
     * @param string|callable|null $easing    Easing function name or callable
     *
     * @return Data The morphed path
     */
    public function morph(
        Data $startPath,
        Data $endPath,
        float $t,
        string|callable|null $easing = null,
    ): Data {
        // Normalize paths
        $normalizedStart = $this->normalizer->normalize($startPath);
        $normalizedEnd = $this->normalizer->normalize($endPath);

        // Match segments
        [$matchedStart, $matchedEnd] = $this->matcher->match($normalizedStart, $normalizedEnd);

        // Get easing function
        $easingFn = is_string($easing) ? $this->getEasingFunction($easing) : $easing;

        // Interpolate
        return $this->interpolator->interpolate($matchedStart, $matchedEnd, $t, $easingFn);
    }

    /**
     * Generates multiple frames for animation.
     *
     * @param int                  $frameCount Number of frames
     * @param string|callable|null $easing     Easing function
     *
     * @return Data[]
     */
    public function generateFrames(
        Data $startPath,
        Data $endPath,
        int $frameCount = 60,
        string|callable|null $easing = null,
    ): array {
        // Normalize and match once
        $normalizedStart = $this->normalizer->normalize($startPath);
        $normalizedEnd = $this->normalizer->normalize($endPath);
        [$matchedStart, $matchedEnd] = $this->matcher->match($normalizedStart, $normalizedEnd);

        // Get easing function
        $easingFn = is_string($easing) ? $this->getEasingFunction($easing) : $easing;

        // Generate frames
        return $this->interpolator->generateFrames($matchedStart, $matchedEnd, $frameCount, $easingFn);
    }

    /**
     * Gets an easing function by name.
     */
    private function getEasingFunction(?string $name): ?callable
    {
        assert(null !== $name);

        return match ($name) {
            'linear' => MorphingInterpolator::easeLinear(...),
            'ease-in' => MorphingInterpolator::easeIn(...),
            'ease-out' => MorphingInterpolator::easeOut(...),
            'ease-in-out' => MorphingInterpolator::easeInOut(...),
            'ease-in-cubic' => MorphingInterpolator::easeInCubic(...),
            'ease-out-cubic' => MorphingInterpolator::easeOutCubic(...),
            'ease-in-out-cubic' => MorphingInterpolator::easeInOutCubic(...),
            'ease-out-elastic' => MorphingInterpolator::easeOutElastic(...),
            'ease-in-back' => MorphingInterpolator::easeInBack(...),
            'ease-out-back' => MorphingInterpolator::easeOutBack(...),
            default => null,
        };
    }

    /**
     * Normalizes a path (for debugging/inspection).
     */
    public function normalize(Data $path): Data
    {
        return $this->normalizer->normalize($path);
    }

    /**
     * Matches two paths (for debugging/inspection).
     *
     * @return array{0: Data, 1: Data}
     */
    public function match(Data $startPath, Data $endPath): array
    {
        return $this->matcher->match($startPath, $endPath);
    }
}
