<?php

declare(strict_types=1);

namespace Atelier\Svg\Exception;

/**
 * Exception thrown when an invalid SVG attribute name or value is encountered.
 */
final class InvalidAttributeException extends \InvalidArgumentException implements SvgExceptionInterface
{
}
