<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Simplifier;

use Atelier\Svg\Path\Data;

interface SimplifierInterface
{
    /**
     * Simplifies the given path data using a specific algorithm.
     *
     * It's recommended this method does not modify the original Path\Data object,
     * but returns a new one with the simplified segments.
     *
     * @param Data  $pathData  the original path data object
     * @param float $tolerance A tolerance value controlling simplification aggressiveness.
     *                         Its meaning depends on the algorithm (e.g., max distance error for RDP).
     *                         Must be non-negative.
     *
     * @return Data a new Path\Data object with the simplified path segments
     *
     * @throws \Atelier\Svg\Exception\InvalidArgumentException if tolerance is negative
     */
    public function simplify(Data $pathData, float $tolerance): Data;
}
