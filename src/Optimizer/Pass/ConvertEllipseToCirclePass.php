<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;

/**
 * Optimization pass that converts non-eccentric ellipses to circles.
 *
 * An ellipse where rx === ry can be represented more efficiently as a circle.
 * This pass:
 * - Detects ellipses with equal radii (rx === ry)
 * - Converts them to circle elements
 * - Preserves all other attributes
 *
 * Benefits:
 * - Smaller file size (circle has fewer attributes)
 * - Semantically clearer (circle vs ellipse)
 * - Better for further optimizations
 */
final readonly class ConvertEllipseToCirclePass implements OptimizerPassInterface
{
    /**
     * Creates a new ConvertEllipseToCirclePass.
     *
     * @param float $tolerance Tolerance for comparing rx and ry (default: 0.001)
     */
    public function __construct(
        private float $tolerance = 0.001,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'convert-ellipse-to-circle';
    }

    /**
     * Optimizes the document by converting non-eccentric ellipses to circles.
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
     * Recursively processes elements to convert ellipses to circles.
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

            // After processing children, convert any ellipses
            foreach ($children as $child) {
                if ($child instanceof EllipseElement) {
                    $circleElement = $this->convertEllipseToCircle($child);
                    if (null !== $circleElement) {
                        $this->replaceElement($element, $child, $circleElement);
                    }
                }
            }
        }
    }

    /**
     * Converts an ellipse to a circle if rx === ry.
     *
     * @param EllipseElement $ellipse The ellipse element
     *
     * @return CircleElement|null The circle element, or null if not convertible
     */
    private function convertEllipseToCircle(EllipseElement $ellipse): ?CircleElement
    {
        $rx = $this->parseFloat($ellipse->getAttribute('rx'));
        $ry = $this->parseFloat($ellipse->getAttribute('ry'));

        // Can't convert if either radius is missing or invalid
        if (null === $rx || null === $ry || $rx <= 0 || $ry <= 0) {
            return null;
        }

        // Check if radii are equal (within tolerance)
        if (abs($rx - $ry) > $this->tolerance) {
            return null; // Not a circle
        }

        // Create circle element
        $circle = new CircleElement();

        // Set center and radius
        $cx = $ellipse->getAttribute('cx') ?? '0';
        $cy = $ellipse->getAttribute('cy') ?? '0';
        $circle->setCx((float) $cx);
        $circle->setCy((float) $cy);
        $circle->setR($rx); // Use rx as the radius

        // Copy all other attributes except ellipse-specific ones
        $skipAttributes = ['rx', 'ry', 'cx', 'cy'];

        foreach ($ellipse->getAttributes() as $name => $value) {
            if (!in_array($name, $skipAttributes, true)) {
                $circle->setAttribute($name, $value);
            }
        }

        return $circle;
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
        $index = array_search($oldElement, $children, true);

        assert(false !== $index);

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

        // Handle scientific notation
        if (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?/', $value, $matches)) {
            return (float) $matches[0];
        }

        return null;
    }
}
