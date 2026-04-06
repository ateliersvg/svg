<?php

declare(strict_types=1);

namespace Atelier\Svg\Morphing;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\ArcTo;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\QuadraticCurveTo;
use Atelier\Svg\Path\Segment\SegmentInterface;
use Atelier\Svg\Path\Segment\SmoothCurveTo;
use Atelier\Svg\Path\Segment\SmoothQuadraticCurveTo;

/**
 * Normalizes SVG paths for morphing compatibility.
 *
 * Normalization includes:
 * - Converting relative commands to absolute
 * - Expanding shorthand commands to full form
 * - Converting arcs to cubic bezier approximations
 * - Ensuring consistent command structure
 */
final class PathNormalizer
{
    private Point $currentPoint;
    private Point $startPoint;
    private ?Point $lastControlPoint = null;

    public function __construct()
    {
        $this->currentPoint = new Point(0, 0);
        $this->startPoint = new Point(0, 0);
    }

    /**
     * Normalizes a path to absolute cubic bezier commands.
     *
     * This converts all commands to absolute coordinates and uses only:
     * - M (MoveTo)
     * - C (CurveTo - cubic bezier)
     * - Z (ClosePath)
     */
    public function normalize(Data $path): Data
    {
        $this->currentPoint = new Point(0, 0);
        $this->startPoint = new Point(0, 0);
        $this->lastControlPoint = null;

        $normalized = [];

        foreach ($path->getSegments() as $segment) {
            $normalized = array_merge($normalized, $this->normalizeSegment($segment));
        }

        return new Data($normalized);
    }

    /**
     * Normalizes a single segment to absolute form.
     *
     * @return SegmentInterface[]
     */
    private function normalizeSegment(SegmentInterface $segment): array
    {
        $absolutePoint = $this->getAbsolutePoint($segment);

        // Handle each command type
        if ($segment instanceof MoveTo) {
            $this->startPoint = $absolutePoint;
            $this->currentPoint = $absolutePoint;
            $this->lastControlPoint = null;

            return [new MoveTo('M', $absolutePoint)];
        }

        if ($segment instanceof LineTo) {
            // Convert line to cubic bezier (flat curve)
            $result = $this->lineToCubic($this->currentPoint, $absolutePoint);
            $this->currentPoint = $absolutePoint;
            $this->lastControlPoint = $result->getControlPoint2();

            return [$result];
        }

        if ($segment instanceof CurveTo) {
            $cp1 = $this->resolveControlPoint($segment->getControlPoint1(), $segment->isRelative());
            $cp2 = $this->resolveControlPoint($segment->getControlPoint2(), $segment->isRelative());
            $result = new CurveTo('C', $cp1, $cp2, $absolutePoint);
            $this->lastControlPoint = $cp2;
            $this->currentPoint = $absolutePoint;

            return [$result];
        }

        if ($segment instanceof SmoothCurveTo) {
            // Expand S to C by reflecting last control point
            $cp1 = $this->reflectControlPoint();
            $cp2 = $this->resolveControlPoint($segment->getControlPoint2(), $segment->isRelative());
            $result = new CurveTo('C', $cp1, $cp2, $absolutePoint);
            $this->lastControlPoint = $cp2;
            $this->currentPoint = $absolutePoint;

            return [$result];
        }

        if ($segment instanceof QuadraticCurveTo) {
            // Convert quadratic to cubic bezier
            $cp = $this->resolveControlPoint($segment->getControlPoint(), $segment->isRelative());
            $result = $this->quadraticToCubic($this->currentPoint, $cp, $absolutePoint);
            $this->lastControlPoint = $result->getControlPoint2();
            $this->currentPoint = $absolutePoint;

            return [$result];
        }

        if ($segment instanceof SmoothQuadraticCurveTo) {
            // Expand T to Q then to C
            $cp = $this->reflectControlPoint();
            $result = $this->quadraticToCubic($this->currentPoint, $cp, $absolutePoint);
            $this->lastControlPoint = $result->getControlPoint2();
            $this->currentPoint = $absolutePoint;

            return [$result];
        }

        if ($segment instanceof ArcTo) {
            // Convert arc to cubic bezier curves (may produce multiple curves)
            $arcs = $this->arcToCubic($segment, $absolutePoint);
            $this->currentPoint = $absolutePoint;
            if (!empty($arcs)) {
                $this->lastControlPoint = end($arcs)->getControlPoint2();
            }

            return $arcs;
        }

        if ($segment instanceof ClosePath) {
            // Close path with line to start
            if (!$this->currentPoint->equals($this->startPoint)) {
                $result = $this->lineToCubic($this->currentPoint, $this->startPoint);
                $this->currentPoint = $this->startPoint;

                return [$result, new ClosePath('Z')];
            }

            return [new ClosePath('Z')];
        }

        return [];
    }

    /**
     * Gets the absolute target point from a segment.
     */
    private function getAbsolutePoint(SegmentInterface $segment): Point
    {
        $point = $segment->getTargetPoint();
        if (null === $point) {
            return $this->currentPoint;
        }

        if ($segment->isRelative()) {
            return new Point(
                $this->currentPoint->x + $point->x,
                $this->currentPoint->y + $point->y
            );
        }

        return $point;
    }

    /**
     * Resolves a control point to absolute coordinates.
     */
    private function resolveControlPoint(Point $point, bool $isRelative): Point
    {
        if ($isRelative) {
            return new Point(
                $this->currentPoint->x + $point->x,
                $this->currentPoint->y + $point->y
            );
        }

        return $point;
    }

    /**
     * Reflects the last control point for smooth curves.
     */
    private function reflectControlPoint(): Point
    {
        if (null === $this->lastControlPoint) {
            return clone $this->currentPoint;
        }

        return new Point(
            2 * $this->currentPoint->x - $this->lastControlPoint->x,
            2 * $this->currentPoint->y - $this->lastControlPoint->y
        );
    }

    /**
     * Converts a line segment to a cubic bezier (flat curve).
     */
    private function lineToCubic(Point $start, Point $end): CurveTo
    {
        // Place control points 1/3 and 2/3 along the line
        $cp1 = new Point(
            $start->x + ($end->x - $start->x) / 3,
            $start->y + ($end->y - $start->y) / 3
        );

        $cp2 = new Point(
            $start->x + 2 * ($end->x - $start->x) / 3,
            $start->y + 2 * ($end->y - $start->y) / 3
        );

        return new CurveTo('C', $cp1, $cp2, $end);
    }

    /**
     * Converts a quadratic bezier to cubic bezier.
     *
     * Formula:
     * CP1 = QP0 + 2/3 * (QP1 - QP0)
     * CP2 = QP2 + 2/3 * (QP1 - QP2)
     */
    private function quadraticToCubic(Point $start, Point $control, Point $end): CurveTo
    {
        $cp1 = new Point(
            $start->x + 2 / 3 * ($control->x - $start->x),
            $start->y + 2 / 3 * ($control->y - $start->y)
        );

        $cp2 = new Point(
            $end->x + 2 / 3 * ($control->x - $end->x),
            $end->y + 2 / 3 * ($control->y - $end->y)
        );

        return new CurveTo('C', $cp1, $cp2, $end);
    }

    /**
     * Converts an arc to one or more cubic bezier curves.
     *
     * @return CurveTo[]
     */
    private function arcToCubic(ArcTo $arc, Point $end): array
    {
        // This is a simplified implementation
        // A full implementation would properly handle arc parameters
        // and convert to multiple bezier curves for better approximation

        // For now, create a simple curve approximation
        $start = $this->currentPoint;

        // Create control points that approximate the arc
        $midX = ($start->x + $end->x) / 2;
        $midY = ($start->y + $end->y) / 2;

        // Offset perpendicular to the line
        $dx = $end->x - $start->x;
        $dy = $end->y - $start->y;
        $len = sqrt($dx * $dx + $dy * $dy);

        if ($len < 0.0001) {
            return [$this->lineToCubic($start, $end)];
        }

        $perpX = -$dy / $len;
        $perpY = $dx / $len;

        // Use arc radius to determine bulge
        $rx = $arc->getRx();
        $bulge = $rx * 0.5; // Simplified

        $cp1 = new Point(
            $start->x + $dx / 3 + $perpX * $bulge,
            $start->y + $dy / 3 + $perpY * $bulge
        );

        $cp2 = new Point(
            $start->x + 2 * $dx / 3 + $perpX * $bulge,
            $start->y + 2 * $dy / 3 + $perpY * $bulge
        );

        return [new CurveTo('C', $cp1, $cp2, $end)];
    }
}
