<?php

declare(strict_types=1);

namespace Atelier\Svg\Morphing;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\SegmentInterface;

/**
 * Matches points between two paths to make them compatible for morphing.
 *
 * The matcher ensures both paths have the same number of segments by:
 * - Subdividing curves in the simpler path
 * - Maintaining the overall shape
 */
final class PathMatcher
{
    /**
     * Makes two paths compatible by ensuring they have the same structure.
     *
     * Returns a tuple of [startPath, endPath] with matched segments.
     *
     * @return array{0: Data, 1: Data}
     */
    public function match(Data $startPath, Data $endPath): array
    {
        // First, ensure both paths are normalized
        // (assume they are already normalized to M, C, Z commands)

        $startSegments = $startPath->getSegments();
        $endSegments = $endPath->getSegments();

        $startCount = count($startSegments);
        $endCount = count($endSegments);

        // If already equal, return as-is
        if ($startCount === $endCount) {
            return [$startPath, $endPath];
        }

        // Determine which path needs more segments
        if ($startCount < $endCount) {
            $startSegments = $this->subdivide($startSegments, $endCount);
        } else {
            $endSegments = $this->subdivide($endSegments, $startCount);
        }

        return [
            new Data($startSegments),
            new Data($endSegments),
        ];
    }

    /**
     * Subdivides path segments to reach target count.
     *
     * @param SegmentInterface[] $segments
     *
     * @return SegmentInterface[]
     */
    private function subdivide(array $segments, int $targetCount): array
    {
        $currentCount = count($segments);

        assert($currentCount < $targetCount);

        $result = [];
        $segmentsToAdd = $targetCount - $currentCount;

        // Group segments by type for smart subdivision
        $curves = [];
        $curveIndices = [];

        foreach ($segments as $i => $segment) {
            if ($segment instanceof CurveTo) {
                $curves[] = $segment;
                $curveIndices[] = $i;
            }
        }

        // Calculate how many times to subdivide each curve
        $curvesCount = count($curves);
        if (0 === $curvesCount) {
            // No curves to subdivide, just duplicate segments proportionally
            return $this->duplicateSegments($segments, $targetCount);
        }

        $subdivisionsPerCurve = intdiv($segmentsToAdd, $curvesCount);
        $remainder = $segmentsToAdd % $curvesCount;

        // Track which curves get extra subdivisions
        $subdivisionCounts = array_fill(0, $curvesCount, $subdivisionsPerCurve);
        for ($i = 0; $i < $remainder; ++$i) {
            ++$subdivisionCounts[$i];
        }

        // Build result with subdivided curves
        $curveIdx = 0;
        $previousPoint = new Point(0, 0);

        foreach ($segments as $i => $segment) {
            if ($segment instanceof MoveTo) {
                $result[] = $segment;
                $previousPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof CurveTo) {
                if (in_array($i, $curveIndices, true)) {
                    $subdivisions = $subdivisionCounts[$curveIdx] + 1; // +1 because we split into N+1 curves
                    $subdivided = $this->subdivideCurve($segment, $previousPoint, $subdivisions);
                    $result = array_merge($result, $subdivided);
                    ++$curveIdx;
                }
                $previousPoint = $segment->getTargetPoint();
            } else {
                $result[] = $segment;
            }
        }

        return $result;
    }

    /**
     * Subdivides a cubic bezier curve into multiple curves.
     *
     * Uses De Casteljau's algorithm for curve subdivision.
     *
     * @return CurveTo[]
     */
    private function subdivideCurve(CurveTo $curve, Point $startPoint, int $count): array
    {
        if ($count <= 1) {
            return [$curve];
        }

        $result = [];
        $p0 = $startPoint;
        $p1 = $curve->getControlPoint1();
        $p2 = $curve->getControlPoint2();
        $p3 = $curve->getTargetPoint();

        for ($i = 0; $i < $count; ++$i) {
            $t1 = $i / $count;
            $t2 = ($i + 1) / $count;

            // Extract subcurve from t1 to t2
            // This is a simplified approach; a full implementation would use
            // De Casteljau's algorithm for more accuracy

            $subCurve = $this->extractSubcurve($p0, $p1, $p2, $p3, $t1, $t2);
            $result[] = $subCurve;

            // Update start point for next iteration
            $p0 = $subCurve->getTargetPoint();
        }

        return $result;
    }

    /**
     * Extracts a subcurve from a cubic bezier curve.
     *
     * Uses the fact that a subcurve of a bezier curve is also a bezier curve.
     */
    private function extractSubcurve(
        Point $p0,
        Point $p1,
        Point $p2,
        Point $p3,
        float $t1,
        float $t2,
    ): CurveTo {
        // First, get curve at t1
        [$left, $right] = $this->splitCurveAt($p0, $p1, $p2, $p3, $t1);

        // Then split the right portion at adjusted t
        assert($t1 < 0.9999);

        // Calculate start point of right curve (the split point at t1)
        // This is p0123 from the De Casteljau algorithm
        $p01 = $this->lerp($p0, $p1, $t1);
        $p12 = $this->lerp($p1, $p2, $t1);
        $p23 = $this->lerp($p2, $p3, $t1);
        $p012 = $this->lerp($p01, $p12, $t1);
        $p123 = $this->lerp($p12, $p23, $t1);
        $rightStart = $this->lerp($p012, $p123, $t1);

        $adjustedT = ($t2 - $t1) / (1 - $t1);
        [$subcurve, $_] = $this->splitCurveAt(
            $rightStart,
            $right->getControlPoint1(),
            $right->getControlPoint2(),
            $right->getTargetPoint(),
            $adjustedT
        );

        return $subcurve;
    }

    /**
     * Splits a cubic bezier curve at parameter t using De Casteljau's algorithm.
     *
     * @return array{0: CurveTo, 1: CurveTo} [left curve, right curve]
     */
    private function splitCurveAt(Point $p0, Point $p1, Point $p2, Point $p3, float $t): array
    {
        // De Casteljau's algorithm
        $p01 = $this->lerp($p0, $p1, $t);
        $p12 = $this->lerp($p1, $p2, $t);
        $p23 = $this->lerp($p2, $p3, $t);

        $p012 = $this->lerp($p01, $p12, $t);
        $p123 = $this->lerp($p12, $p23, $t);

        $p0123 = $this->lerp($p012, $p123, $t);

        $left = new CurveTo('C', $p01, $p012, $p0123);
        $right = new CurveTo('C', $p123, $p23, $p3);

        return [$left, $right];
    }

    /**
     * Linear interpolation between two points.
     */
    private function lerp(Point $p1, Point $p2, float $t): Point
    {
        return new Point(
            $p1->x + ($p2->x - $p1->x) * $t,
            $p1->y + ($p2->y - $p1->y) * $t
        );
    }

    /**
     * Duplicates segments proportionally to reach target count.
     *
     * @param SegmentInterface[] $segments
     *
     * @return SegmentInterface[]
     */
    private function duplicateSegments(array $segments, int $targetCount): array
    {
        $result = [];
        $currentCount = count($segments);
        $ratio = $targetCount / $currentCount;

        foreach ($segments as $segment) {
            $duplicates = max(1, (int) round($ratio));
            for ($i = 0; $i < $duplicates; ++$i) {
                $result[] = $segment;
            }
        }

        // Trim or pad to exact count
        while (count($result) > $targetCount) {
            array_pop($result);
        }

        while (count($result) < $targetCount) {
            $lastSegment = $segments[count($segments) - 1] ?? $segments[0] ?? null;
            assert(null !== $lastSegment);
            $result[] = $lastSegment;
        }

        return $result;
    }
}
