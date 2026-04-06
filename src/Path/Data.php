<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Path\Segment\SegmentInterface;

final class Data implements \Stringable
{
    /**
     * @param SegmentInterface[] $segments
     */
    public function __construct(private array $segments = [])
    {
    }

    /**
     * @return SegmentInterface[]
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    public function addSegment(SegmentInterface $segment): void
    {
        $this->segments[] = $segment;
    }

    /**
     * Reverses the path direction.
     *
     * Note: This is a complex operation that involves:
     * 1. Converting all segments to absolute coordinates
     * 2. Reordering segments in reverse
     * 3. Transforming segment types (e.g., LineTo target becomes previous segment's target)
     * 4. Swapping control points for curves
     */
    public function reverse(): self
    {
        if (empty($this->segments)) {
            return new self([]);
        }

        // 1. Convert to absolute to simplify reversal
        $absolutePath = PathUtils::toAbsolute($this);
        $absSegments = $absolutePath->getSegments();

        // 2. Track points to know the "start" of each segment in the original path
        $points = [new \Atelier\Svg\Geometry\Point(0, 0)];
        foreach ($absSegments as $segment) {
            $target = $segment->getTargetPoint();
            if (null !== $target) {
                $points[] = $target;
            } else {
                // For ClosePath, the target is the start of the current subpath
                // For simplicity, we assume the first point for now
                $points[] = $points[1] ?? $points[0];
            }
        }

        $reversedSegments = [];
        $n = count($absSegments);

        // The new path starts at the last point of the original path
        $lastPoint = $points[count($points) - 1];
        $reversedSegments[] = new Segment\MoveTo('M', $lastPoint);

        for ($i = $n - 1; $i >= 1; --$i) {
            $segment = $absSegments[$i];
            $prevPoint = $points[$i]; // This was the start point for $segment

            if ($segment instanceof Segment\LineTo || $segment instanceof Segment\MoveTo) {
                $reversedSegments[] = new Segment\LineTo('L', $prevPoint);
            } elseif ($segment instanceof Segment\CurveTo) {
                // Reverse cubic bezier: swap control points
                $reversedSegments[] = new Segment\CurveTo(
                    'C',
                    $segment->getControlPoint2(),
                    $segment->getControlPoint1(),
                    $prevPoint
                );
            } elseif ($segment instanceof Segment\QuadraticCurveTo) {
                // Reverse quadratic bezier: control point remains same
                $reversedSegments[] = new Segment\QuadraticCurveTo(
                    'Q',
                    $segment->getControlPoint(),
                    $prevPoint
                );
            } elseif ($segment instanceof Segment\ClosePath) {
                // ClosePath in reverse becomes a LineTo the subpath start
                $reversedSegments[] = new Segment\LineTo('L', $prevPoint);
            } else {
                // Fallback for other types: convert to LineTo for now
                $reversedSegments[] = new Segment\LineTo('L', $prevPoint);
            }
        }

        return new self($reversedSegments);
    }

    /**
     * Gets a subpath from start to end segment index.
     */
    public function subpath(int $start, int $end): self
    {
        $subSegments = array_slice($this->segments, $start, $end - $start + 1);

        return new self($subSegments);
    }

    /**
     * Counts the number of segments in this path.
     */
    public function count(): int
    {
        return count($this->segments);
    }

    /**
     * Checks if the path is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->segments);
    }

    /**
     * Serializes the path data to a string.
     */
    public function toString(): string
    {
        $pathData = '';
        foreach ($this->segments as $segment) {
            $args = $segment->commandArgumentsToString();
            $pathData .= $segment->getCommand().('' !== $args ? ' '.$args : '').' ';
        }

        return trim($pathData);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
