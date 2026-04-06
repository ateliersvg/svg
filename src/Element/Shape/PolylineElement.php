<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Shape;

use Atelier\Svg\Element\AbstractContainerElement;

/**
 * Represents an SVG <polyline> element.
 *
 * The polyline element defines a set of connected straight line segments.
 * Unlike polygon, the shape is not closed (the last point is not connected to the first).
 *
 * @see https://www.w3.org/TR/SVG11/shapes.html#PolylineElement
 */
final class PolylineElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('polyline');
    }

    /**
     * Creates a new polyline with the given points.
     *
     * @param string $points The points attribute (e.g., "0,0 100,0 100,100")
     */
    public static function create(string $points): static
    {
        return (new static())->setPoints($points);
    }

    /**
     * Sets the points that define the polyline.
     *
     * @param string $points The points attribute (e.g., "0,0 100,0 100,100")
     */
    public function setPoints(string $points): static
    {
        $this->setAttribute('points', $points);

        return $this;
    }

    /**
     * Gets the points that define the polyline.
     *
     * @return string|null The points attribute value, or null if not set
     */
    public function getPoints(): ?string
    {
        return $this->getAttribute('points');
    }

    /**
     * Sets the points from an array of coordinate pairs.
     *
     * @param array<array{float, float}> $pointsArray Array of [x, y] coordinate pairs
     */
    public function setPointsFromArray(array $pointsArray): static
    {
        $pointsString = implode(' ', array_map(
            fn (array $point) => $point[0].','.$point[1],
            $pointsArray
        ));

        return $this->setPoints($pointsString);
    }

    /**
     * Gets the points as an array of coordinate pairs.
     *
     * @return array<array{float, float}>|null Array of [x, y] coordinate pairs, or null if not set
     */
    public function getPointsAsArray(): ?array
    {
        $points = $this->getPoints();
        if (null === $points || '' === trim($points)) {
            return null;
        }

        // Parse the points string
        // Points can be separated by whitespace and/or commas
        $points = preg_replace('/[\s,]+/', ' ', trim($points));
        assert(null !== $points && '' !== $points);

        $coords = explode(' ', $points);
        $result = [];

        for ($i = 0; $i < count($coords) - 1; $i += 2) {
            $result[] = [(float) $coords[$i], (float) $coords[$i + 1]];
        }

        return $result;
    }
}
