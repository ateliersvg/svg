<?php

declare(strict_types=1);

namespace Atelier\Svg\Morphing;

use Atelier\Svg\Exception\RuntimeException;
use Atelier\Svg\Path\Data;

/**
 * Fluent builder for morphing operations.
 */
final class MorphingBuilder
{
    private ?Data $startPath = null;
    private ?Data $endPath = null;
    private int $frameCount = 60;
    private string|\Closure $easing = 'ease-in-out';
    private readonly ShapeMorpher $morpher;

    public function __construct()
    {
        $this->morpher = new ShapeMorpher();
    }

    /**
     * Sets the starting path.
     */
    public function from(Data $path): self
    {
        $this->startPath = $path;

        return $this;
    }

    /**
     * Sets the ending path.
     */
    public function to(Data $path): self
    {
        $this->endPath = $path;

        return $this;
    }

    /**
     * Sets the number of frames to generate.
     */
    public function withFrames(int $count): self
    {
        $this->frameCount = $count;

        return $this;
    }

    /**
     * Sets the easing function.
     *
     * @param string|\Closure $easing Easing function name or callable
     */
    public function withEasing(string|\Closure $easing): self
    {
        $this->easing = $easing;

        return $this;
    }

    /**
     * Sets duration in milliseconds and frame rate to calculate frame count.
     */
    public function withDuration(int $durationMs, int $fps = 60): self
    {
        $this->frameCount = (int) ceil(($durationMs / 1000) * $fps);

        return $this;
    }

    /**
     * Generates the morphing frames.
     *
     * @return Data[]
     */
    public function generate(): array
    {
        if (null === $this->startPath || null === $this->endPath) {
            throw new RuntimeException('Both start and end paths must be set');
        }

        return $this->morpher->generateFrames(
            $this->startPath,
            $this->endPath,
            $this->frameCount,
            $this->easing
        );
    }

    /**
     * Gets a single interpolated frame at parameter t.
     */
    public function at(float $t): Data
    {
        if (null === $this->startPath || null === $this->endPath) {
            throw new RuntimeException('Both start and end paths must be set');
        }

        return $this->morpher->morph(
            $this->startPath,
            $this->endPath,
            $t,
            $this->easing
        );
    }
}
