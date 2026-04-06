<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;

/**
 * Represents the parsed value of the 'points' attribute for <polygon> and <polyline>.
 * It holds an ordered array of Atelier\Svg\Geometry\Point objects.
 * Coordinates can be separated by whitespace and/or commas.
 *
 * @see https://www.w3.org/TR/SVG11/shapes.html#PointsBNF
 * @see https://www.w3.org/TR/SVG2/shapes.html#PointsBNF
 */
final readonly class PointList implements \Stringable
{
    /**
     * Private constructor, use the static parse method.
     *
     * @param Point[] $points array of Point objects
     */
    private function __construct(private array $points)
    {
    }

    /**
     * Parses a 'points' attribute string (e.g., "10,20 30,40 50 60") into a PointList object.
     * Returns an empty PointList if the input is empty or contains only whitespace/commas.
     *
     * @param string|null $attributeValue the raw attribute string
     *
     * @throws InvalidArgumentException if the string contains non-numeric values or an odd number of coordinates
     */
    public static function parse(?string $attributeValue): self
    {
        $trimmedValue = trim((string) $attributeValue);

        // An empty points attribute is valid and results in an empty list
        if ('' === $trimmedValue) {
            return new self([]);
        }

        // Split the string into individual number strings by whitespace and/or commas
        $coords = preg_split('/[,\s]+/', $trimmedValue, -1, PREG_SPLIT_NO_EMPTY);

        // If splitting resulted in nothing (e.g., input was " , "), return empty list
        if (empty($coords)) {
            return new self([]);
        }

        // Must have an even number of coordinates (x1 y1 x2 y2...)
        $count = count($coords);
        if (0 !== $count % 2) {
            throw new InvalidArgumentException(sprintf("Invalid points attribute: Expected an even number of coordinates, found %d in '%s'.", $count, $trimmedValue));
        }

        $parsedPoints = [];
        for ($i = 0; $i < $count; $i += 2) {
            $xStr = $coords[$i];
            $yStr = $coords[$i + 1];

            // Validate that both components are numeric before creating the Point
            if (!is_numeric($xStr) || !is_numeric($yStr)) {
                throw new InvalidArgumentException(sprintf("Invalid points attribute: Non-numeric coordinate found near '%s,%s' in '%s'.", $xStr, $yStr, $trimmedValue));
            }

            // Assuming Point constructor takes float x, float y
            $parsedPoints[] = new Point((float) $xStr, (float) $yStr);
        }

        return new self($parsedPoints);
    }

    /**
     * Gets the array of Point objects.
     *
     * @return Point[]
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    /**
     * Checks if the points list is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->points);
    }

    /**
     * Serializes the PointList object back to a string suitable for the 'points' attribute.
     * Uses "x,y" format for coordinate pairs, separated by spaces. Returns an empty string
     * if the list is empty.
     */
    public function toString(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        $pairs = [];
        foreach ($this->points as $point) {
            // Assuming Point has __toString or we format here
            // Let's format here for consistency using comma separator for pair
            $pairs[] = $this->formatCoordinate($point->x).','.$this->formatCoordinate($point->y);
        }

        // Join pairs with a single space
        return implode(' ', $pairs);
    }

    /**
     * Helper method to format coordinate numbers consistently.
     * (Could be shared via a Trait or utility class if used in many Value objects).
     */
    private function formatCoordinate(float $number): string
    {
        $numberStr = rtrim(rtrim(sprintf('%.6G', $number), '0'), '.');
        if (str_contains(strtolower($numberStr), 'e')) {
            $numberStr = sprintf('%.5f', $number); // Fallback precision
            $numberStr = rtrim(rtrim($numberStr, '0'), '.');
        }
        if ('-0' === $numberStr) {
            $numberStr = '0';
        }
        // If it was an integer to begin with and had no decimal
        if (!str_contains($numberStr, '.') && floatval($numberStr) === floor(floatval($numberStr))) {
            return (string) (int) $numberStr;
        }

        return $numberStr;
    }

    /**
     * Magic method for string conversion.
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
