<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Geometry\BoundingBox;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\ArcTo;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\HorizontalLineTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\QuadraticCurveTo;
use Atelier\Svg\Path\Segment\SmoothCurveTo;
use Atelier\Svg\Path\Segment\SmoothQuadraticCurveTo;
use Atelier\Svg\Path\Segment\VerticalLineTo;

/**
 * Analyzes SVG paths to extract geometric information.
 *
 * Provides methods for calculating path length, points at specific lengths,
 * bounding boxes, and other geometric properties.
 */
final readonly class PathAnalyzer
{
    public function __construct(
        private Data $pathData,
    ) {
    }

    /**
     * Calculates the approximate total length of the path.
     *
     * Note: For curves and arcs, this uses numerical approximation with
     * adaptive subdivision for accuracy.
     */
    public function getLength(): float
    {
        $totalLength = 0;
        $currentPoint = new Point(0, 0);

        foreach ($this->pathData->getSegments() as $segment) {
            if ($segment instanceof MoveTo) {
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof LineTo) {
                $point = $segment->getTargetPoint();
                $dx = $point->x - $currentPoint->x;
                $dy = $point->y - $currentPoint->y;
                $totalLength += sqrt($dx * $dx + $dy * $dy);
                $currentPoint = $point;
            } elseif ($segment instanceof CurveTo) {
                $totalLength += $this->getCubicBezierLength(
                    $currentPoint,
                    $segment->getControlPoint1(),
                    $segment->getControlPoint2(),
                    $segment->getTargetPoint()
                );
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof QuadraticCurveTo) {
                $totalLength += $this->getQuadraticBezierLength(
                    $currentPoint,
                    $segment->getControlPoint(),
                    $segment->getTargetPoint()
                );
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof SmoothCurveTo) {
                // For smooth curves, approximate with a simpler curve
                // Note: Full implementation would track previous control point for proper reflection
                $approxLength = $this->getCubicBezierLength(
                    $currentPoint,
                    $currentPoint,
                    $segment->getControlPoint2(),
                    $segment->getTargetPoint()
                );
                $totalLength += $approxLength;
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof SmoothQuadraticCurveTo) {
                // For smooth quadratic, approximate as line
                $point = $segment->getTargetPoint();
                $dx = $point->x - $currentPoint->x;
                $dy = $point->y - $currentPoint->y;
                $totalLength += sqrt($dx * $dx + $dy * $dy);
                $currentPoint = $point;
            } elseif ($segment instanceof ArcTo) {
                $totalLength += $this->getArcLength(
                    $currentPoint,
                    $segment
                );
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof HorizontalLineTo) {
                $point = new Point($segment->getX(), $currentPoint->y);
                $totalLength += abs($point->x - $currentPoint->x);
                $currentPoint = $point;
            } elseif ($segment instanceof VerticalLineTo) {
                $point = new Point($currentPoint->x, $segment->getY());
                $totalLength += abs($point->y - $currentPoint->y);
                $currentPoint = $point;
            }
        }

        return $totalLength;
    }

    /**
     * Gets the point at a specific length along the path.
     *
     * @param float $length The length along the path
     *
     * @return Point|null The point at that length, or null if the length exceeds the path
     */
    public function getPointAtLength(float $length): ?Point
    {
        $accumulatedLength = 0;
        $currentPoint = new Point(0, 0);

        foreach ($this->pathData->getSegments() as $segment) {
            if ($segment instanceof MoveTo) {
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof LineTo) {
                $point = $segment->getTargetPoint();
                $dx = $point->x - $currentPoint->x;
                $dy = $point->y - $currentPoint->y;
                $segmentLength = sqrt($dx * $dx + $dy * $dy);

                if ($accumulatedLength + $segmentLength >= $length) {
                    // The target length is within this segment
                    $remainingLength = $length - $accumulatedLength;
                    $ratio = $segmentLength > 0 ? $remainingLength / $segmentLength : 0;

                    return new Point(
                        $currentPoint->x + $dx * $ratio,
                        $currentPoint->y + $dy * $ratio
                    );
                }

                $accumulatedLength += $segmentLength;
                $currentPoint = $point;
            } elseif ($segment instanceof CurveTo) {
                $segmentLength = $this->getCubicBezierLength(
                    $currentPoint,
                    $segment->getControlPoint1(),
                    $segment->getControlPoint2(),
                    $segment->getTargetPoint()
                );

                if ($accumulatedLength + $segmentLength >= $length) {
                    $remainingLength = $length - $accumulatedLength;
                    $t = $remainingLength / $segmentLength;

                    return $this->getCubicBezierPoint(
                        $currentPoint,
                        $segment->getControlPoint1(),
                        $segment->getControlPoint2(),
                        $segment->getTargetPoint(),
                        $t
                    );
                }

                $accumulatedLength += $segmentLength;
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof QuadraticCurveTo) {
                $segmentLength = $this->getQuadraticBezierLength(
                    $currentPoint,
                    $segment->getControlPoint(),
                    $segment->getTargetPoint()
                );

                if ($accumulatedLength + $segmentLength >= $length) {
                    $remainingLength = $length - $accumulatedLength;
                    $t = $remainingLength / $segmentLength;

                    return $this->getQuadraticBezierPoint(
                        $currentPoint,
                        $segment->getControlPoint(),
                        $segment->getTargetPoint(),
                        $t
                    );
                }

                $accumulatedLength += $segmentLength;
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof ArcTo) {
                $segmentLength = $this->getArcLength($currentPoint, $segment);

                if ($accumulatedLength + $segmentLength >= $length) {
                    $remainingLength = $length - $accumulatedLength;
                    $t = $segmentLength > 0 ? $remainingLength / $segmentLength : 0;

                    return $this->getArcPoint($currentPoint, $segment, $t);
                }

                $accumulatedLength += $segmentLength;
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof HorizontalLineTo) {
                $point = new Point($segment->getX(), $currentPoint->y);
                $segmentLength = abs($point->x - $currentPoint->x);

                if ($accumulatedLength + $segmentLength >= $length) {
                    $remainingLength = $length - $accumulatedLength;
                    $ratio = $segmentLength > 0 ? $remainingLength / $segmentLength : 0;

                    return new Point(
                        $currentPoint->x + ($point->x - $currentPoint->x) * $ratio,
                        $currentPoint->y
                    );
                }

                $accumulatedLength += $segmentLength;
                $currentPoint = $point;
            } elseif ($segment instanceof VerticalLineTo) {
                $point = new Point($currentPoint->x, $segment->getY());
                $segmentLength = abs($point->y - $currentPoint->y);

                if ($accumulatedLength + $segmentLength >= $length) {
                    $remainingLength = $length - $accumulatedLength;
                    $ratio = $segmentLength > 0 ? $remainingLength / $segmentLength : 0;

                    return new Point(
                        $currentPoint->x,
                        $currentPoint->y + ($point->y - $currentPoint->y) * $ratio
                    );
                }

                $accumulatedLength += $segmentLength;
                $currentPoint = $point;
            } else {
                // For other segment types, approximate as line to target
                $point = $segment->getTargetPoint();
                if (null !== $point) {
                    $dx = $point->x - $currentPoint->x;
                    $dy = $point->y - $currentPoint->y;
                    $segmentLength = sqrt($dx * $dx + $dy * $dy);

                    if ($accumulatedLength + $segmentLength >= $length) {
                        $remainingLength = $length - $accumulatedLength;
                        $ratio = $segmentLength > 0 ? $remainingLength / $segmentLength : 0;

                        return new Point(
                            $currentPoint->x + $dx * $ratio,
                            $currentPoint->y + $dy * $ratio
                        );
                    }

                    $accumulatedLength += $segmentLength;
                    $currentPoint = $point;
                }
            }
        }

        return null;
    }

    /**
     * Calculates the bounding box of the path.
     *
     * For curves, includes control points and samples along the curve for accuracy.
     */
    public function getBoundingBox(): BoundingBox
    {
        $points = [];
        $currentPoint = new Point(0, 0);

        foreach ($this->pathData->getSegments() as $segment) {
            if ($segment instanceof MoveTo || $segment instanceof LineTo) {
                $point = $segment->getTargetPoint();
                $points[] = $point;
                $currentPoint = $point;
            } elseif ($segment instanceof CurveTo) {
                // Include control points and target point
                $points[] = $segment->getControlPoint1();
                $points[] = $segment->getControlPoint2();
                $points[] = $segment->getTargetPoint();

                // Sample points along the curve for better accuracy
                for ($t = 0; $t <= 1; $t += 0.1) {
                    $points[] = $this->getCubicBezierPoint(
                        $currentPoint,
                        $segment->getControlPoint1(),
                        $segment->getControlPoint2(),
                        $segment->getTargetPoint(),
                        $t
                    );
                }

                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof QuadraticCurveTo) {
                // Include control point and target point
                $points[] = $segment->getControlPoint();
                $points[] = $segment->getTargetPoint();

                // Sample points along the curve
                for ($t = 0; $t <= 1; $t += 0.1) {
                    $points[] = $this->getQuadraticBezierPoint(
                        $currentPoint,
                        $segment->getControlPoint(),
                        $segment->getTargetPoint(),
                        $t
                    );
                }

                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof SmoothCurveTo) {
                $points[] = $segment->getControlPoint2();
                $points[] = $segment->getTargetPoint();
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof SmoothQuadraticCurveTo) {
                $points[] = $segment->getTargetPoint();
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof ArcTo) {
                // Sample points along the arc
                for ($t = 0; $t <= 1; $t += 0.1) {
                    $points[] = $this->getArcPoint($currentPoint, $segment, $t);
                }
                $points[] = $segment->getTargetPoint();
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof HorizontalLineTo) {
                $point = new Point($segment->getX(), $currentPoint->y);
                $points[] = $point;
                $currentPoint = $point;
            } elseif ($segment instanceof VerticalLineTo) {
                $point = new Point($currentPoint->x, $segment->getY());
                $points[] = $point;
                $currentPoint = $point;
            } else {
                $point = $segment->getTargetPoint();
                if (null !== $point) {
                    $points[] = $point;
                    $currentPoint = $point;
                }
            }
        }

        if (empty($points)) {
            return new BoundingBox(0, 0, 0, 0);
        }

        return BoundingBox::fromPoints(...$points);
    }

    /**
     * Checks if a point is inside the path (using even-odd rule).
     *
     * This is a simplified implementation that works for simple polygons.
     */
    public function containsPoint(Point $point): bool
    {
        // Simple ray-casting algorithm
        // Cast a ray from the point to infinity and count intersections
        $intersections = 0;
        $vertices = $this->getVertices();

        for ($i = 0, $j = count($vertices) - 1; $i < count($vertices); $j = $i++) {
            $vi = $vertices[$i];
            $vj = $vertices[$j];

            if ((($vi->y > $point->y) !== ($vj->y > $point->y))
                && ($point->x < ($vj->x - $vi->x) * ($point->y - $vi->y) / ($vj->y - $vi->y) + $vi->x)) {
                ++$intersections;
            }
        }

        return ($intersections % 2) === 1;
    }

    /**
     * Gets all vertices (points) from the path.
     *
     * For curves, samples multiple points along the curve for better representation.
     *
     * @return array<Point>
     */
    public function getVertices(): array
    {
        $vertices = [];
        $currentPoint = new Point(0, 0);

        foreach ($this->pathData->getSegments() as $segment) {
            if ($segment instanceof MoveTo || $segment instanceof LineTo) {
                $vertices[] = $segment->getTargetPoint();
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof CurveTo) {
                // Sample points along the curve
                for ($t = 0.2; $t <= 1; $t += 0.2) {
                    $vertices[] = $this->getCubicBezierPoint(
                        $currentPoint,
                        $segment->getControlPoint1(),
                        $segment->getControlPoint2(),
                        $segment->getTargetPoint(),
                        $t
                    );
                }
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof QuadraticCurveTo) {
                // Sample points along the curve
                for ($t = 0.2; $t <= 1; $t += 0.2) {
                    $vertices[] = $this->getQuadraticBezierPoint(
                        $currentPoint,
                        $segment->getControlPoint(),
                        $segment->getTargetPoint(),
                        $t
                    );
                }
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof ArcTo) {
                // Sample points along the arc
                for ($t = 0.2; $t <= 1; $t += 0.2) {
                    $vertices[] = $this->getArcPoint($currentPoint, $segment, $t);
                }
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof HorizontalLineTo) {
                $point = new Point($segment->getX(), $currentPoint->y);
                $vertices[] = $point;
                $currentPoint = $point;
            } elseif ($segment instanceof VerticalLineTo) {
                $point = new Point($currentPoint->x, $segment->getY());
                $vertices[] = $point;
                $currentPoint = $point;
            } else {
                $point = $segment->getTargetPoint();
                if (null !== $point) {
                    $vertices[] = $point;
                    $currentPoint = $point;
                }
            }
        }

        return $vertices;
    }

    /**
     * Gets the center point of the path's bounding box.
     */
    public function getCenter(): Point
    {
        return $this->getBoundingBox()->getCenter();
    }

    /**
     * Calculates the length of a cubic Bezier curve using adaptive subdivision.
     *
     * @param Point $p0 Start point
     * @param Point $p1 First control point
     * @param Point $p2 Second control point
     * @param Point $p3 End point
     *
     * @return float The approximate length
     */
    private function getCubicBezierLength(Point $p0, Point $p1, Point $p2, Point $p3): float
    {
        // Use adaptive subdivision with multiple samples
        $steps = 20;
        $length = 0.0;
        $prevPoint = $p0;

        for ($i = 1; $i <= $steps; ++$i) {
            $t = $i / $steps;
            $point = $this->getCubicBezierPoint($p0, $p1, $p2, $p3, $t);
            $dx = $point->x - $prevPoint->x;
            $dy = $point->y - $prevPoint->y;
            $length += sqrt($dx * $dx + $dy * $dy);
            $prevPoint = $point;
        }

        return $length;
    }

    /**
     * Gets a point on a cubic Bezier curve at parameter t.
     *
     * @param Point $p0 Start point
     * @param Point $p1 First control point
     * @param Point $p2 Second control point
     * @param Point $p3 End point
     * @param float $t  Parameter (0 to 1)
     *
     * @return Point The point at parameter t
     */
    private function getCubicBezierPoint(Point $p0, Point $p1, Point $p2, Point $p3, float $t): Point
    {
        $t2 = $t * $t;
        $t3 = $t2 * $t;
        $mt = 1 - $t;
        $mt2 = $mt * $mt;
        $mt3 = $mt2 * $mt;

        return new Point(
            $mt3 * $p0->x + 3 * $mt2 * $t * $p1->x + 3 * $mt * $t2 * $p2->x + $t3 * $p3->x,
            $mt3 * $p0->y + 3 * $mt2 * $t * $p1->y + 3 * $mt * $t2 * $p2->y + $t3 * $p3->y
        );
    }

    /**
     * Calculates the length of a quadratic Bezier curve.
     *
     * @param Point $p0 Start point
     * @param Point $p1 Control point
     * @param Point $p2 End point
     *
     * @return float The approximate length
     */
    private function getQuadraticBezierLength(Point $p0, Point $p1, Point $p2): float
    {
        $steps = 20;
        $length = 0.0;
        $prevPoint = $p0;

        for ($i = 1; $i <= $steps; ++$i) {
            $t = $i / $steps;
            $point = $this->getQuadraticBezierPoint($p0, $p1, $p2, $t);
            $dx = $point->x - $prevPoint->x;
            $dy = $point->y - $prevPoint->y;
            $length += sqrt($dx * $dx + $dy * $dy);
            $prevPoint = $point;
        }

        return $length;
    }

    /**
     * Gets a point on a quadratic Bezier curve at parameter t.
     *
     * @param Point $p0 Start point
     * @param Point $p1 Control point
     * @param Point $p2 End point
     * @param float $t  Parameter (0 to 1)
     *
     * @return Point The point at parameter t
     */
    private function getQuadraticBezierPoint(Point $p0, Point $p1, Point $p2, float $t): Point
    {
        $t2 = $t * $t;
        $mt = 1 - $t;
        $mt2 = $mt * $mt;

        return new Point(
            $mt2 * $p0->x + 2 * $mt * $t * $p1->x + $t2 * $p2->x,
            $mt2 * $p0->y + 2 * $mt * $t * $p1->y + $t2 * $p2->y
        );
    }

    /**
     * Calculates the approximate length of an elliptical arc.
     *
     * @param Point $start Start point
     * @param ArcTo $arc   Arc segment
     *
     * @return float The approximate length
     */
    private function getArcLength(Point $start, ArcTo $arc): float
    {
        // Use Ramanujan's approximation for ellipse perimeter
        // For a full ellipse: π[3(a+b) - √((3a+b)(a+3b))]
        // We scale by the angle swept

        $rx = $arc->getRx();
        $ry = $arc->getRy();
        $end = $arc->getTargetPoint();

        // Simple approximation: use multiple line segments
        $steps = 20;
        $length = 0.0;
        $prevPoint = $start;

        for ($i = 1; $i <= $steps; ++$i) {
            $t = $i / $steps;
            $point = $this->getArcPoint($start, $arc, $t);
            $dx = $point->x - $prevPoint->x;
            $dy = $point->y - $prevPoint->y;
            $length += sqrt($dx * $dx + $dy * $dy);
            $prevPoint = $point;
        }

        return $length;
    }

    /**
     * Gets a point on an elliptical arc at parameter t.
     *
     * Note: This is a simplified implementation using linear interpolation.
     * For production use with precise arc calculations, consider converting
     * arcs to cubic Bezier curves first using the morphing PathNormalizer
     * (Atelier\Svg\Morphing\PathNormalizer), which converts arcs to
     * cubic curves for accurate interpolation.
     *
     * @param Point $start Start point
     * @param ArcTo $arc   Arc segment
     * @param float $t     Parameter (0 to 1)
     *
     * @return Point The point at parameter t
     */
    private function getArcPoint(Point $start, ArcTo $arc, float $t): Point
    {
        $end = $arc->getTargetPoint();

        // Simplified linear interpolation between start and end
        // A full implementation would calculate the actual elliptical arc path
        // considering rx, ry, rotation, large-arc-flag, and sweep-flag

        return new Point(
            $start->x + ($end->x - $start->x) * $t,
            $start->y + ($end->y - $start->y) * $t
        );
    }
}
