<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;

/**
 * Represents an absolute (S) or relative (s) smooth cubic Bezier curve command.
 * Draws a cubic Bezier curve from the current point to (x,y).
 * The first control point is assumed to be the reflection of the second control point
 * on the previous command relative to the current point.
 */
final class SmoothCurveTo extends AbstractSegment
{
    public function __construct(string $command, private readonly Point $controlPoint2, private readonly Point $point)
    {
        parent::__construct($command);
        if ('S' !== strtoupper($command)) {
            throw new InvalidArgumentException('SmoothCurveTo segment must use S or s command.');
        }
    }

    public function getControlPoint2(): Point
    {
        return $this->controlPoint2;
    }

    public function getTargetPoint(): Point
    {
        return $this->point;
    }

    #[\Override]
    public function commandArgumentsToString(): string
    {
        return $this->controlPoint2.' '.$this->point;
    }
}
