<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Optimizer\Util\NumberFormatter;

/**
 * Optimization pass that converts simple SVG shapes to path elements.
 *
 * This conversion enables further path optimizations and often results in
 * smaller file sizes. The pass can convert rectangles, circles, ellipses,
 * lines, polygons, and polylines to equivalent path elements.
 *
 * Benefits:
 * - Enables path-specific optimizations (merging, simplification)
 * - Often results in smaller file size
 * - Allows for more advanced manipulations
 *
 * @see https://www.w3.org/TR/SVG11/shapes.html
 */
final readonly class ConvertShapeToPathPass implements OptimizerPassInterface
{
    /**
     * Creates a new ConvertShapeToPathPass.
     *
     * @param bool $convertRects     Convert <rect> elements to paths
     * @param bool $convertCircles   Convert <circle> elements to paths
     * @param bool $convertEllipses  Convert <ellipse> elements to paths
     * @param bool $convertLines     Convert <line> elements to paths
     * @param bool $convertPolygons  Convert <polygon> elements to paths
     * @param bool $convertPolylines Convert <polyline> elements to paths
     * @param bool $floats2Ints      Kept for backward compatibility; NumberFormatter handles this automatically
     * @param bool $allowExpansion   Allow conversions even when resulting path data is longer than the original shape-specific attributes
     *
     * @phpstan-ignore constructor.unusedParameter (floats2Ints kept for backward compatibility)
     */
    public function __construct(
        private bool $convertRects = true,
        private bool $convertCircles = true,
        private bool $convertEllipses = true,
        private bool $convertLines = true,
        private bool $convertPolygons = true,
        private bool $convertPolylines = true,
        bool $floats2Ints = true,
        private bool $allowExpansion = true,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'convert-shape-to-path';
    }

    /**
     * Optimizes the document by converting shapes to paths.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        $this->processElement($rootElement);
    }

    /**
     * Recursively processes elements to convert shapes to paths.
     *
     * @param ElementInterface $element The element to process
     */
    private function processElement(ElementInterface $element): void
    {
        // Process children first (bottom-up) if this is a container
        if ($element instanceof ContainerElementInterface) {
            // Use array copy to avoid modification during iteration
            $children = $element->getChildren();

            foreach ($children as $child) {
                $this->processElement($child);
            }

            // After processing children, convert any shapes
            foreach ($children as $child) {
                $pathElement = $this->convertShapeToPath($child);
                if (null !== $pathElement) {
                    $this->replaceElement($element, $child, $pathElement);
                }
            }
        }
    }

    /**
     * Converts a shape element to a path element if applicable.
     *
     * @param ElementInterface $element The element to convert
     *
     * @return PathElement|null The converted path element, or null if not convertible
     */
    private function convertShapeToPath(ElementInterface $element): ?PathElement
    {
        return match (true) {
            $this->convertRects && $element instanceof RectElement => $this->rectToPath($element),
            $this->convertCircles && $element instanceof CircleElement => $this->circleToPath($element),
            $this->convertEllipses && $element instanceof EllipseElement => $this->ellipseToPath($element),
            $this->convertLines && $element instanceof LineElement => $this->lineToPath($element),
            $this->convertPolygons && $element instanceof PolygonElement => $this->polygonToPath($element),
            $this->convertPolylines && $element instanceof PolylineElement => $this->polylineToPath($element),
            default => null,
        };
    }

    /**
     * Converts a rectangle to a path.
     *
     * @param RectElement $rect The rectangle element
     *
     * @return PathElement|null The converted path, or null if invalid
     */
    private function rectToPath(RectElement $rect): ?PathElement
    {
        $x = $this->parseFloat($rect->getAttribute('x') ?? '0') ?? 0.0;
        $y = $this->parseFloat($rect->getAttribute('y') ?? '0') ?? 0.0;
        $width = $this->parseFloat($rect->getAttribute('width'));
        $height = $this->parseFloat($rect->getAttribute('height'));

        // Skip invalid rectangles
        if (null === $width || null === $height || $width <= 0 || $height <= 0) {
            return null;
        }

        $rx = $this->parseFloat($rect->getAttribute('rx'));
        $ry = $this->parseFloat($rect->getAttribute('ry'));

        // Handle rounded corners
        if (null !== $rx || null !== $ry) {
            // If only one is specified, use it for both (SVG spec)
            $rx ??= $ry ?? 0.0;
            $ry ??= $rx;

            // Cap corner radii to half the width/height
            $rx = min($rx, $width / 2);
            $ry = min($ry, $height / 2);

            // If radii are effectively zero, treat as regular rect
            if ($rx > 0 && $ry > 0) {
                $pathData = $this->roundedRectPath($x, $y, $width, $height, $rx, $ry);

                return $this->createPathFromShape($rect, $pathData);
            }
        }

        // Regular rectangle (no rounded corners)
        $x2 = $x + $width;
        $y2 = $y + $height;

        $pathData = sprintf(
            'M%s %s L%s %s L%s %s L%s %s Z',
            $this->formatNumber($x),
            $this->formatNumber($y),
            $this->formatNumber($x2),
            $this->formatNumber($y),
            $this->formatNumber($x2),
            $this->formatNumber($y2),
            $this->formatNumber($x),
            $this->formatNumber($y2)
        );

        return $this->createPathFromShape($rect, $pathData);
    }

    /**
     * Generates path data for a rounded rectangle.
     *
     * @param float $x      X coordinate
     * @param float $y      Y coordinate
     * @param float $width  Width
     * @param float $height Height
     * @param float $rx     X-axis radius
     * @param float $ry     Y-axis radius
     *
     * @return string Path data string
     */
    private function roundedRectPath(float $x, float $y, float $width, float $height, float $rx, float $ry): string
    {
        // Start at top-left, after the rounded corner
        $x1 = $x + $rx;
        $x2 = $x + $width - $rx;
        $y1 = $y + $ry;
        $y2 = $y + $height - $ry;

        return sprintf(
            'M%s %s L%s %s A%s %s 0 0 1 %s %s L%s %s A%s %s 0 0 1 %s %s L%s %s A%s %s 0 0 1 %s %s L%s %s A%s %s 0 0 1 %s %s Z',
            $this->formatNumber($x1), $this->formatNumber($y),
            $this->formatNumber($x2), $this->formatNumber($y),
            $this->formatNumber($rx), $this->formatNumber($ry), $this->formatNumber($x + $width), $this->formatNumber($y1),
            $this->formatNumber($x + $width), $this->formatNumber($y2),
            $this->formatNumber($rx), $this->formatNumber($ry), $this->formatNumber($x2), $this->formatNumber($y + $height),
            $this->formatNumber($x1), $this->formatNumber($y + $height),
            $this->formatNumber($rx), $this->formatNumber($ry), $this->formatNumber($x), $this->formatNumber($y2),
            $this->formatNumber($x), $this->formatNumber($y1),
            $this->formatNumber($rx), $this->formatNumber($ry), $this->formatNumber($x1), $this->formatNumber($y)
        );
    }

    /**
     * Converts a circle to a path.
     *
     * @param CircleElement $circle The circle element
     *
     * @return PathElement|null The converted path, or null if invalid
     */
    private function circleToPath(CircleElement $circle): ?PathElement
    {
        $cx = $this->parseFloat($circle->getAttribute('cx') ?? '0') ?? 0.0;
        $cy = $this->parseFloat($circle->getAttribute('cy') ?? '0') ?? 0.0;
        $r = $this->parseFloat($circle->getAttribute('r'));

        // Skip invalid circles
        if (null === $r || $r <= 0) {
            return null;
        }

        // Circle as two semicircular arcs
        $x1 = $cx + $r;
        $x2 = $cx - $r;

        $pathData = sprintf(
            'M%s %s A%s %s 0 1 0 %s %s A%s %s 0 1 0 %s %s Z',
            $this->formatNumber($x1),
            $this->formatNumber($cy),
            $this->formatNumber($r),
            $this->formatNumber($r),
            $this->formatNumber($x2),
            $this->formatNumber($cy),
            $this->formatNumber($r),
            $this->formatNumber($r),
            $this->formatNumber($x1),
            $this->formatNumber($cy)
        );

        return $this->createPathFromShape($circle, $pathData);
    }

    /**
     * Converts an ellipse to a path.
     *
     * @param EllipseElement $ellipse The ellipse element
     *
     * @return PathElement|null The converted path, or null if invalid
     */
    private function ellipseToPath(EllipseElement $ellipse): ?PathElement
    {
        $cx = $this->parseFloat($ellipse->getAttribute('cx') ?? '0') ?? 0.0;
        $cy = $this->parseFloat($ellipse->getAttribute('cy') ?? '0') ?? 0.0;
        $rx = $this->parseFloat($ellipse->getAttribute('rx'));
        $ry = $this->parseFloat($ellipse->getAttribute('ry'));

        // Skip invalid ellipses
        if (null === $rx || null === $ry || $rx <= 0 || $ry <= 0) {
            return null;
        }

        // Ellipse as two semi-elliptical arcs
        $x1 = $cx + $rx;
        $x2 = $cx - $rx;

        $pathData = sprintf(
            'M%s %s A%s %s 0 1 0 %s %s A%s %s 0 1 0 %s %s Z',
            $this->formatNumber($x1),
            $this->formatNumber($cy),
            $this->formatNumber($rx),
            $this->formatNumber($ry),
            $this->formatNumber($x2),
            $this->formatNumber($cy),
            $this->formatNumber($rx),
            $this->formatNumber($ry),
            $this->formatNumber($x1),
            $this->formatNumber($cy)
        );

        return $this->createPathFromShape($ellipse, $pathData);
    }

    /**
     * Converts a line to a path.
     *
     * @param LineElement $line The line element
     *
     * @return PathElement|null The converted path, or null if invalid
     */
    private function lineToPath(LineElement $line): ?PathElement
    {
        $x1 = $this->parseFloat($line->getAttribute('x1') ?? '0') ?? 0.0;
        $y1 = $this->parseFloat($line->getAttribute('y1') ?? '0') ?? 0.0;
        $x2 = $this->parseFloat($line->getAttribute('x2') ?? '0') ?? 0.0;
        $y2 = $this->parseFloat($line->getAttribute('y2') ?? '0') ?? 0.0;

        $pathData = sprintf(
            'M%s %s L%s %s',
            $this->formatNumber($x1),
            $this->formatNumber($y1),
            $this->formatNumber($x2),
            $this->formatNumber($y2)
        );

        return $this->createPathFromShape($line, $pathData);
    }

    /**
     * Converts a polygon to a path.
     *
     * @param PolygonElement $polygon The polygon element
     *
     * @return PathElement|null The converted path, or null if invalid
     */
    private function polygonToPath(PolygonElement $polygon): ?PathElement
    {
        $points = $this->parsePoints($polygon->getAttribute('points'));

        if (null === $points || count($points) < 2) {
            return null;
        }

        $pathData = $this->pointsToPathData($points, true);

        return $this->createPathFromShape($polygon, $pathData);
    }

    /**
     * Converts a polyline to a path.
     *
     * @param PolylineElement $polyline The polyline element
     *
     * @return PathElement|null The converted path, or null if invalid
     */
    private function polylineToPath(PolylineElement $polyline): ?PathElement
    {
        $points = $this->parsePoints($polyline->getAttribute('points'));

        if (null === $points || count($points) < 2) {
            return null;
        }

        $pathData = $this->pointsToPathData($points, false);

        return $this->createPathFromShape($polyline, $pathData);
    }

    /**
     * Converts points array to path data string.
     *
     * @param array<array{float, float}> $points Array of [x, y] coordinate pairs
     * @param bool                       $close  Whether to close the path with Z command
     *
     * @return string Path data string
     */
    private function pointsToPathData(array $points, bool $close): string
    {
        $commands = [];

        // First point is MoveTo
        $commands[] = sprintf(
            'M%s %s',
            $this->formatNumber($points[0][0]),
            $this->formatNumber($points[0][1])
        );

        // Remaining points are LineTo
        for ($i = 1; $i < count($points); ++$i) {
            $commands[] = sprintf(
                'L%s %s',
                $this->formatNumber($points[$i][0]),
                $this->formatNumber($points[$i][1])
            );
        }

        // Close path if needed
        if ($close) {
            $commands[] = 'Z';
        }

        return implode(' ', $commands);
    }

    /**
     * Creates a path element from a shape element, preserving attributes.
     *
     * @param ElementInterface $shape    The original shape element
     * @param string           $pathData The path data string
     *
     * @return PathElement|null The new path element or null if conversion is not beneficial
     */
    private function createPathFromShape(ElementInterface $shape, string $pathData): ?PathElement
    {
        $skipAttributes = ['x', 'y', 'width', 'height', 'rx', 'ry', 'r', 'cx', 'cy', 'x1', 'y1', 'x2', 'y2', 'points'];

        if (!$this->allowExpansion && !$this->isPathShorterThanShapeAttributes($shape, $pathData, $skipAttributes)) {
            return null;
        }

        $path = new PathElement();
        $path->setPathData($pathData);

        // Copy all attributes except shape-specific ones
        foreach ($shape->getAttributes() as $name => $value) {
            if (!in_array($name, $skipAttributes, true)) {
                $path->setAttribute($name, $value);
            }
        }

        return $path;
    }

    /**
     * Checks if the generated path data is shorter than the shape-specific attributes being removed.
     *
     * @param ElementInterface $shape                   The original shape element
     * @param string           $pathData                The generated path data
     * @param array<string>    $shapeSpecificAttributes Attributes that will be removed during conversion
     */
    private function isPathShorterThanShapeAttributes(
        ElementInterface $shape,
        string $pathData,
        array $shapeSpecificAttributes,
    ): bool {
        $originalLength = 0;

        foreach ($shapeSpecificAttributes as $attribute) {
            if ($shape->hasAttribute($attribute)) {
                $value = (string) $shape->getAttribute($attribute);
                $originalLength += strlen($attribute) + strlen($value) + 3; // account for =""
            }
        }

        assert(0 !== $originalLength);

        $pathLength = strlen('d="'.$pathData.'"');

        return $pathLength <= $originalLength;
    }

    /**
     * Replaces an element with another in its parent container.
     *
     * @param ContainerElementInterface $parent     The parent container
     * @param ElementInterface          $oldElement The element to replace
     * @param ElementInterface          $newElement The replacement element
     */
    private function replaceElement(
        ContainerElementInterface $parent,
        ElementInterface $oldElement,
        ElementInterface $newElement,
    ): void {
        $children = $parent->getChildren();
        $index = null;

        foreach ($children as $i => $child) {
            if ($child === $oldElement) {
                $index = $i;
                break;
            }
        }

        assert(null !== $index);

        // Build new children array with replacement
        $newChildren = [];
        foreach ($children as $i => $child) {
            if ($i === $index) {
                $newChildren[] = $newElement;
            } else {
                $newChildren[] = $child;
            }
        }

        // Clear and re-add all children
        $parent->clearChildren();
        foreach ($newChildren as $child) {
            $parent->appendChild($child);
        }
    }

    /**
     * Parses a points attribute string into coordinate pairs.
     *
     * @param string|null $pointsStr The points attribute value
     *
     * @return array<array{float, float}>|null Array of [x, y] pairs, or null if invalid
     */
    private function parsePoints(?string $pointsStr): ?array
    {
        if (null === $pointsStr || '' === trim($pointsStr)) {
            return null;
        }

        // Points can be separated by whitespace and/or commas
        // Example: "10,20 30,40" or "10 20 30 40" or "10, 20, 30, 40"
        $pointsStr = preg_replace('/[,\s]+/', ' ', trim($pointsStr));

        assert(null !== $pointsStr && '' !== $pointsStr);

        $coords = explode(' ', $pointsStr);

        // Must have even number of coordinates
        if (0 !== count($coords) % 2) {
            return null;
        }

        $points = [];
        for ($i = 0; $i < count($coords) - 1; $i += 2) {
            $x = $this->parseFloat($coords[$i]);
            $y = $this->parseFloat($coords[$i + 1]);

            if (null === $x || null === $y) {
                return null;
            }

            $points[] = [$x, $y];
        }

        return $points;
    }

    /**
     * Parses a string to a float value.
     *
     * @param string|null $value The string to parse
     *
     * @return float|null The parsed float, or null if invalid
     */
    private function parseFloat(?string $value): ?float
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }

        $value = trim($value);

        // Handle units by stripping them (px, pt, em, etc.)
        // For simplicity, we'll just parse the numeric part
        if (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?/', $value, $matches)) {
            return (float) $matches[0];
        }

        return null;
    }

    /**
     * Formats a number for path data output.
     *
     * Uses high precision (10 decimals) to preserve coordinate accuracy
     * during shape-to-path conversion. Trailing zeros and unnecessary
     * decimal points are stripped automatically.
     */
    private function formatNumber(float $value): string
    {
        return NumberFormatter::format($value, 10);
    }
}
