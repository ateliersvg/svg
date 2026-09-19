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

    /**
     * Transforms an elliptical arc under an arbitrary affine matrix.
     *
     * An SVG arc is an ellipse, and an ellipse is the image of the unit circle
     * under R(rotation) . diag(rx, ry). Applying the matrix means composing with
     * its linear part, then reading the radii and the rotation back out of the
     * product. Rotation, skew, non-uniform scale and reflection are all exact.
     */
    private function transformArcTo(ArcTo $segment, Matrix $matrix, string $command): ArcTo
    {
        $point = $matrix->transform($segment->getTargetPoint());
        $rx = $segment->getRx();
        $ry = $segment->getRy();
        $rotation = $segment->getXAxisRotation();
        $sweepFlag = $segment->getSweepFlag();

        // A reflection reverses the direction the arc is drawn in.
        if ($matrix->determinant() < 0.0) {
            $sweepFlag = !$sweepFlag;
        }

        // A zero radius degenerates the arc into a straight line, and SVG says
        // to draw it as one. There is no ellipse left to transform.
        if ($rx <= 0.0 || $ry <= 0.0) {
            return new ArcTo($command, $rx, $ry, $rotation, $segment->getLargeArcFlag(), $sweepFlag, $point);
        }

        $phi = deg2rad($rotation);
        $cos = cos($phi);
        $sin = sin($phi);

        // The ellipse as R(phi) . diag(rx, ry), then the matrix linear part
        // [[a, c], [b, d]] applied on the left.
        $e11 = $cos * $rx;
        $e12 = -$sin * $ry;
        $e21 = $sin * $rx;
        $e22 = $cos * $ry;

        $s11 = $matrix->a * $e11 + $matrix->c * $e21;
        $s12 = $matrix->a * $e12 + $matrix->c * $e22;
        $s21 = $matrix->b * $e11 + $matrix->d * $e21;
        $s22 = $matrix->b * $e12 + $matrix->d * $e22;

        // Closed-form 2x2 singular value decomposition. The singular values are
        // the semi-axes of the transformed ellipse, and the left rotation angle
        // is where its major axis now points.
        $sum = ($s11 + $s22) / 2.0;
        $diff = ($s11 - $s22) / 2.0;
        $crossSum = ($s21 + $s12) / 2.0;
        $crossDiff = ($s21 - $s12) / 2.0;

        $outer = sqrt($sum * $sum + $crossDiff * $crossDiff);
        $inner = sqrt($diff * $diff + $crossSum * $crossSum);

        return new ArcTo(
            $command,
            $outer + $inner,
            abs($outer - $inner),
            rad2deg(atan2($crossDiff, $sum) + atan2($crossSum, $diff)) / 2.0,
            $segment->getLargeArcFlag(),
            $sweepFlag,
            $point
        );
    }
}
