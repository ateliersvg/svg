<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;

/**
 * Abstract base class for segments.
 */
abstract class AbstractSegment implements SegmentInterface
{
    // Store the original command character to preserve case (relative/absolute)
    protected string $originalCommand;

    public function __construct(string $command)
    {
        if (1 !== strlen($command) || !ctype_alpha($command)) {
            throw new InvalidArgumentException('Invalid command character: '.$command);
        }
        $this->originalCommand = $command;
    }

    public function getCommand(): string
    {
        return $this->originalCommand;
    }

    public function isRelative(): bool
    {
        // Lowercase commands are relative in SVG
        return ctype_lower($this->originalCommand);
    }

    // Default implementation for commands without points (like ClosePath)
    public function getTargetPoint(): ?Point
    {
        return null;
    }

    // Default implementation for commands without extra args (like ClosePath)
    public function commandArgumentsToString(): string
    {
        return '';
    }

    /**
     * Converts the segment to its string representation for SVG path data.
     */
    public function toString(): string
    {
        $args = $this->commandArgumentsToString();

        return trim($this->originalCommand.($args ? ' '.$args : ''));
    }
}
