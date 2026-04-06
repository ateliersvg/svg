<?php

declare(strict_types=1);

namespace Atelier\Svg\Layout;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\BoundingBox;

/**
 * Helper for positioning and aligning SVG elements within a container.
 *
 * Use this on container elements (groups, SVG root) to layout their children:
 * ```php
 * $group = $doc->g();
 * $group->layout()->grid($children, columns: 3, gapX: 10, gapY: 10);
 * ```
 */
final readonly class LayoutBuilder
{
    public function __construct(
        private ContainerElementInterface $container,
    ) {
    }

    /**
     * Positions an element at specific coordinates.
     *
     * @param AbstractElement $element Element to position
     * @param float           $x       Target X coordinate
     * @param float           $y       Target Y coordinate
     * @param string          $anchor  Anchor point on element (default: 'top-left')
     */
    public function positionAt(
        AbstractElement $element,
        float $x,
        float $y,
        string $anchor = 'top-left',
    ): self {
        $bbox = $element->bbox()->get();
        $anchorPoint = $bbox->getAnchor($anchor);

        // Calculate translation needed
        $dx = $x - $anchorPoint->x;
        $dy = $y - $anchorPoint->y;

        $element->transform()->translate($dx, $dy)->apply();

        return $this;
    }

    /**
     * Centers an element within the container.
     *
     * @param AbstractElement $element    Element to center
     * @param bool            $horizontal Center horizontally
     * @param bool            $vertical   Center vertically
     */
    public function center(
        AbstractElement $element,
        bool $horizontal = true,
        bool $vertical = true,
    ): self {
        $containerBBox = $this->getContainerBBox();
        $elementBBox = $element->bbox()->get();

        $dx = $horizontal ? ($containerBBox->getCenter()->x - $elementBBox->getCenter()->x) : 0;
        $dy = $vertical ? ($containerBBox->getCenter()->y - $elementBBox->getCenter()->y) : 0;

        if (0.0 !== $dx || 0.0 !== $dy) {
            $element->transform()->translate($dx, $dy)->apply();
        }

        return $this;
    }

    /**
     * Aligns an element to a container edge.
     *
     * @param AbstractElement $element   Element to align
     * @param string          $alignment Alignment direction: 'left', 'right', 'top', 'bottom', 'center'
     * @param float           $offset    Offset from edge (default: 0)
     */
    public function align(
        AbstractElement $element,
        string $alignment,
        float $offset = 0,
    ): self {
        $containerBBox = $this->getContainerBBox();
        $elementBBox = $element->bbox()->get();

        match ($alignment) {
            'left' => $this->positionAt($element, $containerBBox->minX + $offset, $elementBBox->minY, 'top-left'),
            'right' => $this->positionAt($element, $containerBBox->maxX - $offset, $elementBBox->minY, 'top-right'),
            'top' => $this->positionAt($element, $elementBBox->minX, $containerBBox->minY + $offset, 'top-left'),
            'bottom' => $this->positionAt($element, $elementBBox->minX, $containerBBox->maxY - $offset, 'bottom-left'),
            'center' => $this->center($element),
            default => throw new InvalidArgumentException("Invalid alignment: {$alignment}"),
        };

        return $this;
    }

    /**
     * Distributes elements evenly along an axis.
     *
     * @param array<AbstractElement> $elements  Elements to distribute
     * @param string                 $direction 'horizontal' or 'vertical'
     * @param float                  $gap       Gap between elements
     * @param string|null            $align     Cross-axis alignment
     */
    public function distribute(
        array $elements,
        string $direction = 'horizontal',
        float $gap = 0,
        ?string $align = null,
    ): self {
        if (empty($elements)) {
            return $this;
        }

        $containerBBox = $this->getContainerBBox();
        $bboxes = array_map(fn ($el) => $el->bbox()->get(), $elements);

        if ('horizontal' === $direction) {
            $totalWidth = array_sum(array_map(fn ($b) => $b->getWidth(), $bboxes));
            $totalGap = $gap * (count($elements) - 1);
            $availableSpace = $containerBBox->getWidth() - $totalWidth - $totalGap;
            $spacing = $availableSpace > 0 ? $availableSpace / (count($elements) + 1) : 0;

            $x = $containerBBox->minX + $spacing;

            foreach ($elements as $i => $element) {
                $bbox = $bboxes[$i];

                // Position horizontally
                $y = $align ? $this->calculateAlignedY($bbox, $align, $containerBBox) : $bbox->minY;
                $this->positionAt($element, $x, $y, 'top-left');

                $x += $bbox->getWidth() + $gap + $spacing;
            }
        } else {
            // Vertical distribution
            $totalHeight = array_sum(array_map(fn ($b) => $b->getHeight(), $bboxes));
            $totalGap = $gap * (count($elements) - 1);
            $availableSpace = $containerBBox->getHeight() - $totalHeight - $totalGap;
            $spacing = $availableSpace > 0 ? $availableSpace / (count($elements) + 1) : 0;

            $y = $containerBBox->minY + $spacing;

            foreach ($elements as $i => $element) {
                $bbox = $bboxes[$i];

                // Position vertically
                $x = $align ? $this->calculateAlignedX($bbox, $align, $containerBBox) : $bbox->minX;
                $this->positionAt($element, $x, $y, 'top-left');

                $y += $bbox->getHeight() + $gap + $spacing;
            }
        }

        return $this;
    }

    /**
     * Stacks elements vertically or horizontally with gap.
     *
     * @param array<AbstractElement> $elements  Elements to stack
     * @param string                 $direction 'horizontal' or 'vertical'
     * @param float                  $gap       Gap between elements
     * @param string                 $align     Cross-axis alignment: 'left', 'center', 'right' (horizontal) or 'top', 'center', 'bottom' (vertical)
     */
    public function stack(
        array $elements,
        string $direction = 'vertical',
        float $gap = 0,
        string $align = 'left',
    ): self {
        if (empty($elements)) {
            return $this;
        }

        $containerBBox = $this->getContainerBBox();
        $bboxes = array_map(fn ($el) => $el->bbox()->get(), $elements);

        if ('vertical' === $direction) {
            $y = $containerBBox->minY;

            foreach ($elements as $i => $element) {
                $bbox = $bboxes[$i];

                // Calculate X based on alignment
                $x = match ($align) {
                    'left' => $containerBBox->minX,
                    'center' => $containerBBox->getCenter()->x - $bbox->getWidth() / 2,
                    'right' => $containerBBox->maxX - $bbox->getWidth(),
                    default => $containerBBox->minX,
                };

                $this->positionAt($element, $x, $y, 'top-left');
                $y += $bbox->getHeight() + $gap;
            }
        } else {
            // Horizontal stack
            $x = $containerBBox->minX;

            foreach ($elements as $i => $element) {
                $bbox = $bboxes[$i];

                // Calculate Y based on alignment
                $y = match ($align) {
                    'top' => $containerBBox->minY,
                    'center' => $containerBBox->getCenter()->y - $bbox->getHeight() / 2,
                    'bottom' => $containerBBox->maxY - $bbox->getHeight(),
                    default => $containerBBox->minY,
                };

                $this->positionAt($element, $x, $y, 'top-left');
                $x += $bbox->getWidth() + $gap;
            }
        }

        return $this;
    }

    /**
     * Arranges elements in a grid.
     *
     * @param array<AbstractElement> $elements Elements to arrange
     * @param int                    $columns  Number of columns
     * @param float                  $gapX     Horizontal gap
     * @param float                  $gapY     Vertical gap
     * @param string                 $alignH   Horizontal alignment within cell: 'left', 'center', 'right'
     * @param string                 $alignV   Vertical alignment within cell: 'top', 'center', 'bottom'
     */
    public function grid(
        array $elements,
        int $columns,
        float $gapX = 0,
        float $gapY = 0,
        string $alignH = 'left',
        string $alignV = 'top',
    ): self {
        if (empty($elements) || $columns <= 0) {
            return $this;
        }

        $containerBBox = $this->getContainerBBox();
        $rows = (int) ceil(count($elements) / $columns);

        $cellWidth = ($containerBBox->getWidth() - $gapX * ($columns - 1)) / $columns;
        $cellHeight = ($containerBBox->getHeight() - $gapY * ($rows - 1)) / $rows;

        foreach ($elements as $i => $element) {
            $row = (int) floor($i / $columns);
            $col = $i % $columns;

            $bbox = $element->bbox()->get();

            $cellX = $containerBBox->minX + $col * ($cellWidth + $gapX);
            $cellY = $containerBBox->minY + $row * ($cellHeight + $gapY);

            // Align within cell
            $x = $cellX + match ($alignH) {
                'left' => 0,
                'center' => ($cellWidth - $bbox->getWidth()) / 2,
                'right' => $cellWidth - $bbox->getWidth(),
                default => 0,
            };

            $y = $cellY + match ($alignV) {
                'top' => 0,
                'center' => ($cellHeight - $bbox->getHeight()) / 2,
                'bottom' => $cellHeight - $bbox->getHeight(),
                default => 0,
            };

            $this->positionAt($element, $x, $y, 'top-left');
        }

        return $this;
    }

    /**
     * Gets the bounding box of the container.
     */
    private function getContainerBBox(): BoundingBox
    {
        if (!$this->container instanceof AbstractElement) {
            throw new \LogicException(sprintf('Container must be an AbstractElement, got %s', $this->container::class));
        }

        return $this->container->bbox()->getLocal();
    }

    /**
     * Calculates aligned Y position for horizontal distribution.
     */
    private function calculateAlignedY(BoundingBox $bbox, string $align, BoundingBox $containerBBox): float
    {
        return match ($align) {
            'top' => $containerBBox->minY,
            'center' => $containerBBox->getCenter()->y - $bbox->getHeight() / 2,
            'bottom' => $containerBBox->maxY - $bbox->getHeight(),
            default => $bbox->minY,
        };
    }

    /**
     * Calculates aligned X position for vertical distribution.
     */
    private function calculateAlignedX(BoundingBox $bbox, string $align, BoundingBox $containerBBox): float
    {
        return match ($align) {
            'left' => $containerBBox->minX,
            'center' => $containerBBox->getCenter()->x - $bbox->getWidth() / 2,
            'right' => $containerBBox->maxX - $bbox->getWidth(),
            default => $bbox->minX,
        };
    }
}
