<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Geometry\Point;

interface SegmentInterface
{
    /**
     * Gets the SVG command character (e.g., 'M', 'l', 'C').
     */
    public function getCommand(): string;

    /**
     * Gets the target point of this segment (if applicable).
     * May return null for commands like ClosePath.
     */
    public function getTargetPoint(): ?Point;

    /**
     * Checks if the command used relative coordinates.
     */
    public function isRelative(): bool;

    /**
     * Basic string representation (used by Serializer).
     * Should output coordinates/parameters for the command.
     */
    public function commandArgumentsToString(): string;
}
