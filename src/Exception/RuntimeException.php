<?php

declare(strict_types=1);

namespace Atelier\Svg\Exception;

/**
 * Exception thrown for runtime errors in SVG operations.
 *
 * This exception is thrown when an error occurs during the execution of an
 * operation that couldn't be detected at compile time. Typical scenarios include:
 *
 * - File system errors (unable to read/write SVG files)
 * - Resource allocation failures
 * - Unexpected states during processing
 * - External dependency failures
 *
 * Unlike InvalidArgumentException which indicates incorrect input, RuntimeException
 * indicates that something went wrong during execution even though the inputs
 * may have been valid.
 *
 * Example:
 * ```php
 * // This will throw RuntimeException if file doesn't exist
 * $loader = new DomLoader();
 * $document = $loader->loadFromFile('/nonexistent/file.svg');
 * ```
 */
class RuntimeException extends \RuntimeException implements SvgExceptionInterface
{
}
