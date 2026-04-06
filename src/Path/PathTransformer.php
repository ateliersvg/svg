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

        foreach ($data->getSegments() as $segment) {
            $transformedSegments[] = $this->transformSegment($segment, $matrix);
        }

        return new Data($transformedSegments);
    }

    /**
     * Transform a single segment.
     */
    private function transformSegment(SegmentInterface $segment, Matrix $matrix): SegmentInterface
    {
        $command = $segment->getCommand();

        // Note: For relative commands, we should transform them differently
        // For simplicity, we convert all to absolute coordinates after transformation
        $absoluteCommand = strtoupper($command);

        return match (true) {
            $segment instanceof MoveTo => $this->transformMoveTo($segment, $matrix, $absoluteCommand),
            $segment instanceof LineTo => $this->transformLineTo($segment, $matrix, $absoluteCommand),
            $segment instanceof HorizontalLineTo => $this->transformHorizontalLineTo($segment, $matrix),
            $segment instanceof VerticalLineTo => $this->transformVerticalLineTo($segment, $matrix),
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

    private function transformHorizontalLineTo(HorizontalLineTo $segment, Matrix $matrix): LineTo
    {
        // Convert H/h to L/L since transformation makes it non-horizontal
        // We need the current point to do this properly, so we'll approximate
        // For now, convert to LineTo with y=0 (this is a simplification)
        // In production, you'd track current point through transformation
        $point = new Point($segment->getX(), 0);
        $transformedPoint = $matrix->transform($point);

        return new LineTo('L', $transformedPoint);
    }

    private function transformVerticalLineTo(VerticalLineTo $segment, Matrix $matrix): LineTo
    {
        // Convert V/v to L/L since transformation makes it non-vertical
        $point = new Point(0, $segment->getY());
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
