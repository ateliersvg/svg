<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;

/**
 * Represents an absolute (C) or relative (c) cubic Bezier curve command.
 * Draws a cubic Bezier curve from the current point to (x,y) using (x1,y1)
 * as the control point at the beginning of the curve and (x2,y2) as the
 * control point at the end of the curve.
 */
final class CurveTo extends AbstractSegment
{
    public function __construct(string $command, private readonly Point $controlPoint1, private readonly Point $controlPoint2, private readonly Point $point)
    {
        parent::__construct($command);
        if ('C' !== strtoupper($command)) {
            throw new InvalidArgumentException('CurveTo segment must use C or c command.');
        }
    }

    public function getControlPoint1(): Point
    {
        return $this->controlPoint1;
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
        return $this->controlPoint1.' '.$this->controlPoint2.' '.$this->point;
    }
}
