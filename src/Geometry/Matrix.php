<?php

declare(strict_types=1);

namespace Atelier\Svg\Geometry;

use Atelier\Svg\Exception\RuntimeException;

final readonly class Matrix implements \Stringable
{
    public function __construct(
        public float $a = 1,
        public float $b = 0,
        public float $c = 0,
        public float $d = 1,
        public float $e = 0,
        public float $f = 0,
    ) {
    }

    public function multiply(self $m): self
    {
        return new self(
            a: $this->a * $m->a + $this->c * $m->b,
            b: $this->b * $m->a + $this->d * $m->b,
            c: $this->a * $m->c + $this->c * $m->d,
            d: $this->b * $m->c + $this->d * $m->d,
            e: $this->a * $m->e + $this->c * $m->f + $this->e,
            f: $this->b * $m->e + $this->d * $m->f + $this->f,
        );
    }

    public function transform(Point $p): Point
    {
        return new Point(
            x: $p->x * $this->a + $p->y * $this->c + $this->e,
            y: $p->x * $this->b + $p->y * $this->d + $this->f,
        );
    }

    /**
     * Calculates the determinant of the matrix.
     */
    public function determinant(): float
    {
        return $this->a * $this->d - $this->b * $this->c;
    }

    /**
     * Returns the inverse of this matrix.
     *
     * @throws RuntimeException if the matrix is singular (not invertible)
     */
    public function inverse(): self
    {
        $det = $this->determinant();

        if (abs($det) < 1e-10) {
            throw new RuntimeException('Matrix is singular and cannot be inverted');
        }

        return new self(
            a: $this->d / $det,
            b: -$this->b / $det,
            c: -$this->c / $det,
            d: $this->a / $det,
            e: ($this->c * $this->f - $this->d * $this->e) / $det,
            f: ($this->b * $this->e - $this->a * $this->f) / $det,
        );
    }

    /**
     * Transforms a bounding box.
     */
    public function transformBBox(BoundingBox $bbox): BoundingBox
    {
        // Transform all four corners
        $topLeft = $this->transform(new Point($bbox->minX, $bbox->minY));
        $topRight = $this->transform(new Point($bbox->maxX, $bbox->minY));
        $bottomLeft = $this->transform(new Point($bbox->minX, $bbox->maxY));
        $bottomRight = $this->transform(new Point($bbox->maxX, $bbox->maxY));

        return BoundingBox::fromPoints($topLeft, $topRight, $bottomLeft, $bottomRight);
    }

    /**
     * Checks if this is an identity matrix.
     */
    public function isIdentity(): bool
    {
        return abs($this->a - 1) < 1e-10
            && abs($this->b) < 1e-10
            && abs($this->c) < 1e-10
            && abs($this->d - 1) < 1e-10
            && abs($this->e) < 1e-10
            && abs($this->f) < 1e-10;
    }

    /**
     * Decomposes the matrix into its transformation components.
     *
     * @return array{translateX: float, translateY: float, scaleX: float, scaleY: float, rotation: float, skewX: float}
     */
    public function decompose(): array
    {
        $a = $this->a;
        $b = $this->b;
        $c = $this->c;
        $d = $this->d;

        $translateX = $this->e;
        $translateY = $this->f;

        $scaleX = sqrt($a * $a + $b * $b);

        // Normalize the first column (X-axis)
        if (0 != $scaleX) {
            $a /= $scaleX;
            $b /= $scaleX;
        }

        // Shear is the dot product of the normalized X-axis and the Y-axis (c, d)
        $shear = $a * $c + $b * $d;

        // Make the Y-axis orthogonal to X-axis
        $c -= $a * $shear;
        $d -= $b * $shear;

        $scaleY = sqrt($c * $c + $d * $d);

        // Normalize Y-axis
        if (0 != $scaleY) {
            $c /= $scaleY;
            $d /= $scaleY;
        }

        // Rotation (angle of the X-axis)
        $rotation = rad2deg(atan2($b, $a));

        // Determinant check for flip (if determinant is negative, flip scaleY)
        if ($this->a * $this->d - $this->b * $this->c < 0) {
            $scaleY = -$scaleY;
        }

        // Calculate skew angle from shear factor
        // The shear variable is tan(skewAngle)
        $skewX = rad2deg(atan($shear));

        return [
            'translateX' => $translateX,
            'translateY' => $translateY,
            'scaleX' => $scaleX,
            'scaleY' => $scaleY,
            'rotation' => $rotation,
            'skewX' => $skewX,
        ];
    }

    /**
     * Checks if the matrix has only uniform scaling (no shear/skew).
     */
    public function isUniformScale(): bool
    {
        $components = $this->decompose();

        return abs($components['scaleX'] - $components['scaleY']) < 1e-6
            && abs($components['skewX']) < 1e-6;
    }

    /**
     * Checks if the matrix has shear/skew.
     */
    public function hasShear(): bool
    {
        $components = $this->decompose();

        return abs($components['skewX']) >= 1e-6;
    }

    /**
     * Returns a string representation of the matrix.
     */
    public function toString(): string
    {
        return sprintf(
            'matrix(%g, %g, %g, %g, %g, %g)',
            $this->a,
            $this->b,
            $this->c,
            $this->d,
            $this->e,
            $this->f
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
