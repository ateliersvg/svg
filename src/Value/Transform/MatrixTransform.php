<?php

declare(strict_types=1);

namespace Atelier\Svg\Value\Transform;

use Atelier\Svg\Value\Transform;

/**
 * Represents an SVG matrix transform.
 */
final readonly class MatrixTransform implements Transform
{
    public function __construct(
        private float $a,
        private float $b,
        private float $c,
        private float $d,
        private float $e,
        private float $f,
    ) {
    }

    public function getA(): float
    {
        return $this->a;
    }

    public function getB(): float
    {
        return $this->b;
    }

    public function getC(): float
    {
        return $this->c;
    }

    public function getD(): float
    {
        return $this->d;
    }

    public function getE(): float
    {
        return $this->e;
    }

    public function getF(): float
    {
        return $this->f;
    }

    public function toString(): string
    {
        return "matrix({$this->a},{$this->b},{$this->c},{$this->d},{$this->e},{$this->f})";
    }
}
