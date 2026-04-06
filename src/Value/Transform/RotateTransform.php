<?php

declare(strict_types=1);

namespace Atelier\Svg\Value\Transform;

use Atelier\Svg\Value\Angle;
use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\Transform;

/**
 * Represents an SVG rotate transform.
 */
final readonly class RotateTransform implements Transform
{
    /**
     * @param Angle       $angle Rotation angle
     * @param Length|null $cx    Center X (optional)
     * @param Length|null $cy    Center Y (optional)
     */
    public function __construct(
        private Angle $angle,
        private ?Length $cx = null,
        private ?Length $cy = null,
    ) {
    }

    public function getAngle(): Angle
    {
        return $this->angle;
    }

    public function getCx(): ?Length
    {
        return $this->cx;
    }

    public function getCy(): ?Length
    {
        return $this->cy;
    }

    public function hasCenter(): bool
    {
        return null !== $this->cx && null !== $this->cy;
    }

    public function toString(): string
    {
        if (!$this->hasCenter()) {
            return "rotate({$this->angle})";
        }

        return "rotate({$this->angle},{$this->cx},{$this->cy})";
    }
}
