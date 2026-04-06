<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an absolute (V) or relative (v) vertical lineto command.
 * Draws a vertical line from the current point to the specified y coordinate.
 */
final class VerticalLineTo extends AbstractSegment
{
    public function __construct(string $command, private readonly float $y)
    {
        parent::__construct($command);
        if ('V' !== strtoupper($command)) {
            throw new InvalidArgumentException('VerticalLineTo segment must use V or v command.');
        }
    }

    public function getY(): float
    {
        return $this->y;
    }

    #[\Override]
    public function commandArgumentsToString(): string
    {
        return (string) $this->y;
    }
}
