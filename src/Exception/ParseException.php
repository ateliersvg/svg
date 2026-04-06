<?php

declare(strict_types=1);

namespace Atelier\Svg\Exception;

/**
 * Exception thrown when parsing SVG data fails due to syntax errors.
 *
 * This exception is thrown when the parser encounters malformed or invalid
 * SVG data that cannot be processed. This includes:
 *
 * - Invalid XML syntax in SVG documents
 * - Malformed attribute values (e.g., invalid path data, transform syntax)
 * - Incorrect numeric formats or units
 * - Unrecognized or improperly structured SVG elements
 *
 * ParseException extends RuntimeException because parsing failures occur at
 * runtime when processing external data. The exception message typically
 * includes details about what went wrong and where in the input.
 *
 * Example:
 * ```php
 * // This will throw ParseException for invalid path data
 * $path = PathData::parse("M 10 20 INVALID");
 *
 * // This will throw ParseException for malformed transform
 * $transform = Transform::parse("rotate(invalid)");
 * ```
 */
final class ParseException extends RuntimeException
{
}
