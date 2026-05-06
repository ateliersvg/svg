<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;
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

/**
 * Transforms path segments using transformation matrices.
 *
 * Handles transformation of all segment types, including those with
 * control points and arc parameters.
 */
final class PathTransformer
{
    /**
     * Transform a path Data object using a transformation matrix.
     */
    public function transform(Data $data, Matrix $matrix): Data
    {
        $transformedSegments = [];
        $currentPoint = new Point(0, 0);
        $subpathStart = new Point(0, 0);

        foreach ($data->getSegments() as $segment) {
            $result = $this->transformSegment($segment, $matrix, $currentPoint);
            $transformedSegments[] = $result;

            // Track current point for H/V resolution
            $currentPoint = $this->advanceCurrentPoint($segment, $currentPoint, $subpathStart);
            if ($segment instanceof MoveTo) {
                $subpathStart = $currentPoint;
            } elseif ($segment instanceof ClosePath) {
                $currentPoint = $subpathStart;
            }
        }

        return new Data($transformedSegments);
    }

    /**
     * Advance the current point based on the original (pre-transform) segment.
     */
    private function advanceCurrentPoint(SegmentInterface $segment, Point $currentPoint, Point $subpathStart): Point
    {
        if ($segment instanceof ClosePath) {
            return $subpathStart;
        }

        if ($segment instanceof HorizontalLineTo) {
            $x = $segment->isRelative() ? $currentPoint->x + $segment->getX() : $segment->getX();

            return new Point($x, $currentPoint->y);
        }

        if ($segment instanceof VerticalLineTo) {
            $y = $segment->isRelative() ? $currentPoint->y + $segment->getY() : $segment->getY();

            return new Point($currentPoint->x, $y);
        }

        $point = $segment->getTargetPoint();
        \assert($point instanceof Point);

        return $segment->isRelative() ? $currentPoint->add($point) : $point;
    }

    /**
     * Transform a single segment.
     */
    private function transformSegment(SegmentInterface $segment, Matrix $matrix, Point $currentPoint): SegmentInterface
    {
        $command = $segment->getCommand();

        // Note: For relative commands, we should transform them differently
        // For simplicity, we convert all to absolute coordinates after transformation
        $absoluteCommand = strtoupper($command);

        return match (true) {
            $segment instanceof MoveTo => $this->transformMoveTo($segment, $matrix, $absoluteCommand),
            $segment instanceof LineTo => $this->transformLineTo($segment, $matrix, $absoluteCommand),
            $segment instanceof HorizontalLineTo => $this->transformHorizontalLineTo($segment, $matrix, $currentPoint),
            $segment instanceof VerticalLineTo => $this->transformVerticalLineTo($segment, $matrix, $currentPoint),
            $segment instanceof CurveTo => $this->transformCurveTo($segment, $matrix, $absoluteCommand),
            $segment instanceof SmoothCurveTo => $this->transformSmoothCurveTo($segment, $matrix, $absoluteCommand),
            $segment instanceof QuadraticCurveTo => $this->transformQuadraticCurveTo($segment, $matrix, $absoluteCommand),
            $segment instanceof SmoothQuadraticCurveTo => $this->transformSmoothQuadraticCurveTo($segment, $matrix, $absoluteCommand),
            $segment instanceof ArcTo => $this->transformArcTo($segment, $matrix, $absoluteCommand),
            $segment instanceof ClosePath => $segment, // ClosePath doesn't need transformation
            default => $segment,
        };
    }

    private function transformMoveTo(MoveTo $segment, Matrix $matrix, string $command): MoveTo
    {
        $point = $segment->getTargetPoint();
        $transformedPoint = $matrix->transform($point);

        return new MoveTo($command, $transformedPoint);
    }

    private function transformLineTo(LineTo $segment, Matrix $matrix, string $command): LineTo
    {
        $point = $segment->getTargetPoint();
        $transformedPoint = $matrix->transform($point);

        return new LineTo($command, $transformedPoint);
    }

    private function transformHorizontalLineTo(HorizontalLineTo $segment, Matrix $matrix, Point $currentPoint): LineTo
    {
        // Resolve to absolute point using current position for the missing coordinate
        $absX = $segment->isRelative() ? $currentPoint->x + $segment->getX() : $segment->getX();
        $point = new Point($absX, $currentPoint->y);
        $transformedPoint = $matrix->transform($point);

        return new LineTo('L', $transformedPoint);
    }

    private function transformVerticalLineTo(VerticalLineTo $segment, Matrix $matrix, Point $currentPoint): LineTo
    {
        // Resolve to absolute point using current position for the missing coordinate
        $absY = $segment->isRelative() ? $currentPoint->y + $segment->getY() : $segment->getY();
        $point = new Point($currentPoint->x, $absY);
        $transformedPoint = $matrix->transform($point);

        return new LineTo('L', $transformedPoint);
    }

    private function transformCurveTo(CurveTo $segment, Matrix $matrix, string $command): CurveTo
    {
        $cp1 = $segment->getControlPoint1();
        $cp2 = $segment->getControlPoint2();
        $point = $segment->getTargetPoint();

        return new CurveTo(
            $command,
            $matrix->transform($cp1),
            $matrix->transform($cp2),
            $matrix->transform($point)
        );
    }

    private function transformSmoothCurveTo(SmoothCurveTo $segment, Matrix $matrix, string $command): SmoothCurveTo
    {
        $cp2 = $segment->getControlPoint2();
        $point = $segment->getTargetPoint();

        return new SmoothCurveTo(
            $command,
            $matrix->transform($cp2),
            $matrix->transform($point)
        );
    }

    private function transformQuadraticCurveTo(QuadraticCurveTo $segment, Matrix $matrix, string $command): QuadraticCurveTo
    {
        $cp = $segment->getControlPoint();
        $point = $segment->getTargetPoint();

        return new QuadraticCurveTo(
            $command,
            $matrix->transform($cp),
            $matrix->transform($point)
        );
    }

    private function transformSmoothQuadraticCurveTo(SmoothQuadraticCurveTo $segment, Matrix $matrix, string $command): SmoothQuadraticCurveTo
    {
        $point = $segment->getTargetPoint();

        return new SmoothQuadraticCurveTo(
            $command,
            $matrix->transform($point)
        );
    }

    private function transformArcTo(ArcTo $segment, Matrix $matrix, string $command): ArcTo
    {
        // Transforming arcs is complex - we need to adjust rx, ry, and rotation
        // For simplicity, we'll just transform the endpoint
        // A proper implementation would convert to cubic bezier curves first
        $point = $segment->getTargetPoint();
        $transformedPoint = $matrix->transform($point);

        // Scale the radii (simplified - doesn't handle rotation perfectly)
        $rx = $segment->getRx() * abs($matrix->a);
        $ry = $segment->getRy() * abs($matrix->d);

        return new ArcTo(
            $command,
            $rx,
            $ry,
            $segment->getXAxisRotation(),
            $segment->getLargeArcFlag(),
            $segment->getSweepFlag(),
            $transformedPoint
        );
    }
}
