<?php

declare(strict_types=1);

namespace Atelier\Svg\Value\Transform;

use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\Transform;

/**
 * Represents an SVG translate transform.
 */
final readonly class TranslateTransform implements Transform
{
    public function __construct(
        private Length $tx,
        private Length $ty,
    ) {
    }

    public function getTx(): Length
    {
        return $this->tx;
    }

    public function getTy(): Length
    {
        return $this->ty;
    }

    public function toString(): string
    {
        // If ty is 0, we can omit it
        if (0.0 === $this->ty->getValue() && $this->ty->isUnitless()) {
            return "translate({$this->tx})";
        }

        return "translate({$this->tx},{$this->ty})";
    }
}
