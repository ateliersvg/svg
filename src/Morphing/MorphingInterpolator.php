<?php

declare(strict_types=1);

namespace Atelier\Svg\Morphing;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\SegmentInterface;

/**
 * Interpolates between two matched paths to create morphing animation.
 *
 * The interpolator works with normalized and matched paths to generate
 * intermediate shapes at any point between start (t=0) and end (t=1).
 */
final class MorphingInterpolator
{
    /**
     * Interpolates between two paths at parameter t.
     *
     * @param Data          $startPath      Normalized and matched start path
     * @param Data          $endPath        Normalized and matched end path
     * @param float         $t              Interpolation parameter (0 = start, 1 = end)
     * @param callable|null $easingFunction Optional easing function
     *
     * @return Data The interpolated path
     */
    public function interpolate(
        Data $startPath,
        Data $endPath,
        float $t,
        ?callable $easingFunction = null,
    ): Data {
        // Clamp t to [0, 1]
        $t = max(0, min(1, $t));

        // Apply easing if provided
        if (null !== $easingFunction) {
            $result = $easingFunction($t);
            if (is_numeric($result)) {
                $t = (float) $result;
            }
        }

        $startSegments = $startPath->getSegments();
        $endSegments = $endPath->getSegments();

        if (count($startSegments) !== count($endSegments)) {
            throw new InvalidArgumentException('Paths must have the same number of segments. Use PathMatcher first.');
        }

        $interpolated = [];

        foreach ($startSegments as $i => $startSegment) {
            $endSegment = $endSegments[$i];
            $interpolated[] = $this->interpolateSegment($startSegment, $endSegment, $t);
        }

        return new Data($interpolated);
    }

    /**
     * Interpolates a single segment.
     */
    private function interpolateSegment(
        SegmentInterface $start,
        SegmentInterface $end,
        float $t,
    ): SegmentInterface {
        // Both segments should be the same type after normalization
        if ($start::class !== $end::class) {
            throw new InvalidArgumentException('Cannot interpolate between different segment types: '.$start::class.' and '.$end::class);
        }

        if ($start instanceof MoveTo && $end instanceof MoveTo) {
            return new MoveTo(
                'M',
                $this->interpolatePoint($start->getTargetPoint(), $end->getTargetPoint(), $t)
            );
        }

        if ($start instanceof CurveTo && $end instanceof CurveTo) {
            return new CurveTo(
                'C',
                $this->interpolatePoint($start->getControlPoint1(), $end->getControlPoint1(), $t),
                $this->interpolatePoint($start->getControlPoint2(), $end->getControlPoint2(), $t),
                $this->interpolatePoint($start->getTargetPoint(), $end->getTargetPoint(), $t)
            );
        }

        if ($start instanceof ClosePath && $end instanceof ClosePath) {
            return new ClosePath('Z');
        }

        // Fallback for any other segment types
        return $start;
    }

    /**
     * Linearly interpolates between two points.
     */
    private function interpolatePoint(Point $p1, Point $p2, float $t): Point
    {
        return new Point(
            $p1->x + ($p2->x - $p1->x) * $t,
            $p1->y + ($p2->y - $p1->y) * $t
        );
    }

    /**
     * Generates multiple interpolated frames for animation.
     *
     * @param int           $frameCount     Number of frames to generate
     * @param callable|null $easingFunction Optional easing function
     *
     * @return Data[] Array of interpolated paths
     */
    public function generateFrames(
        Data $startPath,
        Data $endPath,
        int $frameCount,
        ?callable $easingFunction = null,
    ): array {
        $frames = [];

        for ($i = 0; $i < $frameCount; ++$i) {
            $t = $frameCount > 1 ? $i / ($frameCount - 1) : 0;
            $frames[] = $this->interpolate($startPath, $endPath, $t, $easingFunction);
        }

        return $frames;
    }

    // ===== Easing Functions =====

    /**
     * Linear easing (no easing).
     */
    public static function easeLinear(float $t): float
    {
        return $t;
    }

    /**
     * Ease in (slow start).
     */
    public static function easeIn(float $t): float
    {
        return $t * $t;
    }

    /**
     * Ease out (slow end).
     */
    public static function easeOut(float $t): float
    {
        return $t * (2 - $t);
    }

    /**
     * Ease in-out (slow start and end).
     */
    public static function easeInOut(float $t): float
    {
        return $t < 0.5
            ? 2 * $t * $t
            : -1 + (4 - 2 * $t) * $t;
    }

    /**
     * Cubic ease in.
     */
    public static function easeInCubic(float $t): float
    {
        return $t * $t * $t;
    }

    /**
     * Cubic ease out.
     */
    public static function easeOutCubic(float $t): float
    {
        $t1 = $t - 1;

        return $t1 * $t1 * $t1 + 1;
    }

    /**
     * Cubic ease in-out.
     */
    public static function easeInOutCubic(float $t): float
    {
        return $t < 0.5
            ? 4 * $t * $t * $t
            : 1 + 4 * ($t - 1) * ($t - 1) * ($t - 1);
    }

    /**
     * Elastic ease out (bouncy effect).
     */
    public static function easeOutElastic(float $t): float
    {
        $c4 = (2 * M_PI) / 3;

        return 0.0 === $t || 1.0 === $t
            ? $t
            : 2 ** (-10 * $t) * sin(($t * 10 - 0.75) * $c4) + 1;
    }

    /**
     * Back ease in (pulls back before moving forward).
     */
    public static function easeInBack(float $t): float
    {
        $c1 = 1.70158;
        $c3 = $c1 + 1;

        return $c3 * $t * $t * $t - $c1 * $t * $t;
    }

    /**
     * Back ease out (overshoots then settles).
     */
    public static function easeOutBack(float $t): float
    {
        $c1 = 1.70158;
        $c3 = $c1 + 1;

        return 1 + $c3 * ($t - 1) ** 3 + $c1 * ($t - 1) ** 2;
    }

    /**
     * Creates a custom cubic bezier easing function.
     *
     * @param float $x1 First control point x (0-1)
     * @param float $y1 First control point y (0-1)
     * @param float $x2 Second control point x (0-1)
     * @param float $y2 Second control point y (0-1)
     */
    public static function cubicBezierEasing(float $x1, float $y1, float $x2, float $y2): callable
    {
        return function (float $t) use ($y1, $y2): float {
            // Simplified cubic bezier evaluation
            // A full implementation would solve for t given x using Newton's method
            $t2 = $t * $t;
            $t3 = $t2 * $t;
            $mt = 1 - $t;
            $mt2 = $mt * $mt;
            $mt3 = $mt2 * $mt;

            return 3 * $mt2 * $t * $y1 + 3 * $mt * $t2 * $y2 + $t3;
        };
    }
}
