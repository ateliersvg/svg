<?php

declare(strict_types=1);

namespace Atelier\Svg\Exception;

/**
 * Exception thrown for errors in program logic.
 *
 * This exception represents a programming error that should be fixed in the code,
 * rather than handled at runtime. Common scenarios include:
 *
 * - Calling methods in the wrong order or state
 * - Invalid internal state or configuration
 * - Attempting operations that violate API contracts
 * - Logic errors that shouldn't occur in correct usage
 *
 * LogicException typically indicates a bug in the calling code that should be
 * fixed by the developer, rather than an error condition that needs runtime handling.
 *
 * Example:
 * ```php
 * // This will throw LogicException
 * $builder = new Builder();
 * $builder->rect(0, 0, 100, 100); // Called without svg() first
 * // Error: No container to add element to
 * ```
 */
final class LogicException extends \LogicException implements SvgExceptionInterface
{
}
