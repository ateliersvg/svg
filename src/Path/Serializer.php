<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Serializes structured SVG path commands into path data strings.
 *
 * This class converts an array or iterable of path command objects/arrays
 * into a valid SVG path data string suitable for the 'd' attribute of path
 * elements. It handles coordinate formatting, precision control, and proper
 * command syntax.
 *
 * The serializer supports all standard SVG path commands:
 * - M/m (moveto)
 * - L/l (lineto)
 * - H/h (horizontal lineto)
 * - V/v (vertical lineto)
 * - C/c (curveto)
 * - S/s (smooth curveto)
 * - Q/q (quadratic Bézier curveto)
 * - T/t (smooth quadratic Bézier curveto)
 * - A/a (elliptical arc)
 * - Z/z (closepath)
 *
 * Example:
 * ```php
 * $commands = [
 *     ['type' => 'M', 'coords' => [10, 20]],
 *     ['type' => 'L', 'coords' => [30, 40]],
 *     ['type' => 'Z']
 * ];
 *
 * $pathData = Serializer::serialize($commands);
 * // Result: "M10 20 L30 40 Z"
 * ```
 *
 * @see https://www.w3.org/TR/SVG/paths.html#PathData
 */
final class Serializer
{
    /**
     * Serializes path commands into an SVG path data string.
     *
     * Converts an iterable of command structures into a properly formatted
     * SVG path data string. Each command must have a 'type' property (single
     * letter command code) and optional 'coords' array for commands requiring
     * coordinates.
     *
     * Command structure formats:
     * - Array: `['type' => 'M', 'coords' => [10, 20]]`
     * - Object: `{type: 'L', coords: [30, 40]}`
     *
     * The precision parameter controls decimal places in coordinate output,
     * allowing size optimization while maintaining accuracy.
     *
     * @param iterable<int, array<string, mixed>|object> $commands  The path commands to serialize
     * @param int                                        $precision Floating point precision for coordinates (default: 6)
     *
     * @return string The serialized SVG path data string
     *
     * @throws InvalidArgumentException If command structure is invalid or required coordinates are missing
     */
    public static function serialize(iterable $commands, int $precision = 6): string
    {
        $d = [];
        foreach ($commands as $index => $command) {
            // Basic structure validation
            if (is_array($command)) {
                if (!isset($command['type'])) {
                    throw new InvalidArgumentException(sprintf('Command at index %d is missing a "type".', $index));
                }
                /** @var string $type */
                $type = $command['type'];
                /** @var array<int, float|int> $coords */
                $coords = isset($command['coords']) && is_array($command['coords']) ? $command['coords'] : [];
            } elseif (is_object($command)) {
                if (!property_exists($command, 'type')) {
                    throw new InvalidArgumentException(sprintf('Command object at index %d is missing a "type" property.', $index));
                }
                /** @var string $type */
                $type = $command->type;
                $coords = property_exists($command, 'coords') && is_array($command->coords ?? null) ? $command->coords : [];
            } else {
                throw new InvalidArgumentException(sprintf('Invalid command format at index %d; expected array or object.', $index));
            }

            // Basic validation of command type (can be expanded)
            if (1 !== strlen($type) || !ctype_alpha($type)) {
                throw new InvalidArgumentException(sprintf('Invalid command type "%s" at index %d.', $type, $index));
            }

            // Handle commands with and without coordinates
            if ('Z' === strtoupper($type)) { // ClosePath command
                if (!empty($coords)) {
                    // Optionally warn or ignore coordinates for 'Z'/'z'
                }
                $d[] = $type; // 'Z' or 'z'
            } elseif (!empty($coords)) {
                // Format coordinates for other commands
                $d[] = $type.self::formatCoordinates($coords, $precision);
            } else {
                // Command type requires coordinates but none were provided (e.g., M without coords)
                throw new InvalidArgumentException(sprintf('Coordinates missing for command type "%s" at index %d.', $type, $index));
            }
        }

        // Join commands with spaces (SVG path syntax often uses spaces, sometimes commas)
        return implode(' ', $d);
    }

    /**
     * Formats coordinate values into a space-separated string.
     *
     * Converts numeric coordinate values to strings with the specified
     * precision, removes unnecessary trailing zeros and decimal points,
     * and joins them with spaces.
     *
     * @param array<mixed> $coords    The coordinate values to format
     * @param int          $precision The decimal precision to use
     *
     * @return string The formatted coordinate string
     */
    private static function formatCoordinates(array $coords, int $precision): string
    {
        $formatted = [];
        foreach ($coords as $coord) {
            // Ensure numeric before formatting
            if (!is_numeric($coord)) {
                throw new InvalidArgumentException(sprintf('Invalid non-numeric coordinate value found: %s', gettype($coord)));
            }
            $formatted[] = self::formatNumber((float) $coord, $precision);
        }

        // SVG typically uses spaces, but commas are allowed. Spaces are often more compact.
        return implode(' ', $formatted);
    }

    /**
     * Formats a numeric value to a clean string representation with desired precision.
     * Avoids trailing zeros and unnecessary decimal points.
     */
    private static function formatNumber(float $value, int $precision): string
    {
        // Use sprintf with precision, then trim trailing zeros and potentially the decimal point
        $numberStr = sprintf('%.'.$precision.'f', $value);
        // Remove trailing zeros after decimal point
        if (str_contains($numberStr, '.')) {
            $numberStr = rtrim($numberStr, '0');
            // Remove trailing decimal point if it's the last character
            $numberStr = rtrim($numberStr, '.');
        }

        // Handle potential negative zero representation
        return ('-0' === $numberStr) ? '0' : $numberStr;
    }
}
