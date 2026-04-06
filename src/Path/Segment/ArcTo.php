<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;

/**
 * Represents an absolute (A) or relative (a) elliptical arc command.
 * Draws an elliptical arc from the current point to (x,y).
 */
final class ArcTo extends AbstractSegment
{
    public function __construct(
        string $command,
        private readonly float $rx,
        private readonly float $ry,
        private readonly float $xAxisRotation,
        private readonly bool $largeArcFlag,
        private readonly bool $sweepFlag,
        private readonly Point $point,
    ) {
        parent::__construct($command);
        if ('A' !== strtoupper($command)) {
            throw new InvalidArgumentException('ArcTo segment must use A or a command.');
        }
    }

    public function getRx(): float
    {
        return $this->rx;
    }

    public function getRy(): float
    {
        return $this->ry;
    }

    public function getXAxisRotation(): float
    {
        return $this->xAxisRotation;
    }

    public function getLargeArcFlag(): bool
    {
        return $this->largeArcFlag;
    }

    public function getSweepFlag(): bool
    {
        return $this->sweepFlag;
    }

    public function getTargetPoint(): Point
    {
        return $this->point;
    }

    #[\Override]
    public function commandArgumentsToString(): string
    {
        $largeArc = $this->largeArcFlag ? '1' : '0';
        $sweep = $this->sweepFlag ? '1' : '0';

        return $this->rx.','.$this->ry.' '.$this->xAxisRotation.' '.$largeArc.','.$sweep.' '.$this->point;
    }
}
