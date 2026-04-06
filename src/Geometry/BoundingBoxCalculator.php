<?php

declare(strict_types=1);

namespace Atelier\Svg\Geometry;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Path\PathAnalyzer;

/**
 * Helper for calculating bounding boxes of SVG elements.
 */
final readonly class BoundingBoxCalculator
{
    public function __construct(
        private AbstractElement $element,
    ) {
    }

    /**
     * Gets the bounding box in local space (without transforms).
     */
    public function getLocal(): BoundingBox
    {
        return match (true) {
            $this->element instanceof RectElement => $this->getRectBBox(),
            $this->element instanceof CircleElement => $this->getCircleBBox(),
            $this->element instanceof EllipseElement => $this->getEllipseBBox(),
            $this->element instanceof LineElement => $this->getLineBBox(),
            $this->element instanceof PolygonElement => $this->getPolygonBBox(),
            $this->element instanceof PolylineElement => $this->getPolylineBBox(),
            $this->element instanceof PathElement => $this->getPathBBox(),
            $this->element instanceof SvgElement => $this->getSvgBBox(),
            $this->element instanceof AbstractContainerElement => $this->getContainerBBox(),
            default => $this->getDefaultBBox(),
        };
    }

    /**
     * Gets the bounding box with element transforms applied (screen space).
     */
    public function get(): BoundingBox
    {
        $localBBox = $this->getLocal();
        $matrix = $this->element->transform()->getMatrix();

        return $matrix->transformBBox($localBBox);
    }

    /**
     * Gets the bounding box with all parent transforms applied.
     */
    public function getScreen(): BoundingBox
    {
        $localBBox = $this->getLocal();
        $matrix = $this->getEffectiveMatrix();

        return $matrix->transformBBox($localBBox);
    }

    /**
     * Gets the effective transformation matrix including parent transforms.
     */
    private function getEffectiveMatrix(): Matrix
    {
        $matrix = $this->element->transform()->getMatrix();

        // Walk up the parent chain and multiply matrices
        $parent = $this->element->getParent();
        while (null !== $parent) {
            if ($parent instanceof AbstractElement) {
                $parentMatrix = $parent->transform()->getMatrix();
                $matrix = $parentMatrix->multiply($matrix);
            }
            $parent = $parent->getParent();
        }

        return $matrix;
    }

    /**
     * Calculates bounding box for rectangles.
     */
    private function getRectBBox(): BoundingBox
    {
        $x = (float) ($this->element->getAttribute('x') ?? 0);
        $y = (float) ($this->element->getAttribute('y') ?? 0);
        $width = (float) ($this->element->getAttribute('width') ?? 0);
        $height = (float) ($this->element->getAttribute('height') ?? 0);

        return new BoundingBox(
            minX: $x,
            minY: $y,
            maxX: $x + $width,
            maxY: $y + $height
        );
    }

    /**
     * Calculates bounding box for circles.
     */
    private function getCircleBBox(): BoundingBox
    {
        $cx = (float) ($this->element->getAttribute('cx') ?? 0);
        $cy = (float) ($this->element->getAttribute('cy') ?? 0);
        $r = (float) ($this->element->getAttribute('r') ?? 0);

        return new BoundingBox(
            minX: $cx - $r,
            minY: $cy - $r,
            maxX: $cx + $r,
            maxY: $cy + $r
        );
    }

    /**
     * Calculates bounding box for ellipses.
     */
    private function getEllipseBBox(): BoundingBox
    {
        $cx = (float) ($this->element->getAttribute('cx') ?? 0);
        $cy = (float) ($this->element->getAttribute('cy') ?? 0);
        $rx = (float) ($this->element->getAttribute('rx') ?? 0);
        $ry = (float) ($this->element->getAttribute('ry') ?? 0);

        return new BoundingBox(
            minX: $cx - $rx,
            minY: $cy - $ry,
            maxX: $cx + $rx,
            maxY: $cy + $ry
        );
    }

    /**
     * Calculates bounding box for lines.
     */
    private function getLineBBox(): BoundingBox
    {
        $x1 = (float) ($this->element->getAttribute('x1') ?? 0);
        $y1 = (float) ($this->element->getAttribute('y1') ?? 0);
        $x2 = (float) ($this->element->getAttribute('x2') ?? 0);
        $y2 = (float) ($this->element->getAttribute('y2') ?? 0);

        return new BoundingBox(
            minX: min($x1, $x2),
            minY: min($y1, $y2),
            maxX: max($x1, $x2),
            maxY: max($y1, $y2)
        );
    }

    /**
     * Calculates bounding box for polygons.
     */
    private function getPolygonBBox(): BoundingBox
    {
        $points = $this->parsePoints();
        if (empty($points)) {
            return new BoundingBox(0, 0, 0, 0);
        }

        return BoundingBox::fromPoints(...$points);
    }

    /**
     * Calculates bounding box for polylines.
     */
    private function getPolylineBBox(): BoundingBox
    {
        $points = $this->parsePoints();
        if (empty($points)) {
            return new BoundingBox(0, 0, 0, 0);
        }

        return BoundingBox::fromPoints(...$points);
    }

    /**
     * Parses points attribute for polygon/polyline.
     *
     * @return array<Point>
     */
    private function parsePoints(): array
    {
        $pointsAttr = $this->element->getAttribute('points');
        if (null === $pointsAttr) {
            return [];
        }

        // Parse points: "x1,y1 x2,y2 x3,y3" or "x1 y1, x2 y2, x3 y3"
        $trimmed = trim($pointsAttr);
        if ('' === $trimmed) {
            return [];
        }
        $pointsAttr = (string) preg_replace('/[,\s]+/', ' ', $trimmed);
        $coords = explode(' ', $pointsAttr);

        $points = [];
        for ($i = 0; $i < count($coords) - 1; $i += 2) {
            $points[] = new Point((float) $coords[$i], (float) $coords[$i + 1]);
        }

        return $points;
    }

    /**
     * Calculates bounding box for paths.
     */
    private function getPathBBox(): BoundingBox
    {
        $d = $this->element->getAttribute('d');
        if (null === $d || '' === $d) {
            return new BoundingBox(0, 0, 0, 0);
        }

        try {
            $parser = new \Atelier\Svg\Path\PathParser();
            $pathData = $parser->parse($d);
            $analyzer = new PathAnalyzer($pathData);

            return $analyzer->getBoundingBox();
        } catch (\Throwable) {
            return new BoundingBox(0, 0, 0, 0);
        }
    }

    /**
     * Calculates bounding box for SVG root elements.
     */
    private function getSvgBBox(): BoundingBox
    {
        // 1. Try viewBox
        if ($this->element->hasAttribute('viewBox')) {
            $viewBox = $this->element->getAttribute('viewBox');
            if (null !== $viewBox) {
                $parts = preg_split('/[\s,]+/', trim($viewBox));
                if (false !== $parts && 4 === count($parts)) {
                    return new BoundingBox(
                        minX: (float) $parts[0],
                        minY: (float) $parts[1],
                        maxX: (float) $parts[0] + (float) $parts[2],
                        maxY: (float) $parts[1] + (float) $parts[3]
                    );
                }
            }
        }

        // 2. Try width/height
        $width = $this->element->getAttribute('width');
        $height = $this->element->getAttribute('height');

        if (null !== $width && null !== $height) {
            // Check if values are numeric (not percentage)
            if (is_numeric($width) && is_numeric($height)) {
                return new BoundingBox(0, 0, (float) $width, (float) $height);
            }
        }

        // 3. Fallback to container logic (children)
        return $this->getContainerBBox();
    }

    /**
     * Calculates bounding box for container elements (groups).
     */
    private function getContainerBBox(): BoundingBox
    {
        assert($this->element instanceof AbstractContainerElement);

        // Check for explicit width/height (used in tests/layout contexts)
        $width = $this->element->getAttribute('width');
        $height = $this->element->getAttribute('height');

        if (null !== $width && null !== $height && is_numeric($width) && is_numeric($height)) {
            $x = (float) ($this->element->getAttribute('x') ?? 0);
            $y = (float) ($this->element->getAttribute('y') ?? 0);

            return new BoundingBox($x, $y, $x + (float) $width, $y + (float) $height);
        }

        $children = $this->element->getChildren();
        if (empty($children)) {
            return new BoundingBox(0, 0, 0, 0);
        }

        $bbox = null;
        foreach ($children as $child) {
            assert($child instanceof AbstractElement);

            $childBBox = $child->bbox()->get();
            $bbox = null === $bbox ? $childBBox : $bbox->union($childBBox);
        }

        return $bbox;
    }

    /**
     * Default bounding box for unsupported elements.
     */
    private function getDefaultBBox(): BoundingBox
    {
        return new BoundingBox(0, 0, 0, 0);
    }
}
