<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;

/**
 * Represents an absolute (L) or relative (l) lineto command.
 */
final class LineTo extends AbstractSegment
{
    public function __construct(string $command, private readonly Point $point)
    {
        parent::__construct($command);
        if ('L' !== strtoupper($command)) {
            throw new InvalidArgumentException('LineTo segment must use L or l command.');
        }
    }

    public function getTargetPoint(): Point
    {
        return $this->point;
    }

    #[\Override]
    public function commandArgumentsToString(): string
    {
        return (string) $this->point; // Uses Point's __toString
    }
}
