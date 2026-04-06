<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Geometry\Transformation;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\SegmentInterface;
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
     */
    public static function toAbsolute(Data $path): Data
    {
        $absoluteSegments = [];
        $currentPoint = new Point(0, 0);

        foreach ($path->getSegments() as $segment) {
            $command = $segment->getCommand();

            if ($segment instanceof MoveTo) {
                $point = $segment->getTargetPoint();
                if (ctype_lower($command)) {
                    // Relative command - convert to absolute
                    $absolutePoint = $currentPoint->add($point);
                    $absoluteSegments[] = new MoveTo('M', $absolutePoint);
                    $currentPoint = $absolutePoint;
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = $point;
                }
            } elseif ($segment instanceof LineTo) {
                $point = $segment->getTargetPoint();
                if (ctype_lower($command)) {
                    // Relative command - convert to absolute
                    $absolutePoint = $currentPoint->add($point);
                    $absoluteSegments[] = new LineTo('L', $absolutePoint);
                    $currentPoint = $absolutePoint;
                } else {
                    $absoluteSegments[] = $segment;
                    $currentPoint = $point;
                }
            } else {
                // For other segment types, keep as-is for now
                $absoluteSegments[] = $segment;
            }
        }

        return new Data($absoluteSegments);
    }

    /**
     * Converts a path to relative coordinates.
     */
    public static function toRelative(Data $path): Data
    {
        $relativeSegments = [];
        $currentPoint = new Point(0, 0);

        foreach ($path->getSegments() as $segment) {
            $command = $segment->getCommand();

            if ($segment instanceof MoveTo) {
                $point = $segment->getTargetPoint();
                if (ctype_upper($command)) {
                    // Absolute command - convert to relative
                    $relativePoint = new Point(
                        $point->x - $currentPoint->x,
                        $point->y - $currentPoint->y
                    );
                    $relativeSegments[] = new MoveTo('m', $relativePoint);
                    $currentPoint = $point;
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = $currentPoint->add($point);
                }
            } elseif ($segment instanceof LineTo) {
                $point = $segment->getTargetPoint();
                if (ctype_upper($command)) {
                    // Absolute command - convert to relative
                    $relativePoint = new Point(
                        $point->x - $currentPoint->x,
                        $point->y - $currentPoint->y
                    );
                    $relativeSegments[] = new LineTo('l', $relativePoint);
                    $currentPoint = $point;
                } else {
                    $relativeSegments[] = $segment;
                    $currentPoint = $currentPoint->add($point);
                }
            } else {
                // For other segment types, keep as-is for now
                $relativeSegments[] = $segment;
            }
        }

        return new Data($relativeSegments);
    }
}
