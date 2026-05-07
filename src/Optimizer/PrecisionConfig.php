<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Centralized configuration for numeric precision across optimization passes.
 *
 * This class defines standard precision values for different optimization contexts.
 * Using these constants ensures consistency across passes and makes it easier to
 * understand and adjust precision trade-offs.
 *
 * ## Precision Philosophy
 *
 * Different numeric contexts tolerate different precision levels:
 * - **Coordinates** (x, y): 2 decimals = 0.01px precision, imperceptible at normal scales
 * - **Transforms**: 3 decimals needed because transforms are multiplicative; errors compound
 * - **Paths**: 3 decimals preserve curve smoothness
 * - **Opacity**: 2 decimals sufficient for the [0,1] range
 *
 * See docs/optimizer/optimization-strategy.md for detailed rationale.
 */
final class PrecisionConfig
{
    /**
     * Precision for coordinate values (x, y, cx, cy, etc.).
     *
     * 2 decimals = 0.01px precision. At typical viewport sizes (100-1000px),
     * this is imperceptible. Sub-pixel rendering makes fractional pixels useful,
     * but beyond 0.01px offers no visual benefit.
     */
    public const int COORDINATE_DEFAULT = 2;
    public const int COORDINATE_AGGRESSIVE = 1;
    public const int COORDINATE_SAFE = 3;

    /**
     * Precision for dimension values (width, height, r, rx, ry).
     *
     * Same rationale as coordinates - these define sizes in the same pixel space.
     */
    public const int DIMENSION_DEFAULT = 2;
    public const int DIMENSION_AGGRESSIVE = 1;
    public const int DIMENSION_SAFE = 3;

    /**
     * Precision for transform matrix values.
     *
     * Transforms are multiplicative and applied to descendant elements.
     * Small rounding errors compound through the transform chain.
     * 3 decimals minimum recommended; 4-5 for complex nested transforms.
     *
     * Example: A 45° rotation is represented as:
     *   cos(45°) ≈ 0.707107
     * Rounded to 2 decimals: 0.71 (error: ~0.003, or 0.4%)
     * Rounded to 3 decimals: 0.707 (error: ~0.0001, or 0.01%)
     */
    public const int TRANSFORM_DEFAULT = 3;
    public const int TRANSFORM_AGGRESSIVE = 2;
    public const int TRANSFORM_SAFE = 4;

    /**
     * Precision for path data coordinates.
     *
     * Path data includes both straight lines and curves (bezier, arc).
     * Curves are particularly sensitive to precision:
     * - Control points affect curve shape significantly
     * - 3 decimals preserves smooth curves at most scales
     * - 2 decimals acceptable for simple graphics
     */
    public const int PATH_DEFAULT = 3;
    public const int PATH_AGGRESSIVE = 2;
    public const int PATH_SAFE = 4;

    /**
     * Precision for opacity and similar ratio values [0,1].
     *
     * Opacity, stop-opacity, fill-opacity, stroke-opacity.
     * 2 decimals = 0.01 increments in [0,1] range (101 possible values).
     * Human perception of opacity is logarithmic; 0.01 steps are sufficient.
     */
    public const int OPACITY_DEFAULT = 2;
    public const int OPACITY_AGGRESSIVE = 2;  // Don't reduce further
    public const int OPACITY_SAFE = 3;

    /**
     * Precision for cleanup/formatting pass.
     *
     * CleanupNumericValuesPass often runs with slightly higher precision
     * than RoundValuesPass as a safety margin. This pass formats numbers
     * (removes trailing zeros, leading zeros) rather than primarily rounding.
     */
    public const int CLEANUP_DEFAULT = 3;
    public const int CLEANUP_AGGRESSIVE = 2;
    public const int CLEANUP_SAFE = 4;

    /**
     * Precision for angles (rotation, skew).
     *
     * Angles in degrees. 1-2 decimals sufficient:
     * - 0.1° is rarely perceptible in rotation
     * - 0.01° is imperceptible
     */
    public const int ANGLE_DEFAULT = 1;
    public const int ANGLE_AGGRESSIVE = 1;
    public const int ANGLE_SAFE = 2;

    /**
     * Precision bounds.
     *
     * MIN: Never use negative precision
     * MAX: Beyond 6 decimals offers no practical benefit
     *      (floating point has ~15-17 significant digits total)
     */
    public const int MIN_PRECISION = 0;
    public const int MAX_PRECISION = 6;

    /**
     * Validates precision value is within acceptable bounds.
     *
     * @param int $precision The precision value to validate
     *
     * @return int The bounded precision value
     */
    public static function validate(int $precision): int
    {
        return max(self::MIN_PRECISION, min(self::MAX_PRECISION, $precision));
    }

    /**
     * Gets a preset-specific precision configuration.
     *
     * Returns an array of precision values for different contexts
     * based on the preset name.
     *
     * @param string $preset One of: default, aggressive, safe, web
     *
     * @return array{coordinate: int, dimension: int, transform: int, path: int, opacity: int, cleanup: int, angle: int}
     *
     * @throws InvalidArgumentException if preset name is unknown
     */
    public static function forPreset(string $preset): array
    {
        return match ($preset) {
            'default' => [
                'coordinate' => self::COORDINATE_DEFAULT,
                'dimension' => self::DIMENSION_DEFAULT,
                'transform' => self::TRANSFORM_DEFAULT,
                'path' => self::PATH_DEFAULT,
                'opacity' => self::OPACITY_DEFAULT,
                'cleanup' => self::CLEANUP_DEFAULT,
                'angle' => self::ANGLE_DEFAULT,
            ],
            'aggressive' => [
                'coordinate' => self::COORDINATE_AGGRESSIVE,
                'dimension' => self::DIMENSION_AGGRESSIVE,
                'transform' => self::TRANSFORM_AGGRESSIVE,
                'path' => self::PATH_AGGRESSIVE,
                'opacity' => self::OPACITY_AGGRESSIVE,
                'cleanup' => self::CLEANUP_AGGRESSIVE,
                'angle' => self::ANGLE_AGGRESSIVE,
            ],
            'safe' => [
                'coordinate' => self::COORDINATE_SAFE,
                'dimension' => self::DIMENSION_SAFE,
                'transform' => self::TRANSFORM_SAFE,
                'path' => self::PATH_SAFE,
                'opacity' => self::OPACITY_SAFE,
                'cleanup' => self::CLEANUP_SAFE,
                'angle' => self::ANGLE_SAFE,
            ],
            'web' => [
                'coordinate' => self::COORDINATE_AGGRESSIVE,
                'dimension' => self::DIMENSION_AGGRESSIVE,
                'transform' => self::TRANSFORM_AGGRESSIVE,
                'path' => self::PATH_AGGRESSIVE,
                'opacity' => self::OPACITY_AGGRESSIVE,
                'cleanup' => self::CLEANUP_AGGRESSIVE,
                'angle' => self::ANGLE_AGGRESSIVE,
            ],
            default => throw new InvalidArgumentException(sprintf("Unknown preset '%s'. Available: default, aggressive, safe, web", $preset)),
        };
    }
}
