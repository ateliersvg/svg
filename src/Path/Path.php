<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Geometry\BoundingBox;
use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Geometry\Transformation;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\SegmentInterface;
use Atelier\Svg\Path\Simplifier\Simplifier;

/**
 * Facade class for working with SVG paths.
 *
 * Provides a fluent, developer-friendly API for creating, manipulating,
 * and analyzing SVG path data. Combines PathBuilder, Data, and PathAnalyzer
 * functionality into a single, easy-to-use interface.
 *
 * @example
 * ```php
 * // Parse existing path
 * $path = Path::parse('M 10,10 L 50,50 Z');
 *
 * // Create from builder
 * $path = Path::create()
 *     ->moveTo(10, 10)
 *     ->lineTo(50, 50)
 *     ->closePath()
 *     ->toPath();
 *
 * // Create shapes
 * $circle = Path::circle(50, 50, 25);
 * $star = Path::star(100, 100, 40, 20, 5);
 *
 * // Transform
 * $transformed = $path->translate(10, 20)->scale(2)->rotate(45);
 *
 * // Analyze
 * $length = $path->getLength();
 * $bbox = $path->getBoundingBox();
 * $area = $path->getArea();
 * ```
 */
final class Path implements \Stringable
{
    public function __construct(private Data $data)
    {
    }

    // =========================================================================
    // STATIC FACTORY METHODS
    // =========================================================================

    /**
     * Parse path data from a string.
     *
     * @param string $pathData The SVG path data string (e.g., "M 10,10 L 50,50 Z")
     */
    public static function parse(string $pathData): self
    {
        $parser = new PathParser();
        $data = $parser->parse($pathData);

        return new self($data);
    }

    /**
     * Create a new empty path with a builder interface.
     */
    public static function create(): PathBuilder
    {
        return PathBuilder::new();
    }

    /**
     * Create path from an existing PathBuilder.
     */
    public static function fromBuilder(PathBuilder $builder): self
    {
        return new self($builder->toData());
    }

    /**
     * Create path from raw segments.
     *
     * @param array<SegmentInterface> $segments
     */
    public static function fromSegments(array $segments): self
    {
        return new self(new Data($segments));
    }

    // =========================================================================
    // SHAPE FACTORY METHODS
    // =========================================================================

    /**
     * Create a rectangular path.
     */
    public static function rectangle(
        float $x,
        float $y,
        float $width,
        float $height,
        float $rx = 0,
        float $ry = 0,
    ): self {
        return self::fromBuilder(ShapeFactory::rectangle($x, $y, $width, $height, $rx, $ry));
    }

    /**
     * Create a circular path.
     */
    public static function circle(float $cx, float $cy, float $r): self
    {
        return self::fromBuilder(ShapeFactory::circle($cx, $cy, $r));
    }

    /**
     * Create an elliptical path.
     */
    public static function ellipse(float $cx, float $cy, float $rx, float $ry): self
    {
        return self::fromBuilder(ShapeFactory::ellipse($cx, $cy, $rx, $ry));
    }

    /**
     * Create a regular polygon path.
     */
    public static function polygon(
        float $cx,
        float $cy,
        float $radius,
        int $sides,
        float $rotation = 0,
    ): self {
        return self::fromBuilder(ShapeFactory::polygon($cx, $cy, $radius, $sides, $rotation));
    }

    /**
     * Create a star path.
     */
    public static function star(
        float $cx,
        float $cy,
        float $outerRadius,
        float $innerRadius,
        int $points,
        float $rotation = 0,
    ): self {
        return self::fromBuilder(ShapeFactory::star($cx, $cy, $outerRadius, $innerRadius, $points, $rotation));
    }

    /**
     * Create a line path.
     */
    public static function line(float $x1, float $y1, float $x2, float $y2): self
    {
        return self::fromBuilder(ShapeFactory::line($x1, $y1, $x2, $y2));
    }

    /**
     * Create a polyline path.
     *
     * @param array<array{0: float, 1: float}> $points
     */
    public static function polyline(array $points): self
    {
        return self::fromBuilder(ShapeFactory::polyline($points));
    }

    /**
     * Create a closed polygon from points.
     *
     * @param array<array{0: float, 1: float}> $points
     */
    public static function polygonFromPoints(array $points): self
    {
        return self::fromBuilder(ShapeFactory::polygonFromPoints($points));
    }

    // =========================================================================
    // TRANSFORMATION METHODS
    // =========================================================================

    /**
     * Translate (move) the path by dx and dy.
     *
     * @return self A new Path instance with the transformation applied
     */
    public function translate(float $dx, float $dy): self
    {
        $matrix = Transformation::translate($dx, $dy);

        return $this->transform($matrix);
    }

    /**
     * Scale the path by sx and sy (or uniformly if sy is not provided).
     *
     * @param float      $sx Scale factor for x-axis
     * @param float|null $sy Scale factor for y-axis (defaults to sx for uniform scaling)
     * @param float      $cx Center x for scaling (default: 0)
     * @param float      $cy Center y for scaling (default: 0)
     *
     * @return self A new Path instance with the transformation applied
     */
    public function scale(float $sx, ?float $sy = null, float $cx = 0, float $cy = 0): self
    {
        $sy ??= $sx;

        // Build transformation: translate to origin, scale, translate back
        if (0.0 !== $cx || 0.0 !== $cy) {
            $matrix = Transformation::translate($cx, $cy)
                ->multiply(Transformation::scale($sx, $sy))
                ->multiply(Transformation::translate(-$cx, -$cy));
        } else {
            $matrix = Transformation::scale($sx, $sy);
        }

        return $this->transform($matrix);
    }

    /**
     * Rotate the path by the given angle in degrees.
     *
     * @param float $angle Rotation angle in degrees
     * @param float $cx    Center x for rotation (default: 0)
     * @param float $cy    Center y for rotation (default: 0)
     *
     * @return self A new Path instance with the transformation applied
     */
    public function rotate(float $angle, float $cx = 0, float $cy = 0): self
    {
        $matrix = Transformation::rotate($angle, $cx, $cy);

        return $this->transform($matrix);
    }

    /**
     * Apply a transformation matrix to the path.
     *
     * @param Matrix $matrix The transformation matrix to apply
     *
     * @return self A new Path instance with the transformation applied
     */
    public function transform(Matrix $matrix): self
    {
        $transformer = new PathTransformer();
        $transformedData = $transformer->transform($this->data, $matrix);

        return new self($transformedData);
    }

    // =========================================================================
    // PATH ANALYSIS METHODS
    // =========================================================================

    /**
     * Get the total length of the path.
     */
    public function getLength(): float
    {
        $analyzer = new PathAnalyzer($this->data);

        return $analyzer->getLength();
    }

    /**
     * Get the point at a specific length along the path.
     */
    public function getPointAtLength(float $length): ?Point
    {
        $analyzer = new PathAnalyzer($this->data);

        return $analyzer->getPointAtLength($length);
    }

    /**
     * Get the bounding box of the path.
     */
    public function getBoundingBox(): BoundingBox
    {
        $analyzer = new PathAnalyzer($this->data);

        return $analyzer->getBoundingBox();
    }

    /**
     * Get the center point of the path's bounding box.
     */
    public function getCenter(): Point
    {
        $analyzer = new PathAnalyzer($this->data);

        return $analyzer->getCenter();
    }

    /**
     * Check if a point is inside the path.
     */
    public function containsPoint(Point $point): bool
    {
        $analyzer = new PathAnalyzer($this->data);

        return $analyzer->containsPoint($point);
    }

    /**
     * Get all vertices (points) from the path.
     *
     * @return array<Point>
     */
    public function getVertices(): array
    {
        $analyzer = new PathAnalyzer($this->data);

        return $analyzer->getVertices();
    }

    /**
     * Calculate the area enclosed by the path.
     *
     * Uses the shoelace formula (also known as surveyor's formula).
     * Works for simple polygons. Returns absolute value.
     */
    public function getArea(): float
    {
        $vertices = $this->getVertices();
        $n = count($vertices);

        if ($n < 3) {
            return 0.0;
        }

        $area = 0.0;
        for ($i = 0; $i < $n; ++$i) {
            $j = ($i + 1) % $n;
            $area += $vertices[$i]->x * $vertices[$j]->y;
            $area -= $vertices[$j]->x * $vertices[$i]->y;
        }

        return abs($area / 2.0);
    }

    /**
     * Check if the path is closed (ends with a ClosePath command).
     */
    public function isClosed(): bool
    {
        $segments = $this->data->getSegments();
        if (empty($segments)) {
            return false;
        }

        $lastSegment = end($segments);

        return $lastSegment instanceof ClosePath;
    }

    /**
     * Check if the path is drawn in clockwise direction.
     *
     * Uses the signed area calculation. A negative area indicates
     * clockwise winding, while positive indicates counter-clockwise.
     */
    public function isClockwise(): bool
    {
        $vertices = $this->getVertices();
        $n = count($vertices);

        if ($n < 3) {
            return false;
        }

        $signedArea = 0.0;
        for ($i = 0; $i < $n; ++$i) {
            $j = ($i + 1) % $n;
            $signedArea += $vertices[$i]->x * $vertices[$j]->y;
            $signedArea -= $vertices[$j]->x * $vertices[$i]->y;
        }

        return $signedArea < 0;
    }

    // =========================================================================
    // PATH MANIPULATION METHODS
    // =========================================================================

    /**
     * Reverse the direction of the path.
     *
     * @return self A new Path instance with reversed direction
     */
    public function reverse(): self
    {
        $segments = $this->data->getSegments();
        if (empty($segments)) {
            return clone $this;
        }

        $reversed = [];
        $points = [];

        // Collect all points
        foreach ($segments as $segment) {
            if ($segment instanceof MoveTo || $segment instanceof LineTo) {
                $points[] = $segment->getTargetPoint();
            }
        }

        if (empty($points)) {
            return clone $this;
        }

        // Build reversed path
        $reversed[] = new MoveTo('M', end($points));

        for ($i = count($points) - 2; $i >= 0; --$i) {
            $reversed[] = new LineTo('L', $points[$i]);
        }

        // If original was closed, close the reversed path
        if ($this->isClosed()) {
            $reversed[] = new ClosePath('Z');
        }

        return new self(new Data($reversed));
    }

    /**
     * Get a subpath from start to end segment index.
     *
     * @param int $start Starting segment index (inclusive)
     * @param int $end   Ending segment index (inclusive)
     *
     * @return self A new Path instance containing only the specified segments
     */
    public function getSubpath(int $start, int $end): self
    {
        return new self($this->data->subpath($start, $end));
    }

    /**
     * Split the path at a specific length.
     *
     * @param float $length The length at which to split the path
     *
     * @return array{0: self, 1: self} An array containing two Path instances [before, after]
     */
    public function split(float $length): array
    {
        // Find the segment index where the split occurs
        $accumulatedLength = 0;
        $currentPoint = new Point(0, 0);
        $splitIndex = 0;

        foreach ($this->data->getSegments() as $index => $segment) {
            if ($segment instanceof MoveTo) {
                $currentPoint = $segment->getTargetPoint();
            } elseif ($segment instanceof LineTo) {
                $point = $segment->getTargetPoint();
                $dx = $point->x - $currentPoint->x;
                $dy = $point->y - $currentPoint->y;
                $segmentLength = sqrt($dx * $dx + $dy * $dy);

                if ($accumulatedLength + $segmentLength >= $length) {
                    $splitIndex = $index;
                    break;
                }

                $accumulatedLength += $segmentLength;
                $currentPoint = $point;
            }
        }

        // Split at the found index
        $beforeSegments = array_slice($this->data->getSegments(), 0, $splitIndex + 1);
        $afterSegments = array_slice($this->data->getSegments(), $splitIndex);

        return [
            new self(new Data($beforeSegments)),
            new self(new Data($afterSegments)),
        ];
    }

    /**
     * Simplify the path by removing unnecessary points.
     *
     * @param float $tolerance The tolerance for simplification (higher = more aggressive)
     *
     * @return self A new simplified Path instance
     */
    public function simplify(float $tolerance = 1.0): self
    {
        $simplifier = new Simplifier();
        $simplifiedData = $simplifier->simplify($this->data, $tolerance);

        return new self($simplifiedData);
    }

    // =========================================================================
    // DISTANCE COMPUTATION METHODS
    // =========================================================================

    /**
     * Compute the Hausdorff distance to another path.
     *
     * The Hausdorff distance is the maximum distance from any point on one path
     * to the nearest point on the other path. It measures how far two shapes are from each other.
     *
     * @param self $other   The other path to compare with
     * @param int  $samples Number of sample points (default: 50, higher = more accurate)
     *
     * @return float The Hausdorff distance
     */
    public function hausdorffDistance(self $other, int $samples = 50): float
    {
        return PathDistance::hausdorff($this->data, $other->data, $samples);
    }

    /**
     * Compute the discrete Fréchet distance to another path.
     *
     * The Fréchet distance represents the minimum leash length required for
     * traversing both paths simultaneously. It's useful for measuring similarity
     * of paths considering their direction and order.
     *
     * @param self $other   The other path to compare with
     * @param int  $samples Number of sample points (default: 50, higher = more accurate)
     *
     * @return float The discrete Fréchet distance
     */
    public function frechetDistance(self $other, int $samples = 50): float
    {
        return PathDistance::discreteFrechet($this->data, $other->data, $samples);
    }

    /**
     * Compute the average distance between corresponding points on two paths.
     *
     * This is a simpler metric that samples both paths at the same intervals
     * and computes the average distance between corresponding sample points.
     *
     * @param self $other   The other path to compare with
     * @param int  $samples Number of sample points (default: 50)
     *
     * @return float The average distance
     */
    public function averageDistance(self $other, int $samples = 50): float
    {
        return PathDistance::averageDistance($this->data, $other->data, $samples);
    }

    /**
     * Compute the maximum distance between corresponding points on two paths.
     *
     * Similar to averageDistance but returns the maximum instead of average.
     *
     * @param self $other   The other path to compare with
     * @param int  $samples Number of sample points (default: 50)
     *
     * @return float The maximum distance
     */
    public function maxPointDistance(self $other, int $samples = 50): float
    {
        return PathDistance::maxPointDistance($this->data, $other->data, $samples);
    }

    // =========================================================================
    // EXPORT METHODS
    // =========================================================================

    /**
     * Get the underlying Data object.
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * Get the segments array.
     *
     * @return array<SegmentInterface>
     */
    public function getSegments(): array
    {
        return $this->data->getSegments();
    }

    /**
     * Convert to a PathBuilder for further manipulation.
     */
    public function toBuilder(): PathBuilder
    {
        $builder = PathBuilder::new();
        foreach ($this->data->getSegments() as $segment) {
            $builder->addSegment($segment);
        }

        return $builder;
    }

    /**
     * Convert to a string representation.
     */
    public function toString(): string
    {
        return $this->data->toString();
    }

    /**
     * Convert to a string representation.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Clone the path.
     */
    public function __clone()
    {
        $this->data = clone $this->data;
    }
}
