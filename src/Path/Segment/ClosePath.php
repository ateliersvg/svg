<?php

declare(strict_types=1);

namespace Atelier\Svg\Path\Segment;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an absolute (Z) or relative (z) closepath command.
 */
final class ClosePath extends AbstractSegment
{
    public function __construct(string $command = 'Z') // Typically always absolute 'Z'
    {
        parent::__construct($command);
        if ('Z' !== strtoupper($command)) {
            throw new InvalidArgumentException('ClosePath segment must use Z or z command.');
        }
    }
}
