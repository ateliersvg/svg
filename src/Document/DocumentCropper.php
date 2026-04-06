<?php

declare(strict_types=1);

namespace Atelier\Svg\Document;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\Clipping\ClipPathElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Path\Data;

/**
 * Helper for cropping/clipping SVG elements.
 */
final readonly class DocumentCropper
{
    public function __construct(
        private AbstractElement $element,
    ) {
    }

    /**
     * Crops to a rectangle using clipPath.
     *
     * @param float $x      Rectangle X coordinate
     * @param float $y      Rectangle Y coordinate
     * @param float $width  Rectangle width
     * @param float $height Rectangle height
     */
    public function toRect(float $x, float $y, float $width, float $height): self
    {
        $clipPath = $this->createClipPath();

        // Create rectangle for clipping
        $rect = new RectElement();
        $rect->setAttribute('x', $x);
        $rect->setAttribute('y', $y);
        $rect->setAttribute('width', $width);
        $rect->setAttribute('height', $height);

        $clipPath->appendChild($rect);
        $this->applyClipPath($clipPath);

        return $this;
    }

    /**
     * Crops to a circle using clipPath.
     *
     * @param float $cx Circle center X
     * @param float $cy Circle center Y
     * @param float $r  Circle radius
     */
    public function toCircle(float $cx, float $cy, float $r): self
    {
        $clipPath = $this->createClipPath();

        // Create circle for clipping
        $circle = new CircleElement();
        $circle->setAttribute('cx', $cx);
        $circle->setAttribute('cy', $cy);
        $circle->setAttribute('r', $r);

        $clipPath->appendChild($circle);
        $this->applyClipPath($clipPath);

        return $this;
    }

    /**
     * Crops to an ellipse using clipPath.
     *
     * @param float $cx Ellipse center X
     * @param float $cy Ellipse center Y
     * @param float $rx Ellipse radius X
     * @param float $ry Ellipse radius Y
     */
    public function toEllipse(float $cx, float $cy, float $rx, float $ry): self
    {
        $clipPath = $this->createClipPath();

        // Create ellipse for clipping
        $ellipse = new EllipseElement();
        $ellipse->setAttribute('cx', $cx);
        $ellipse->setAttribute('cy', $cy);
        $ellipse->setAttribute('rx', $rx);
        $ellipse->setAttribute('ry', $ry);

        $clipPath->appendChild($ellipse);
        $this->applyClipPath($clipPath);

        return $this;
    }

    /**
     * Crops to a custom path using clipPath.
     *
     * @param string|Data $path Path data
     */
    public function toPath(string|Data $path): self
    {
        $clipPath = $this->createClipPath();

        // Create path for clipping
        $pathElement = new PathElement();
        $pathData = $path instanceof Data ? $path->toString() : $path;
        $pathElement->setAttribute('d', $pathData);

        $clipPath->appendChild($pathElement);
        $this->applyClipPath($clipPath);

        return $this;
    }

    /**
     * Removes crop/clip from the element.
     */
    public function clear(): self
    {
        $this->element->removeAttribute('clip-path');

        return $this;
    }

    /**
     * Gets the current clip path ID if any.
     */
    public function getClipPathId(): ?string
    {
        $clipPathAttr = $this->element->getAttribute('clip-path');
        if (null === $clipPathAttr) {
            return null;
        }

        // Extract ID from url(#id) format
        if (preg_match('/url\(#([^)]+)\)/', $clipPathAttr, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Creates a new clipPath element with unique ID.
     */
    private function createClipPath(): ClipPathElement
    {
        $clipPath = new ClipPathElement();
        $id = $this->generateClipPathId();
        $clipPath->setAttribute('id', $id);

        return $clipPath;
    }

    /**
     * Applies a clipPath to the element.
     */
    private function applyClipPath(ClipPathElement $clipPath): void
    {
        // Find or create defs section
        $defs = $this->findOrCreateDefs();

        // Add clipPath to defs
        $defs->appendChild($clipPath);

        // Reference clipPath from element
        $id = $clipPath->getAttribute('id');
        $this->element->setAttribute('clip-path', "url(#{$id})");
    }

    /**
     * Finds or creates a defs element in the document.
     */
    private function findOrCreateDefs(): DefsElement
    {
        // Try to find existing defs
        $parent = $this->element->getParent();
        while (null !== $parent) {
            if ($parent instanceof \Atelier\Svg\Element\ContainerElementInterface) {
                foreach ($parent->getChildren() as $child) {
                    if ($child instanceof DefsElement) {
                        return $child;
                    }
                }
            }
            $parent = $parent->getParent();
        }

        // Create new defs at root
        $defs = new DefsElement();

        // Find root SVG element
        $root = $this->findRoot();

        // Prepend defs to root
        if ($root instanceof \Atelier\Svg\Element\ContainerElementInterface) {
            // Since prependChild is not available, we append it.
            // SVG allows defs anywhere, but ideally it should be at the top.
            // For now, appending is safer than clearing and re-adding all children.
            $root->appendChild($defs);
        }

        return $defs;
    }

    /**
     * Finds the root SVG element.
     */
    private function findRoot(): \Atelier\Svg\Element\ElementInterface
    {
        $current = $this->element;
        while (null !== $current->getParent()) {
            $parent = $current->getParent();
            if ($parent instanceof AbstractElement) {
                $current = $parent;
            }
        }

        return $current;
    }

    /**
     * Generates a unique clip path ID.
     */
    private function generateClipPathId(): string
    {
        return 'clip-'.uniqid('', true);
    }
}
