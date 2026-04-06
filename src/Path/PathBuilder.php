<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Exception\LogicException;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\SegmentInterface;

final class PathBuilder
{
    // Optional: track current point

    // Private constructor - force using static factory
    /**
     * @param SegmentInterface[] $segments
     */
    private function __construct(private array $segments = [], private ?Point $currentPoint = null)
    {
    }

    /**
     * Start a new path definition.
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * Start a new path with an initial MoveTo.
     */
    public static function startAt(float $x, float $y, bool $relative = false): self
    {
        $builder = new self();

        return $builder->moveTo($x, $y, $relative);
    }

    public function moveTo(float $x, float $y, bool $relative = false): self
    {
        $point = new Point($x, $y);
        $command = $relative ? 'm' : 'M';
        $this->segments[] = new Segment\MoveTo($command, $point);
        $this->currentPoint = $point; // Update current point (absolute position)

        return $this; // Return self for chaining
    }

    /**
     * @throws LogicException If there is no starting point
     */
    public function lineTo(float $x, float $y, bool $relative = false): self
    {
        // Add check: Must have a current point before LineTo etc.
        if (null === $this->currentPoint) {
            // Or automatically make it a MoveTo? Design decision.
            throw new LogicException('Cannot use LineTo without a starting point.');
        }
        $point = new Point($x, $y);
        $command = $relative ? 'l' : 'L';
        $this->segments[] = new Segment\LineTo($command, $point);
        // Update current point based on relative/absolute
        if (!$relative) {
            $this->currentPoint = $point;
        } else {
            $this->currentPoint = $this->currentPoint->add($point);
        }

        return $this;
    }

    /**
     * @throws LogicException If there is no starting point
     */
    public function curveTo(float $x1, float $y1, float $x2, float $y2, float $x, float $y, bool $relative = false): self
    {
        if (null === $this->currentPoint) {
            throw new LogicException('Cannot use curveTo without a starting point.');
        }
        $controlPoint1 = new Point($x1, $y1);
        $controlPoint2 = new Point($x2, $y2);
        $point = new Point($x, $y);
        $command = $relative ? 'c' : 'C';
        $this->segments[] = new Segment\CurveTo($command, $controlPoint1, $controlPoint2, $point);

        // Update current point
        if (!$relative) {
            $this->currentPoint = $point;
        } else {
            $this->currentPoint = $this->currentPoint->add($point);
        }

        return $this;
    }

    /**
     * @throws LogicException If there is no starting point
     */
    public function quadraticCurveTo(float $x1, float $y1, float $x, float $y, bool $relative = false): self
    {
        if (null === $this->currentPoint) {
            throw new LogicException('Cannot use quadraticCurveTo without a starting point.');
        }
        $controlPoint = new Point($x1, $y1);
        $point = new Point($x, $y);
        $command = $relative ? 'q' : 'Q';
        $this->segments[] = new Segment\QuadraticCurveTo($command, $controlPoint, $point);

        // Update current point
        if (!$relative) {
            $this->currentPoint = $point;
        } else {
            $this->currentPoint = $this->currentPoint->add($point);
        }

        return $this;
    }

    /**
     * @throws LogicException If there is no starting point
     */
    public function arcTo(float $rx, float $ry, float $xAxisRotation, bool $largeArcFlag, bool $sweepFlag, float $x, float $y, bool $relative = false): self
    {
        if (null === $this->currentPoint) {
            throw new LogicException('Cannot use arcTo without a starting point.');
        }
        $point = new Point($x, $y);
        $command = $relative ? 'a' : 'A';
        $this->segments[] = new Segment\ArcTo($command, $rx, $ry, $xAxisRotation, $largeArcFlag, $sweepFlag, $point);

        // Update current point
        if (!$relative) {
            $this->currentPoint = $point;
        } else {
            $this->currentPoint = $this->currentPoint->add($point);
        }

        return $this;
    }

    /**
     * @throws LogicException If there is no starting point
     */
    public function horizontalLineTo(float $x, bool $relative = false): self
    {
        if (null === $this->currentPoint) {
            throw new LogicException('Cannot use horizontalLineTo without a starting point.');
        }
        $command = $relative ? 'h' : 'H';
        $this->segments[] = new Segment\HorizontalLineTo($command, $x);

        // Update current point
        if (!$relative) {
            $this->currentPoint = new Point($x, $this->currentPoint->y);
        } else {
            $this->currentPoint = new Point($this->currentPoint->x + $x, $this->currentPoint->y);
        }

        return $this;
    }

    /**
     * @throws LogicException If there is no starting point
     */
    public function verticalLineTo(float $y, bool $relative = false): self
    {
        if (null === $this->currentPoint) {
            throw new LogicException('Cannot use verticalLineTo without a starting point.');
        }
        $command = $relative ? 'v' : 'V';
        $this->segments[] = new Segment\VerticalLineTo($command, $y);

        // Update current point
        if (!$relative) {
            $this->currentPoint = new Point($this->currentPoint->x, $y);
        } else {
            $this->currentPoint = new Point($this->currentPoint->x, $this->currentPoint->y + $y);
        }

        return $this;
    }

    /**
     * @throws LogicException If there is no starting point
     */
    public function smoothCurveTo(float $x2, float $y2, float $x, float $y, bool $relative = false): self
    {
        if (null === $this->currentPoint) {
            throw new LogicException('Cannot use smoothCurveTo without a starting point.');
        }
        $controlPoint2 = new Point($x2, $y2);
        $point = new Point($x, $y);
        $command = $relative ? 's' : 'S';
        $this->segments[] = new Segment\SmoothCurveTo($command, $controlPoint2, $point);

        // Update current point
        if (!$relative) {
            $this->currentPoint = $point;
        } else {
            $this->currentPoint = $this->currentPoint->add($point);
        }

        return $this;
    }

    /**
     * @throws LogicException If there is no starting point
     */
    public function smoothQuadraticCurveTo(float $x, float $y, bool $relative = false): self
    {
        if (null === $this->currentPoint) {
            throw new LogicException('Cannot use smoothQuadraticCurveTo without a starting point.');
        }
        $point = new Point($x, $y);
        $command = $relative ? 't' : 'T';
        $this->segments[] = new Segment\SmoothQuadraticCurveTo($command, $point);

        // Update current point
        if (!$relative) {
            $this->currentPoint = $point;
        } else {
            $this->currentPoint = $this->currentPoint->add($point);
        }

        return $this;
    }

    public function closePath(): self
    {
        // Check if already closed or if first segment is MoveTo?
        $this->segments[] = new Segment\ClosePath('Z'); // Typically use 'Z'
        // Current point technically returns to the start of the subpath
        $this->currentPoint = null; // Reset or find actual start

        return $this;
    }

    public function getPathData(): string
    {
        return $this->toData()->toString();
    }

    /**
     * Converts this path builder to a Data object.
     */
    public function toData(): Data
    {
        return new Data($this->segments);
    }

    /**
     * Gets a path analyzer for this path.
     */
    public function analyze(): PathAnalyzer
    {
        return new PathAnalyzer($this->toData());
    }

    /**
     * Gets the bounding box of this path.
     */
    public function getBoundingBox(): \Atelier\Svg\Geometry\BoundingBox
    {
        return $this->analyze()->getBoundingBox();
    }

    /**
     * Gets the approximate length of this path.
     */
    public function getLength(): float
    {
        return $this->analyze()->getLength();
    }

    /**
     * Gets the point at a specific length along the path.
     */
    public function getPointAtLength(float $length): ?Point
    {
        return $this->analyze()->getPointAtLength($length);
    }

    /**
     * Alias for closePath() to match common API patterns.
     */
    public function close(): self
    {
        return $this->closePath();
    }

    /**
     * Add a segment directly to the path.
     */
    public function addSegment(SegmentInterface $segment): self
    {
        $this->segments[] = $segment;

        // Update current point if applicable
        $targetPoint = $segment->getTargetPoint();
        if (null !== $targetPoint) {
            $this->currentPoint = $targetPoint;
        }

        return $this;
    }

    /**
     * Convert to a Path instance.
     */
    public function toPath(): Path
    {
        return new Path($this->toData());
    }

    /**
     * Alias for toData() - returns path data string.
     */
    public function toPathData(): Data
    {
        return $this->toData();
    }
}
