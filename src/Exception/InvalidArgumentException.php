<?php

declare(strict_types=1);

namespace Atelier\Svg\Exception;

/**
 * Exception thrown when invalid arguments are passed to SVG methods.
 *
 * This exception indicates that a method or function received arguments that
 * don't meet the expected criteria. Common scenarios include:
 *
 * - Invalid attribute names or values (e.g., negative radius for circles)
 * - Out-of-range numeric values
 * - Malformed strings or data structures
 * - Empty strings where non-empty values are required
 * - Invalid enumeration values
 *
 * Example:
 * ```php
 * // This will throw InvalidArgumentException
 * $circle = new CircleElement();
 * $circle->setR(-5); // Radius cannot be negative
 * ```
 */
final class InvalidArgumentException extends \InvalidArgumentException implements SvgExceptionInterface
{
}
