<?php

declare(strict_types=1);

namespace Atelier\Svg\Geometry;

final class Transformation
{
    public static function identity(): Matrix
    {
        return new Matrix();
    }

    public static function translate(float $x, float $y): Matrix
    {
        return new Matrix(e: $x, f: $y);
    }

    public static function scale(float $x, ?float $y = null): Matrix
    {
        return new Matrix(a: $x, d: $y ?? $x);
    }

    public static function rotate(float $angleInDegrees, float $cx = 0, float $cy = 0): Matrix
    {
        $rad = deg2rad($angleInDegrees);
        $cos = cos($rad);
        $sin = sin($rad);

        // Translate → Rotate → Translate back
        return self::translate($cx, $cy)
            ->multiply(new Matrix($cos, $sin, -$sin, $cos))
            ->multiply(self::translate(-$cx, -$cy));
    }

    public static function skewX(float $angleInDegrees): Matrix
    {
        $rad = deg2rad($angleInDegrees);
        $tan = tan($rad);

        return new Matrix(a: 1, b: 0, c: $tan, d: 1, e: 0, f: 0);
    }

    public static function skewY(float $angleInDegrees): Matrix
    {
        $rad = deg2rad($angleInDegrees);
        $tan = tan($rad);

        return new Matrix(a: 1, b: $tan, c: 0, d: 1, e: 0, f: 0);
    }

    public static function fromMatrix(Matrix $matrix): Matrix
    {
        return $matrix;
    }
}
