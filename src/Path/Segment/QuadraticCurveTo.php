<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;

/**
 * Represents an absolute (Q) or relative (q) quadratic Bezier curve command.
 * Draws a quadratic Bezier curve from the current point to (x,y) using (x1,y1)
 * as the control point.
 */
final class QuadraticCurveTo extends AbstractSegment
{
    public function __construct(string $command, private readonly Point $controlPoint, private readonly Point $point)
    {
        parent::__construct($command);
        if ('Q' !== strtoupper($command)) {
            throw new InvalidArgumentException('QuadraticCurveTo segment must use Q or q command.');
        }
    }

    public function getControlPoint(): Point
    {
        return $this->controlPoint;
    }

    public function getTargetPoint(): Point
    {
        return $this->point;
    }

    #[\Override]
    public function commandArgumentsToString(): string
    {
        return $this->controlPoint.' '.$this->point;
    }
}
