<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Simplifier;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;

final class Simplifier implements SimplifierInterface
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

        foreach ($originalSegments as $segment) {
            $targetPoint = $segment->getTargetPoint(); // May be null

            if ($segment instanceof MoveTo && null !== $targetPoint) {
                // Process previous polyline before starting a new one
                $this->processPolyline(
                    $newSegments,
                    $startSegmentOfPolyline,
                    $currentPolylinePoints,
                    $tolerance
                );
                $startSegmentOfPolyline = $segment;
                $currentPolylinePoints = [$targetPoint];
            } elseif ($segment instanceof LineTo && null !== $targetPoint && null !== $startSegmentOfPolyline) {
                $currentPolylinePoints[] = $targetPoint;
            } else {
                // Anything else: process preceding polyline, append segment as-is, reset.
                $this->processPolyline(
                    $newSegments,
                    $startSegmentOfPolyline,
                    $currentPolylinePoints,
                    $tolerance
                );
                $newSegments[] = $segment;
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

        $simplifiedPoints = $this->simplifyPolylineRDP($polylinePoints, $tolerance);

        // Add the MoveTo with the first point (RDP keeps first/last)
        // Use original command case ('M' or 'm')
        $newSegments[] = new MoveTo($startSegment->getCommand(), $simplifiedPoints[0]);

        // Add LineTo segments for the rest
        // For simplicity, using absolute 'L'. A real implementation needs careful
        // handling of relative vs absolute based on original segments/options.
        $lineToCommand = 'L';
        for ($i = 1; $i < count($simplifiedPoints); ++$i) {
            $newSegments[] = new LineTo($lineToCommand, $simplifiedPoints[$i]);
        }
    }

    /**
     * Simplifies a list of points using the RDP algorithm recursively.
     *
     * @param Point[] $points    list of points forming the polyline (at least 2 points)
     * @param float   $tolerance maximum distance error allowed
     *
     * @return Point[] simplified list of points (always includes first and last)
     */
    private function simplifyPolylineRDP(array $points, float $tolerance): array
    {
        $count = count($points);
        if ($count < 3) {
            return $points; // Base case: Cannot simplify further
        }

        $firstPoint = $points[0];
        $lastPoint = $points[$count - 1];
        $maxSqDistance = 0.0; // Use squared distance to avoid sqrt
        $index = 0;

        // Find the point with the maximum distance
        for ($i = 1; $i < $count - 1; ++$i) {
            $sqDistance = $this->perpendicularSqDistance($points[$i], $firstPoint, $lastPoint);
            if ($sqDistance > $maxSqDistance) {
                $index = $i;
                $maxSqDistance = $sqDistance;
            }
        }

        // If max distance is greater than tolerance, recursively simplify
        $toleranceSq = $tolerance * $tolerance;
        if ($maxSqDistance > $toleranceSq) {
            // Recursive calls
            $firstPart = $this->simplifyPolylineRDP(array_slice($points, 0, $index + 1), $tolerance);
            $secondPart = $this->simplifyPolylineRDP(array_slice($points, $index), $tolerance);

            // Combine results, removing the duplicate middle point (last of first part)
            array_pop($firstPart);

            return array_merge($firstPart, $secondPart);
        }

        // All points within tolerance, return only first and last points
        return [$firstPoint, $lastPoint];
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
