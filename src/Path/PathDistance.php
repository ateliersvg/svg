<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Geometry\Point;

/**
 * Computes distance metrics between two SVG paths.
 *
 * Provides various distance measures including:
 * - Hausdorff distance (maximum distance from any point to nearest point)
 * - Fréchet distance (minimum leash length for continuous traversal)
 * - Discrete Fréchet distance (practical approximation)
 */
final class PathDistance
{
    /**
     * Computes the Hausdorff distance between two paths.
     *
     * The Hausdorff distance is the maximum distance from any point on one path
     * to the nearest point on the other path.
     *
     * @param Data $path1   First path
     * @param Data $path2   Second path
     * @param int  $samples Number of sample points per path (default: 50)
     *
     * @return float The Hausdorff distance
     */
    public static function hausdorff(Data $path1, Data $path2, int $samples = 50): float
    {
        $points1 = self::samplePath($path1, $samples);
        $points2 = self::samplePath($path2, $samples);

        // Compute directed Hausdorff from path1 to path2
        $h1to2 = self::directedHausdorff($points1, $points2);

        // Compute directed Hausdorff from path2 to path1
        $h2to1 = self::directedHausdorff($points2, $points1);

        // Hausdorff distance is the maximum of the two directed distances
        return max($h1to2, $h2to1);
    }

    /**
     * Computes the discrete Fréchet distance between two paths.
     *
     * The Fréchet distance represents the minimum leash length required for
     * a person and a dog to walk along two paths from start to end.
     * This is a discrete approximation using sampled points.
     *
     * @param Data $path1   First path
     * @param Data $path2   Second path
     * @param int  $samples Number of sample points per path (default: 50)
     *
     * @return float The discrete Fréchet distance
     */
    public static function discreteFrechet(Data $path1, Data $path2, int $samples = 50): float
    {
        $points1 = self::samplePath($path1, $samples);
        $points2 = self::samplePath($path2, $samples);

        $n = count($points1);
        $m = count($points2);

        // Dynamic programming matrix
        $ca = [];
        for ($i = 0; $i < $n; ++$i) {
            $ca[$i] = array_fill(0, $m, -1.0);
        }

        if (0 === $n || 0 === $m) {
            return 0.0;
        }

        return self::computeFrechet($points1, $points2, $n - 1, $m - 1, $ca);
    }

    /**
     * Computes the average distance between corresponding points on two paths.
     *
     * This is a simpler metric that compares paths point-by-point.
     *
     * @param Data $path1   First path
     * @param Data $path2   Second path
     * @param int  $samples Number of sample points per path (default: 50)
     *
     * @return float The average distance
     */
    public static function averageDistance(Data $path1, Data $path2, int $samples = 50): float
    {
        $points1 = self::samplePath($path1, $samples);
        $points2 = self::samplePath($path2, $samples);

        $totalDistance = 0.0;
        $count = min(count($points1), count($points2));

        for ($i = 0; $i < $count; ++$i) {
            $totalDistance += self::pointDistance($points1[$i], $points2[$i]);
        }

        return $count > 0 ? $totalDistance / $count : 0.0;
    }

    /**
     * Computes the maximum distance between corresponding points on two paths.
     *
     * @param Data $path1   First path
     * @param Data $path2   Second path
     * @param int  $samples Number of sample points per path (default: 50)
     *
     * @return float The maximum distance
     */
    public static function maxPointDistance(Data $path1, Data $path2, int $samples = 50): float
    {
        $points1 = self::samplePath($path1, $samples);
        $points2 = self::samplePath($path2, $samples);

        $maxDistance = 0.0;
        $count = min(count($points1), count($points2));

        for ($i = 0; $i < $count; ++$i) {
            $distance = self::pointDistance($points1[$i], $points2[$i]);
            $maxDistance = max($maxDistance, $distance);
        }

        return $maxDistance;
    }

    /**
     * Samples a path at regular intervals along its length.
     *
     * @param Data $path  The path to sample
     * @param int  $count Number of sample points
     *
     * @return array<Point> Array of sampled points
     */
    private static function samplePath(Data $path, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $analyzer = new PathAnalyzer($path);
        $totalLength = $analyzer->getLength();

        if ($totalLength <= 0) {
            return [];
        }

        $points = [];

        // Handle single point case
        if (1 === $count) {
            $point = $analyzer->getPointAtLength(0);

            return null !== $point ? [$point] : [];
        }

        for ($i = 0; $i < $count; ++$i) {
            $length = ($i / ($count - 1)) * $totalLength;
            $point = $analyzer->getPointAtLength($length);
            if (null !== $point) {
                $points[] = $point;
            }
        }

        return $points;
    }

    /**
     * Computes the directed Hausdorff distance from set A to set B.
     *
     * @param array<Point> $pointsA First point set
     * @param array<Point> $pointsB Second point set
     *
     * @return float The directed Hausdorff distance
     */
    private static function directedHausdorff(array $pointsA, array $pointsB): float
    {
        $maxMinDistance = 0.0;

        foreach ($pointsA as $pointA) {
            $minDistance = PHP_FLOAT_MAX;

            foreach ($pointsB as $pointB) {
                $distance = self::pointDistance($pointA, $pointB);
                $minDistance = min($minDistance, $distance);
            }

            $maxMinDistance = max($maxMinDistance, $minDistance);
        }

        return $maxMinDistance;
    }

    /**
     * Recursively computes the discrete Fréchet distance using dynamic programming.
     *
     * @param array<Point>        $points1 Points from first path
     * @param array<Point>        $points2 Points from second path
     * @param int                 $i       Current index in first path
     * @param int                 $j       Current index in second path
     * @param array<array<float>> $ca      Memoization array
     *
     * @return float The discrete Fréchet distance
     */
    private static function computeFrechet(array $points1, array $points2, int $i, int $j, array &$ca): float
    {
        if ($ca[$i][$j] > -1) {
            return $ca[$i][$j];
        }

        $distance = self::pointDistance($points1[$i], $points2[$j]);

        if (0 === $i && 0 === $j) {
            $ca[$i][$j] = $distance;
        } elseif ($i > 0 && 0 === $j) {
            $ca[$i][$j] = max(
                self::computeFrechet($points1, $points2, $i - 1, 0, $ca),
                $distance
            );
        } elseif (0 === $i && $j > 0) {
            $ca[$i][$j] = max(
                self::computeFrechet($points1, $points2, 0, $j - 1, $ca),
                $distance
            );
        } else {
            $ca[$i][$j] = max(
                min(
                    self::computeFrechet($points1, $points2, $i - 1, $j, $ca),
                    self::computeFrechet($points1, $points2, $i - 1, $j - 1, $ca),
                    self::computeFrechet($points1, $points2, $i, $j - 1, $ca)
                ),
                $distance
            );
        }

        return $ca[$i][$j];
    }

    /**
     * Computes the Euclidean distance between two points.
     *
     * @param Point $p1 First point
     * @param Point $p2 Second point
     *
     * @return float The distance
     */
    private static function pointDistance(Point $p1, Point $p2): float
    {
        $dx = $p2->x - $p1->x;
        $dy = $p2->y - $p1->y;

        return sqrt($dx * $dx + $dy * $dy);
    }
}
