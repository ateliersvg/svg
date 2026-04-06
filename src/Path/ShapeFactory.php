<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Factory for creating common SVG shapes as path data.
 *
 * Provides convenient methods for generating paths for rectangles,
 * circles, ellipses, polygons, and other common shapes.
 */
final class ShapeFactory
{
    /**
     * Creates a rectangular path.
     *
     * @param float $x      X coordinate of the top-left corner
     * @param float $y      Y coordinate of the top-left corner
     * @param float $width  Width of the rectangle
     * @param float $height Height of the rectangle
     * @param float $rx     Optional horizontal corner radius
     * @param float $ry     Optional vertical corner radius
     */
    public static function rectangle(
        float $x,
        float $y,
        float $width,
        float $height,
        float $rx = 0,
        float $ry = 0,
    ): PathBuilder {
        if ($rx <= 0 && $ry <= 0) {
            // Simple rectangle without rounded corners
            return PathBuilder::new()
                ->moveTo($x, $y)
                ->horizontalLineTo($x + $width)
                ->verticalLineTo($y + $height)
                ->horizontalLineTo($x)
                ->closePath();
        }

        // Rectangle with rounded corners
        // Limit radii to half of width/height
        $rx = min($rx, $width / 2);
        $ry = min($ry, $height / 2);

        return PathBuilder::new()
            ->moveTo($x + $rx, $y)
            ->horizontalLineTo($x + $width - $rx)
            ->arcTo($rx, $ry, 0, false, true, $x + $width, $y + $ry)
            ->verticalLineTo($y + $height - $ry)
            ->arcTo($rx, $ry, 0, false, true, $x + $width - $rx, $y + $height)
            ->horizontalLineTo($x + $rx)
            ->arcTo($rx, $ry, 0, false, true, $x, $y + $height - $ry)
            ->verticalLineTo($y + $ry)
            ->arcTo($rx, $ry, 0, false, true, $x + $rx, $y)
            ->closePath();
    }

    /**
     * Creates a circular path.
     *
     * @param float $cx Center X coordinate
     * @param float $cy Center Y coordinate
     * @param float $r  Radius
     */
    public static function circle(float $cx, float $cy, float $r): PathBuilder
    {
        return self::ellipse($cx, $cy, $r, $r);
    }

    /**
     * Creates an elliptical path.
     *
     * @param float $cx Center X coordinate
     * @param float $cy Center Y coordinate
     * @param float $rx Horizontal radius
     * @param float $ry Vertical radius
     */
    public static function ellipse(float $cx, float $cy, float $rx, float $ry): PathBuilder
    {
        // Ellipse using four arc commands
        return PathBuilder::new()
            ->moveTo($cx - $rx, $cy)
            ->arcTo($rx, $ry, 0, false, true, $cx, $cy - $ry)
            ->arcTo($rx, $ry, 0, false, true, $cx + $rx, $cy)
            ->arcTo($rx, $ry, 0, false, true, $cx, $cy + $ry)
            ->arcTo($rx, $ry, 0, false, true, $cx - $rx, $cy)
            ->closePath();
    }

    /**
     * Creates a regular polygon path.
     *
     * @param float $cx       Center X coordinate
     * @param float $cy       Center Y coordinate
     * @param float $radius   Radius (distance from center to vertices)
     * @param int   $sides    Number of sides (must be >= 3)
     * @param float $rotation Starting rotation in degrees
     */
    public static function polygon(
        float $cx,
        float $cy,
        float $radius,
        int $sides,
        float $rotation = 0,
    ): PathBuilder {
        if ($sides < 3) {
            throw new InvalidArgumentException('Polygon must have at least 3 sides');
        }

        $builder = PathBuilder::new();
        $angleStep = (2 * M_PI) / $sides;
        $startAngle = deg2rad($rotation - 90); // Start from top

        for ($i = 0; $i < $sides; ++$i) {
            $angle = $startAngle + ($i * $angleStep);
            $x = $cx + $radius * cos($angle);
            $y = $cy + $radius * sin($angle);

            if (0 === $i) {
                $builder->moveTo($x, $y);
            } else {
                $builder->lineTo($x, $y);
            }
        }

        return $builder->closePath();
    }

    /**
     * Creates a star path.
     *
     * @param float $cx          Center X coordinate
     * @param float $cy          Center Y coordinate
     * @param float $outerRadius Outer radius (distance to points)
     * @param float $innerRadius Inner radius (distance to indents)
     * @param int   $points      Number of points (must be >= 3)
     * @param float $rotation    Starting rotation in degrees
     */
    public static function star(
        float $cx,
        float $cy,
        float $outerRadius,
        float $innerRadius,
        int $points,
        float $rotation = 0,
    ): PathBuilder {
        if ($points < 3) {
            throw new InvalidArgumentException('Star must have at least 3 points');
        }

        $builder = PathBuilder::new();
        $angleStep = M_PI / $points;
        $startAngle = deg2rad($rotation - 90);

        for ($i = 0; $i < $points * 2; ++$i) {
            $angle = $startAngle + ($i * $angleStep);
            $radius = (0 === $i % 2) ? $outerRadius : $innerRadius;
            $x = $cx + $radius * cos($angle);
            $y = $cy + $radius * sin($angle);

            if (0 === $i) {
                $builder->moveTo($x, $y);
            } else {
                $builder->lineTo($x, $y);
            }
        }

        return $builder->closePath();
    }

    /**
     * Creates a line path.
     *
     * @param float $x1 Start X coordinate
     * @param float $y1 Start Y coordinate
     * @param float $x2 End X coordinate
     * @param float $y2 End Y coordinate
     */
    public static function line(float $x1, float $y1, float $x2, float $y2): PathBuilder
    {
        return PathBuilder::new()
            ->moveTo($x1, $y1)
            ->lineTo($x2, $y2);
    }

    /**
     * Creates a polyline path (multiple connected line segments).
     *
     * @param array<array{0: float, 1: float}> $points Array of [x, y] coordinate pairs
     */
    public static function polyline(array $points): PathBuilder
    {
        if (empty($points)) {
            throw new InvalidArgumentException('Polyline must have at least one point');
        }

        $builder = PathBuilder::new();

        foreach ($points as $i => $point) {
            [$x, $y] = $point;

            if (0 === $i) {
                $builder->moveTo($x, $y);
            } else {
                $builder->lineTo($x, $y);
            }
        }

        return $builder;
    }

    /**
     * Creates a closed polygon from points.
     *
     * @param array<array{0: float, 1: float}> $points Array of [x, y] coordinate pairs
     */
    public static function polygonFromPoints(array $points): PathBuilder
    {
        if (count($points) < 3) {
            throw new InvalidArgumentException('Polygon must have at least 3 points');
        }

        return self::polyline($points)->closePath();
    }
}
