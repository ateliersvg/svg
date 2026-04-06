<?php

declare(strict_types=1);

namespace Atelier\Svg\Value\Transform;

use Atelier\Svg\Value\Transform;

/**
 * Represents an SVG scale transform.
 */
final readonly class ScaleTransform implements Transform
{
    public function __construct(
        private float $sx,
        private float $sy,
    ) {
    }

    public function getSx(): float
    {
        return $this->sx;
    }

    public function getSy(): float
    {
        return $this->sy;
    }

    public function toString(): string
    {
        // If sx and sy are equal, we can omit sy
        if ($this->sx === $this->sy) {
            return "scale({$this->sx})";
        }

        return "scale({$this->sx},{$this->sy})";
    }
}
