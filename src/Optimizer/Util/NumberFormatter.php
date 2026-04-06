<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Util;

/**
 * Shared number formatting for optimizer passes.
 *
 * Consolidates the rounding and formatting logic previously duplicated across
 * RoundValuesPass, CleanupNumericValuesPass, ConvertPathDataPass,
 * SimplifyTransformsPass, and ConvertShapeToPathPass.
 */
final class NumberFormatter
{
    /**
     * Regex pattern matching numeric values in SVG attribute strings.
     *
     * Matches integers (42), decimals (10.5), and negative values (-3.14).
     * Does not match bare decimals (.5) or scientific notation (1e10).
     */
    private const string NUMBER_PATTERN = '/-?\d+\.?\d*/';

    /**
     * Formats a float value as a compact SVG-ready string.
     *
     * Rounds to the given precision, then strips trailing zeros
     * and unnecessary decimal points.
     *
     * Examples (precision=2):
     *   10.12345 → "10.12"
     *   100.999  → "101"
     *   10.500   → "10.5"
     *   30.000   → "30"
     *
     * With removeLeadingZero=true:
     *   0.5  → ".5"
     *   -0.5 → "-.5"
     *
     * @param float $value             The numeric value to format
     * @param int   $precision         Number of decimal places to round to
     * @param bool  $removeLeadingZero Whether to remove the leading zero before the decimal point
     */
    public static function format(float $value, int $precision, bool $removeLeadingZero = false): string
    {
        $rounded = round($value, $precision);
        $formatted = number_format($rounded, $precision, '.', '');

        // Strip trailing zeros and unnecessary decimal point
        // "10.50" → "10.5", "10.00" → "10." → "10"
        if (str_contains($formatted, '.')) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, '.');
        }

        // Remove leading zero: "0.5" → ".5", "-0.5" → "-.5"
        if ($removeLeadingZero) {
            if (str_starts_with($formatted, '0.')) {
                $formatted = substr($formatted, 1);
            } elseif (str_starts_with($formatted, '-0.')) {
                $formatted = '-'.substr($formatted, 2);
            }
        }

        return $formatted;
    }

    /**
     * Rounds all numeric values found within an SVG attribute string.
     *
     * Useful for compound attributes like transform, d (path data), viewBox, and points.
     *
     * Examples (precision=2):
     *   "translate(10.12345, 20.98765)" → "translate(10.12, 20.99)"
     *   "M 10.555 20.444 L 30.999"     → "M 10.56 20.44 L 31"
     *
     * @param string $value             The attribute value containing numbers
     * @param int    $precision         Number of decimal places to round to
     * @param bool   $removeLeadingZero Whether to remove leading zeros before decimals
     */
    public static function roundInAttribute(string $value, int $precision, bool $removeLeadingZero = false): string
    {
        return preg_replace_callback(
            self::NUMBER_PATTERN,
            static fn (array $m): string => self::format((float) $m[0], $precision, $removeLeadingZero),
            $value,
        ) ?? $value;
    }
}
