<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Geometry\Transformation;
use Atelier\Svg\Path\Segment\ArcTo;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\HorizontalLineTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\QuadraticCurveTo;
use Atelier\Svg\Path\Segment\SegmentInterface;
use Atelier\Svg\Path\Segment\SmoothCurveTo;
use Atelier\Svg\Path\Segment\SmoothQuadraticCurveTo;
use Atelier\Svg\Path\Segment\VerticalLineTo;
use Atelier\Svg\Path\Simplifier\Simplifier;

/**
 * Helper class for path transformations and operations.
 *
 * Provides utilities for transforming paths (translate, scale, rotate),
 * and other path manipulation operations.
 */
final class PathUtils
{
    /**
     * Translates a path by the given offsets.
     */
    public static function translate(Data $path, float $dx, float $dy): Data
    {
        $matrix = Transformation::translate($dx, $dy);

        return self::transform($path, $matrix);
    }

    /**
     * Scales a path by the given factors.
     */
    public static function scale(Data $path, float $sx, ?float $sy = null): Data
    {
        $matrix = Transformation::scale($sx, $sy);

        return self::transform($path, $matrix);
    }

    /**
     * Rotates a path by the given angle around a center point.
     *
     * @param Data  $path  The path to rotate
     * @param float $angle Rotation angle in degrees
     * @param float $cx    Center X coordinate (default: 0)
     * @param float $cy    Center Y coordinate (default: 0)
     */
    public static function rotate(Data $path, float $angle, float $cx = 0, float $cy = 0): Data
    {
        $matrix = Transformation::rotate($angle, $cx, $cy);

        return self::transform($path, $matrix);
    }

    /**
     * Applies a transformation matrix to a path.
     */
    public static function transform(Data $path, Matrix $matrix): Data
    {
        $transformedSegments = [];

        foreach ($path->getSegments() as $segment) {
            $transformedSegments[] = self::transformSegment($segment, $matrix);
        }

        return new Data($transformedSegments);
    }

    /**
     * Transforms a single path segment.
     *
     * Note: For better performance when transforming complete paths with many segments,
     * use PathTransformer directly instead of this helper method.
     */
    private static function transformSegment(SegmentInterface $segment, Matrix $matrix): SegmentInterface
    {
        if ($segment instanceof MoveTo) {
            $point = $segment->getTargetPoint();
            $transformed = $matrix->transform($point);

            return new MoveTo($segment->getCommand(), $transformed);
        }

        if ($segment instanceof LineTo) {
            $point = $segment->getTargetPoint();
            $transformed = $matrix->transform($point);

            return new LineTo($segment->getCommand(), $transformed);
        }

        // For other segment types, use PathTransformer for full support
        // This creates a new transformer per segment which is less efficient
        // For bulk operations, use PathTransformer directly on the complete path
        $transformer = new PathTransformer();
        $singleSegmentData = new Data([$segment]);
        $transformedData = $transformer->transform($singleSegmentData, $matrix);
        $transformedSegments = $transformedData->getSegments();

        return !empty($transformedSegments) ? $transformedSegments[0] : $segment;
    }

    /**
     * Simplifies a path by removing redundant points.
     *
     * @param Data  $path      The path to simplify
     * @param float $tolerance Simplification tolerance (higher = more aggressive)
     */
    public static function simplify(Data $path, float $tolerance = 1.0): Data
    {
        // Use the dedicated Simplifier class
        $simplifier = new Simplifier();

        return $simplifier->simplify($path, $tolerance);
    }

    /**
     * Converts a path to absolute coordinates.
     *
     * Handles all segment types: M, L, H, V, C, S, Q, T, A, Z.
     */
    public static function toAbsolute(Data $path): Data
    {
        $absoluteSegments = [];
        $currentPoint = new Point(0, 0);
        $subpathStart = new Point(0, 0);

        foreach ($path->getSegments() as $segment) {
            $isRelative = $segment->isRelative();

            if ($segment instanceof MoveTo) {
                $point = $segment->getTargetPoint();
                if ($isRelative) {
                    $absolutePoint = $currentPoint->add($point);
                    $absoluteSegments[] = new MoveTo('M', $absolutePoint);
                    $currentPoint = $absolutePoint;
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = $point;
                }
                $subpathStart = $currentPoint;
            } elseif ($segment instanceof LineTo) {
                $point = $segment->getTargetPoint();
                if ($isRelative) {
                    $absolutePoint = $currentPoint->add($point);
                    $absoluteSegments[] = new LineTo('L', $absolutePoint);
                    $currentPoint = $absolutePoint;
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = $point;
                }
            } elseif ($segment instanceof HorizontalLineTo) {
                $x = $segment->getX();
                if ($isRelative) {
                    $absX = $currentPoint->x + $x;
                    $absoluteSegments[] = new HorizontalLineTo('H', $absX);
                    $currentPoint = new Point($absX, $currentPoint->y);
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = new Point($x, $currentPoint->y);
                }
            } elseif ($segment instanceof VerticalLineTo) {
                $y = $segment->getY();
                if ($isRelative) {
                    $absY = $currentPoint->y + $y;
                    $absoluteSegments[] = new VerticalLineTo('V', $absY);
                    $currentPoint = new Point($currentPoint->x, $absY);
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = new Point($currentPoint->x, $y);
                }
            } elseif ($segment instanceof CurveTo) {
                $cp1 = $segment->getControlPoint1();
                $cp2 = $segment->getControlPoint2();
                $point = $segment->getTargetPoint();
                if ($isRelative) {
                    $absoluteSegments[] = new CurveTo(
                        'C',
                        $currentPoint->add($cp1),
                        $currentPoint->add($cp2),
                        $currentPoint->add($point),
                    );
                    $currentPoint = $currentPoint->add($point);
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = $point;
                }
            } elseif ($segment instanceof SmoothCurveTo) {
                $cp2 = $segment->getControlPoint2();
                $point = $segment->getTargetPoint();
                if ($isRelative) {
                    $absoluteSegments[] = new SmoothCurveTo(
                        'S',
                        $currentPoint->add($cp2),
                        $currentPoint->add($point),
                    );
                    $currentPoint = $currentPoint->add($point);
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = $point;
                }
            } elseif ($segment instanceof QuadraticCurveTo) {
                $cp = $segment->getControlPoint();
                $point = $segment->getTargetPoint();
                if ($isRelative) {
                    $absoluteSegments[] = new QuadraticCurveTo(
                        'Q',
                        $currentPoint->add($cp),
                        $currentPoint->add($point),
                    );
                    $currentPoint = $currentPoint->add($point);
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = $point;
                }
            } elseif ($segment instanceof SmoothQuadraticCurveTo) {
                $point = $segment->getTargetPoint();
                if ($isRelative) {
                    $absoluteSegments[] = new SmoothQuadraticCurveTo('T', $currentPoint->add($point));
                    $currentPoint = $currentPoint->add($point);
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = $point;
                }
            } elseif ($segment instanceof ArcTo) {
                $point = $segment->getTargetPoint();
                if ($isRelative) {
                    $absoluteSegments[] = new ArcTo(
                        'A',
                        $segment->getRx(),
                        $segment->getRy(),
                        $segment->getXAxisRotation(),
                        $segment->getLargeArcFlag(),
                        $segment->getSweepFlag(),
                        $currentPoint->add($point),
                    );
                    $currentPoint = $currentPoint->add($point);
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = $point;
                }
            } elseif ($segment instanceof ClosePath) {
                $absoluteSegments[] = $segment;
                $currentPoint = $subpathStart;
            }
        }

        return new Data($absoluteSegments);
    }

    /**
     * Converts a path to relative coordinates.
     *
     * Handles all segment types: M, L, H, V, C, S, Q, T, A, Z.
     */
    public static function toRelative(Data $path): Data
    {
        $relativeSegments = [];
        $currentPoint = new Point(0, 0);
        $subpathStart = new Point(0, 0);

        foreach ($path->getSegments() as $segment) {
            $isRelative = $segment->isRelative();

            if ($segment instanceof MoveTo) {
                $point = $segment->getTargetPoint();
                if (!$isRelative) {
                    $relativePoint = $point->subtract($currentPoint);
                    $relativeSegments[] = new MoveTo('m', $relativePoint);
                    $currentPoint = $point;
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = $currentPoint->add($point);
                }
                $subpathStart = $currentPoint;
            } elseif ($segment instanceof LineTo) {
                $point = $segment->getTargetPoint();
                if (!$isRelative) {
                    $relativePoint = $point->subtract($currentPoint);
                    $relativeSegments[] = new LineTo('l', $relativePoint);
                    $currentPoint = $point;
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = $currentPoint->add($point);
                }
            } elseif ($segment instanceof HorizontalLineTo) {
                $x = $segment->getX();
                if (!$isRelative) {
                    $relativeSegments[] = new HorizontalLineTo('h', $x - $currentPoint->x);
                    $currentPoint = new Point($x, $currentPoint->y);
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = new Point($currentPoint->x + $x, $currentPoint->y);
                }
            } elseif ($segment instanceof VerticalLineTo) {
                $y = $segment->getY();
                if (!$isRelative) {
                    $relativeSegments[] = new VerticalLineTo('v', $y - $currentPoint->y);
                    $currentPoint = new Point($currentPoint->x, $y);
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = new Point($currentPoint->x, $currentPoint->y + $y);
                }
            } elseif ($segment instanceof CurveTo) {
                $cp1 = $segment->getControlPoint1();
                $cp2 = $segment->getControlPoint2();
                $point = $segment->getTargetPoint();
                if (!$isRelative) {
                    $relativeSegments[] = new CurveTo(
                        'c',
                        $cp1->subtract($currentPoint),
                        $cp2->subtract($currentPoint),
                        $point->subtract($currentPoint),
                    );
                    $currentPoint = $point;
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = $currentPoint->add($point);
                }
            } elseif ($segment instanceof SmoothCurveTo) {
                $cp2 = $segment->getControlPoint2();
                $point = $segment->getTargetPoint();
                if (!$isRelative) {
                    $relativeSegments[] = new SmoothCurveTo(
                        's',
                        $cp2->subtract($currentPoint),
                        $point->subtract($currentPoint),
                    );
                    $currentPoint = $point;
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = $currentPoint->add($point);
                }
            } elseif ($segment instanceof QuadraticCurveTo) {
                $cp = $segment->getControlPoint();
                $point = $segment->getTargetPoint();
                if (!$isRelative) {
                    $relativeSegments[] = new QuadraticCurveTo(
                        'q',
                        $cp->subtract($currentPoint),
                        $point->subtract($currentPoint),
                    );
                    $currentPoint = $point;
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = $currentPoint->add($point);
                }
            } elseif ($segment instanceof SmoothQuadraticCurveTo) {
                $point = $segment->getTargetPoint();
                if (!$isRelative) {
                    $relativeSegments[] = new SmoothQuadraticCurveTo('t', $point->subtract($currentPoint));
                    $currentPoint = $point;
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = $currentPoint->add($point);
                }
            } elseif ($segment instanceof ArcTo) {
                $point = $segment->getTargetPoint();
                if (!$isRelative) {
                    $relativeSegments[] = new ArcTo(
                        'a',
                        $segment->getRx(),
                        $segment->getRy(),
                        $segment->getXAxisRotation(),
                        $segment->getLargeArcFlag(),
                        $segment->getSweepFlag(),
                        $point->subtract($currentPoint),
                    );
                    $currentPoint = $point;
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = $currentPoint->add($point);
                }
            } elseif ($segment instanceof ClosePath) {
                $relativeSegments[] = $segment;
                $currentPoint = $subpathStart;
            }
        }

        return new Data($relativeSegments);
    }
}
