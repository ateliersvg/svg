<?php

declare(strict_types=1);

namespace Atelier\Svg\Value\Transform;

use Atelier\Svg\Value\Angle;
use Atelier\Svg\Value\Transform;

/**
 * Represents an SVG skewX transform.
 */
final readonly class SkewXTransform implements Transform
{
    public function __construct(
        private Angle $angle,
    ) {
    }

    public function getAngle(): Angle
    {
        return $this->angle;
    }

    public function toString(): string
    {
        return "skewX({$this->angle})";
    }
}
