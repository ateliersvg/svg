<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Simplifier;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;

final class VisvalingamWhyattSimplifier implements SimplifierInterface
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
        /** @var array<int, Point> List of points currently being considered for simplification */
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
     * @param array<int, Point>                                 $polylinePoints
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

        $simplifiedPoints = $this->simplifyPolylineVW($polylinePoints, $tolerance);

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
     * Simplifies a polyline using the Visvalingam-Whyatt algorithm.
     *
     * @param array<int, Point> $points        array of Point objects representing the polyline
     * @param float             $areaTolerance the area tolerance for simplification
     *
     * @return array<int, Point> simplified array of Point objects
     */
    private function simplifyPolylineVW(array $points, float $areaTolerance): array
    {
        $count = count($points);
        if ($count < 3) {
            return $points;
        }

        // 1. Calculate initial areas for internal points
        $areas = []; // Store index => area
        for ($i = 1; $i < $count - 1; ++$i) {
            $areas[$i] = $this->calculateTriangleArea($points[$i - 1], $points[$i], $points[$i + 1]);
        }

        // Use a simple array and find min repeatedly, or use a Priority Queue for efficiency
        $remainingIndices = range(0, $count - 1); // Indices of points still in the polyline

        while (true) {
            $minArea = PHP_FLOAT_MAX;
            $minIndex = -1;

            // Find internal point with minimum area
            // Need to map remainingIndices to original indices to check $areas
            $internalIndices = [];
            for ($k = 1; $k < count($remainingIndices) - 1; ++$k) {
                $originalIndex = $remainingIndices[$k];
                if (isset($areas[$originalIndex]) && $areas[$originalIndex] < $minArea) {
                    $minArea = $areas[$originalIndex];
                    $minIndex = $k; // Store the index within remainingIndices
                }
                $internalIndices[] = $originalIndex; // Track internal indices for area recalc later
            }

            // If minimum area is >= tolerance, or <= 2 points left, stop
            if (-1 === $minIndex || $minArea >= $areaTolerance || count($remainingIndices) <= 2) {
                break;
            }

            $removedOriginalIndex = $remainingIndices[$minIndex];
            unset($areas[$removedOriginalIndex]); // Remove its area entry

            // Remove the point by its index in remainingIndices
            array_splice($remainingIndices, $minIndex, 1);

            // 2. Recalculate areas for neighbors (tricky part)
            // Need to find the new neighbors of the points that were adjacent to the removed point.
            // Get the original indices of the points now at $minIndex-1 and $minIndex in remainingIndices
            if ($minIndex > 0 && $minIndex < count($remainingIndices)) { // Check bounds
                $leftOriginalIndex = $remainingIndices[$minIndex - 1];
                $rightOriginalIndex = $remainingIndices[$minIndex];

                // Only recalculate if they are internal points
                if (isset($areas[$leftOriginalIndex])) {
                    // Find neighbors of left point
                    $leftLeftIndex = $remainingIndices[$minIndex - 2] ?? null; // Find index in remainingIndices
                    if (null !== $leftLeftIndex) {
                        $areas[$leftOriginalIndex] = $this->calculateTriangleArea($points[$leftLeftIndex], $points[$leftOriginalIndex], $points[$rightOriginalIndex]);
                    }
                }
                if (isset($areas[$rightOriginalIndex])) {
                    // Find neighbors of right point
                    $rightRightIndex = $remainingIndices[$minIndex + 1] ?? null;
                    if (null !== $rightRightIndex) {
                        $areas[$rightOriginalIndex] = $this->calculateTriangleArea($points[$leftOriginalIndex], $points[$rightOriginalIndex], $points[$rightRightIndex]);
                    }
                }
            }
        } // End while loop

        // Collect final points
        $simplifiedPoints = [];
        foreach ($remainingIndices as $index) {
            $simplifiedPoints[] = $points[$index];
        }

        return $simplifiedPoints;
    }

    private function calculateTriangleArea(Point $p1, Point $p2, Point $p3): float
    {
        // Shoelace formula / cross product magnitude
        return 0.5 * abs($p1->x * ($p2->y - $p3->y) + $p2->x * ($p3->y - $p1->y) + $p3->x * ($p1->y - $p2->y));
    }
}
