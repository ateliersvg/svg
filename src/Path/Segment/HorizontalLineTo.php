<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an absolute (H) or relative (h) horizontal lineto command.
 * Draws a horizontal line from the current point to the specified x coordinate.
 */
final class HorizontalLineTo extends AbstractSegment
{
    public function __construct(string $command, private readonly float $x)
    {
        parent::__construct($command);
        if ('H' !== strtoupper($command)) {
            throw new InvalidArgumentException('HorizontalLineTo segment must use H or h command.');
        }
    }

    public function getX(): float
    {
        return $this->x;
    }

    #[\Override]
    public function commandArgumentsToString(): string
    {
        return (string) $this->x;
    }
}
