<?php

declare(strict_types=1);

namespace Atelier\Svg\Geometry;

final readonly class Point implements \Stringable
{
    public function __construct(
        public float $x,
        public float $y,
    ) {
    }

    public function add(Point $other): self
    {
        return new self($this->x + $other->x, $this->y + $other->y);
    }

    public function subtract(Point $other): self
    {
        return new self($this->x - $other->x, $this->y - $other->y);
    }

    public function distanceTo(Point $other): float
    {
        return hypot($this->x - $other->x, $this->y - $other->y);
    }

    public function equals(Point $other, float $epsilon = 0.0001): bool
    {
        return abs($this->x - $other->x) < $epsilon
            && abs($this->y - $other->y) < $epsilon;
    }

    public function __toString(): string
    {
        return $this->x.','.$this->y;
    }
}
