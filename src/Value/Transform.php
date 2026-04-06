<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

/**
 * Interface for SVG transform functions.
 */
interface Transform
{
    /**
     * Serializes the transform to its string representation.
     */
    public function toString(): string;
}
