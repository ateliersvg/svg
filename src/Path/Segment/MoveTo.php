<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;

/**
 * Represents an absolute (M) or relative (m) moveto command.
 */
final class MoveTo extends AbstractSegment
{
    public function __construct(string $command, private readonly Point $point)
    {
        parent::__construct($command);
        if ('M' !== strtoupper($command)) {
            throw new InvalidArgumentException('MoveTo segment must use M or m command.');
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
