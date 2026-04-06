<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Simplifier;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;

final class CollinearPointRemover implements SimplifierInterface
{
    public function simplify(Data $pathData, float $tolerance): Data
    {
        if ($tolerance < 0) {
            throw new InvalidArgumentException('Tolerance must be non-negative.');
        }

        // If tolerance is effectively zero, return the original path data
        if ($tolerance < 1e-9) {
            return $pathData;
        }

        $originalSegments = $pathData->getSegments();
        if (count($originalSegments) < 3) {
            return $pathData; // Not enough segments to simplify
        }

        /** @var \Atelier\Svg\Path\Segment\SegmentInterface[] $newSegments */
        $newSegments = [];
        /** @var Point[] List of points currently being considered for simplification */
        $currentPolylinePoints = [];
        /** @var MoveTo|null The MoveTo segment that started the current polyline */
        $startSegmentOfPolyline = null;

        foreach ($originalSegments as $i => $segment) {
            $targetPoint = $segment->getTargetPoint(); // May be null
            $isStart = ($segment instanceof MoveTo && null !== $targetPoint);
            $isLine = ($segment instanceof LineTo && null !== $targetPoint);

            if ($isStart || ($isLine && null !== $startSegmentOfPolyline)) {
                \assert($targetPoint instanceof Point);
                // Part of a polyline sequence
                if ($isStart) {
                    // Process previous polyline before starting a new one
                    $this->processPolyline(
                        $newSegments,
                        $startSegmentOfPolyline,
                        $currentPolylinePoints,
                        $tolerance
                    );
                    // Start new polyline tracking
                    /** @var MoveTo $segment */
                    $startSegmentOfPolyline = $segment;
                    $currentPolylinePoints = [$targetPoint];
                } else { // $isLine && $startSegmentOfPolyline !== null
                    $currentPolylinePoints[] = $targetPoint;
                }
            } else {
                // Segment is not MoveTo or LineTo, or is LineTo without a start point.
                // Process any preceding polyline.
                $this->processPolyline(
                    $newSegments,
                    $startSegmentOfPolyline,
                    $currentPolylinePoints,
                    $tolerance
                );
                // Add the current segment (Curve, Arc, ClosePath, etc.) as is.
                $newSegments[] = $segment;
                // Reset polyline tracking
                $startSegmentOfPolyline = null;
                $currentPolylinePoints = [];
            }
        }

        // Process any polyline remaining at the very end
        $this->processPolyline(
            $newSegments,
            $startSegmentOfPolyline,
            $currentPolylinePoints,
            $tolerance
        );

        return new Data($newSegments);
    }

    /**
     * Simplifies the collected polyline points and adds the result to newSegments.
     *
     * @param array<\Atelier\Svg\Path\Segment\SegmentInterface> $newSegments
     * @param array<Point>                                      $polylinePoints
     */
    private function processPolyline(
        array &$newSegments,
        ?MoveTo $startSegment,
        array $polylinePoints,
        float $tolerance,
    ): void {
        if (null === $startSegment || count($polylinePoints) < 2) {
            // If there was only a MoveTo or no points, just add the start segment back
            if (null !== $startSegment) {
                $newSegments[] = $startSegment;
            }

            return;
        }

        $simplifiedPoints = $this->simplifyPolylineCollinear($polylinePoints, $tolerance);

        // Add the MoveTo with the first point
        // Use original command case ('M' or 'm')
        $newSegments[] = new MoveTo($startSegment->getCommand(), $simplifiedPoints[0]);

        // Add LineTo segments for the rest
        // For simplicity, using absolute 'L'.
        $lineToCommand = 'L';
        for ($i = 1; $i < count($simplifiedPoints); ++$i) {
            $newSegments[] = new LineTo($lineToCommand, $simplifiedPoints[$i]);
        }
    }

    /**
     * Simplifies a polyline by removing collinear points.
     *
     * @param array<Point> $points
     *
     * @return array<Point>
     */
    private function simplifyPolylineCollinear(array $points, float $tolerance): array
    {
        if (count($points) < 3) {
            return $points;
        }

        $simplifiedPoints = [$points[0]]; // Start with the first point
        $toleranceSq = $tolerance * $tolerance; // Use squared distance if using perpendicular distance

        for ($i = 1; $i < count($points) - 1; ++$i) {
            $p1 = $points[$i - 1];
            $p2 = $points[$i];
            $p3 = $points[$i + 1];

            // Calculate squared perpendicular distance of p2 from line p1-p3
            // Or calculate triangle area
            $sqDistance = $this->perpendicularSqDistance($p2, $p1, $p3);

            // If the point is NOT collinear (distance > tolerance), keep it
            if ($sqDistance > $toleranceSq) {
                $simplifiedPoints[] = $p2;
            }
            // If it IS collinear (distance <= tolerance), we simply don't add it
        }

        // Always add the last point
        $simplifiedPoints[] = $points[count($points) - 1];

        return $simplifiedPoints;
    }

    /**
     * Calculates the squared perpendicular distance from a point to the line segment.
     * Using squared distance is faster as it avoids sqrt.
     */
    private function perpendicularSqDistance(Point $p, Point $lineStart, Point $lineEnd): float
    {
        $dx = $lineEnd->x - $lineStart->x;
        $dy = $lineEnd->y - $lineStart->y;
        $lineLengthSq = $dx * $dx + $dy * $dy;

        if (0 == $lineLengthSq) { // Start and end points are the same
            $dist_x = $p->x - $lineStart->x;
            $dist_y = $p->y - $lineStart->y;

            return $dist_x * $dist_x + $dist_y * $dist_y;
        }

        // Parameter t representing projection of p onto the line containing the segment
        $t = (($p->x - $lineStart->x) * $dx + ($p->y - $lineStart->y) * $dy) / $lineLengthSq;
        $t = max(0, min(1, $t)); // Clamp t to [0, 1] to stay within the segment

        // Coordinates of the closest point on the segment
        $closestX = $lineStart->x + $t * $dx;
        $closestY = $lineStart->y + $t * $dy;

        // Squared distance from p to closest point
        $dist_x = $p->x - $closestX;
        $dist_y = $p->y - $closestY;

        return $dist_x * $dist_x + $dist_y * $dist_y;
    }
}
