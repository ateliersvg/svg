<?php

declare(strict_types=1);

namespace Atelier\Svg\Exception;

/**
 * Marker interface for all exceptions thrown by the Atelier SVG library.
 *
 * All library-specific exceptions implement this interface, allowing
 * callers to catch any library exception with a single catch block:
 *
 * ```php
 * try {
 *     $svg = Svg::load('input.svg');
 * } catch (SvgExceptionInterface $e) {
 *     // Handle any library exception
 * }
 * ```
 */
interface SvgExceptionInterface extends \Throwable
{
}
