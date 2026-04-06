<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an SVG viewBox attribute value.
 *
 * The viewBox attribute defines the position and dimension of an SVG viewport.
 * Format: "min-x min-y width height"
 *
 * @see https://www.w3.org/TR/SVG11/coords.html#ViewBoxAttribute
 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/viewBox
 */
final readonly class Viewbox implements \Stringable
{
    public function __construct(
        private float $minX,
        private float $minY,
        private float $width,
        private float $height,
    ) {
        if ($width < 0) {
            throw new InvalidArgumentException('viewBox width cannot be negative');
        }

        if ($height < 0) {
            throw new InvalidArgumentException('viewBox height cannot be negative');
        }
    }

    /**
     * Parses a viewBox attribute string.
     *
     * @param string $value The viewBox string (e.g., "0 0 100 100")
     */
    public static function parse(string $value): self
    {
        $value = trim($value);

        if ('' === $value) {
            throw new InvalidArgumentException('Cannot parse an empty string as a viewBox.');
        }

        // viewBox can use whitespace and/or commas as separators
        $parts = preg_split('/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY);

        assert(false !== $parts);

        if (4 !== count($parts)) {
            throw new InvalidArgumentException(sprintf("viewBox must have exactly 4 values, got %d: '%s'", count($parts), $value));
        }

        $minX = self::parseNumber($parts[0], 'min-x');
        $minY = self::parseNumber($parts[1], 'min-y');
        $width = self::parseNumber($parts[2], 'width');
        $height = self::parseNumber($parts[3], 'height');

        return new self($minX, $minY, $width, $height);
    }

    private static function parseNumber(string $value, string $paramName): float
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(sprintf("Invalid viewBox %s value: '%s'", $paramName, $value));
        }

        return (float) $value;
    }

    public function getMinX(): float
    {
        return $this->minX;
    }

    public function getMinY(): float
    {
        return $this->minY;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * Gets the maximum X coordinate (minX + width).
     */
    public function getMaxX(): float
    {
        return $this->minX + $this->width;
    }

    /**
     * Gets the maximum Y coordinate (minY + height).
     */
    public function getMaxY(): float
    {
        return $this->minY + $this->height;
    }

    /**
     * Gets the center X coordinate.
     */
    public function getCenterX(): float
    {
        return $this->minX + $this->width / 2;
    }

    /**
     * Gets the center Y coordinate.
     */
    public function getCenterY(): float
    {
        return $this->minY + $this->height / 2;
    }

    /**
     * Gets the aspect ratio (width / height).
     */
    public function getAspectRatio(): float
    {
        if (0.0 === $this->height) {
            return 0.0;
        }

        return $this->width / $this->height;
    }

    /**
     * Serializes the viewBox to its string representation.
     */
    public function toString(): string
    {
        return sprintf(
            '%s %s %s %s',
            $this->formatNumber($this->minX),
            $this->formatNumber($this->minY),
            $this->formatNumber($this->width),
            $this->formatNumber($this->height)
        );
    }

    private function formatNumber(float $value): string
    {
        // Remove trailing zeros and decimal point if not needed
        $formatted = rtrim(rtrim(sprintf('%.6F', $value), '0'), '.');

        return '-0' === $formatted ? '0' : $formatted;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
