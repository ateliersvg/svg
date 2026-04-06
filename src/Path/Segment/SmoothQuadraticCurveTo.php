<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;

/**
 * Represents an absolute (T) or relative (t) smooth quadratic Bezier curve command.
 * Draws a quadratic Bezier curve from the current point to (x,y).
 * The control point is assumed to be the reflection of the control point
 * on the previous command relative to the current point.
 */
final class SmoothQuadraticCurveTo extends AbstractSegment
{
    public function __construct(string $command, private readonly Point $point)
    {
        parent::__construct($command);
        if ('T' !== strtoupper($command)) {
            throw new InvalidArgumentException('SmoothQuadraticCurveTo segment must use T or t command.');
        }
    }

    public function getTargetPoint(): Point
    {
        return $this->point;
    }

    #[\Override]
    public function commandArgumentsToString(): string
    {
        return (string) $this->point;
    }
}
