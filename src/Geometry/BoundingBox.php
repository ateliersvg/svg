<?php

declare(strict_types=1);

namespace Atelier\Svg\Geometry;

use Atelier\Svg\Exception\InvalidArgumentException;

final readonly class BoundingBox
{
    public function __construct(
        public float $minX,
        public float $minY,
        public float $maxX,
        public float $maxY,
    ) {
    }

    public function getCenter(): Point
    {
        return new Point(
            x: ($this->minX + $this->maxX) / 2,
            y: ($this->minY + $this->maxY) / 2,
        );
    }

    public function getCenterX(): float
    {
        return ($this->minX + $this->maxX) / 2;
    }

    public function getCenterY(): float
    {
        return ($this->minY + $this->maxY) / 2;
    }

    public function getArea(): float
    {
        return $this->getWidth() * $this->getHeight();
    }

    public function getPerimeter(): float
    {
        return 2 * ($this->getWidth() + $this->getHeight());
    }

    public function getWidth(): float
    {
        return $this->maxX - $this->minX;
    }

    public function getHeight(): float
    {
        return $this->maxY - $this->minY;
    }

    public function contains(Point $point): bool
    {
        return
            $point->x >= $this->minX
            && $point->x <= $this->maxX
            && $point->y >= $this->minY
            && $point->y <= $this->maxY
        ;
    }

    public static function fromPoints(Point ...$points): self
    {
        if (empty($points)) {
            return new self(0, 0, 0, 0);
        }

        $xs = array_map(fn ($p) => $p->x, $points);
        $ys = array_map(fn ($p) => $p->y, $points);

        return new self(
            min($xs),
            min($ys),
            max($xs),
            max($ys),
        );
    }

    /**
     * Returns the union of two bounding boxes.
     */
    public function union(BoundingBox $other): self
    {
        return new self(
            min($this->minX, $other->minX),
            min($this->minY, $other->minY),
            max($this->maxX, $other->maxX),
            max($this->maxY, $other->maxY),
        );
    }

    /**
     * Returns the intersection of two bounding boxes, or null if they don't intersect.
     */
    public function intersect(BoundingBox $other): ?self
    {
        $minX = max($this->minX, $other->minX);
        $minY = max($this->minY, $other->minY);
        $maxX = min($this->maxX, $other->maxX);
        $maxY = min($this->maxY, $other->maxY);

        if ($maxX <= $minX || $maxY <= $minY) {
            return null; // No intersection
        }

        return new self($minX, $minY, $maxX, $maxY);
    }

    /**
     * Expands the bounding box by the given margin in all directions.
     */
    public function expand(float $margin): self
    {
        return new self(
            $this->minX - $margin,
            $this->minY - $margin,
            $this->maxX + $margin,
            $this->maxY + $margin,
        );
    }

    /**
     * Gets an anchor point on the bounding box.
     *
     * Supported anchors:
     * - 'top-left', 'tl'
     * - 'top-center', 'tc', 'top'
     * - 'top-right', 'tr'
     * - 'center-left', 'cl', 'left'
     * - 'center', 'c'
     * - 'center-right', 'cr', 'right'
     * - 'bottom-left', 'bl'
     * - 'bottom-center', 'bc', 'bottom'
     * - 'bottom-right', 'br'
     */
    public function getAnchor(string $anchor): Point
    {
        return match ($anchor) {
            'top-left', 'tl' => new Point($this->minX, $this->minY),
            'top-center', 'tc', 'top' => new Point(($this->minX + $this->maxX) / 2, $this->minY),
            'top-right', 'tr' => new Point($this->maxX, $this->minY),
            'center-left', 'cl', 'left' => new Point($this->minX, ($this->minY + $this->maxY) / 2),
            'center', 'c' => $this->getCenter(),
            'center-right', 'cr', 'right' => new Point($this->maxX, ($this->minY + $this->maxY) / 2),
            'bottom-left', 'bl' => new Point($this->minX, $this->maxY),
            'bottom-center', 'bc', 'bottom' => new Point(($this->minX + $this->maxX) / 2, $this->maxY),
            'bottom-right', 'br' => new Point($this->maxX, $this->maxY),
            default => throw new InvalidArgumentException("Invalid anchor: {$anchor}"),
        };
    }

    /**
     * Checks if this bounding box intersects with another.
     */
    public function intersects(BoundingBox $other): bool
    {
        return null !== $this->intersect($other);
    }

    /**
     * Gets the X coordinate (minX).
     */
    public function getX(): float
    {
        return $this->minX;
    }

    /**
     * Gets the Y coordinate (minY).
     */
    public function getY(): float
    {
        return $this->minY;
    }
}
