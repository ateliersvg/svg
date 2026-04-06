<?php

declare(strict_types=1);

namespace Atelier\Svg\Layout;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\RectElement;

/**
 * Utility class for creating responsive SVG documents.
 *
 * This helper provides methods to manage viewBox, aspect ratios, and responsive sizing
 * for SVG documents, making them fluid and adaptable to different viewport sizes.
 */
final class LayoutManager
{
    /**
     * Makes an SVG responsive by removing fixed dimensions and ensuring viewBox is set.
     *
     * This removes width and height attributes and sets a viewBox if not already present,
     * allowing the SVG to scale fluidly with its container.
     *
     * @param Document $document The SVG document to make responsive
     *
     * @return Document The modified document (for method chaining)
     */
    public static function makeResponsive(Document $document): Document
    {
        $root = $document->getRootElement();
        if (null === $root) {
            return $document;
        }

        // Get current dimensions before removing them
        $width = $root->getAttribute('width');
        $height = $root->getAttribute('height');

        // If no viewBox exists, create one from current dimensions
        if (!$root->hasAttribute('viewBox')) {
            $w = null !== $width ? (float) $width : 300;
            $h = null !== $height ? (float) $height : 150;
            self::setViewBox($document, 0, 0, $w, $h);
        }

        // Remove fixed dimensions
        $root->removeAttribute('width');
        $root->removeAttribute('height');

        return $document;
    }

    /**
     * Sets intrinsic size while maintaining viewBox for responsive behavior.
     *
     * This sets both width/height attributes (for intrinsic sizing) and viewBox
     * (for responsive scaling).
     *
     * @param Document $document The SVG document
     * @param float    $width    The intrinsic width
     * @param float    $height   The intrinsic height
     *
     * @return Document The modified document (for method chaining)
     */
    public static function setIntrinsicSize(Document $document, float $width, float $height): Document
    {
        $root = $document->getRootElement();
        if (null === $root) {
            return $document;
        }

        $root->setAttribute('width', $width);
        $root->setAttribute('height', $height);
        self::setViewBox($document, 0, 0, $width, $height);

        return $document;
    }

    /**
     * Gets the viewBox of an SVG document.
     *
     * @param Document $document The SVG document
     *
     * @return array{0: float, 1: float, 2: float, 3: float}|null Array of [minX, minY, width, height] or null if not set
     */
    public static function getViewBox(Document $document): ?array
    {
        $root = $document->getRootElement();
        if (null === $root) {
            return null;
        }
        $viewBox = $root->getAttribute('viewBox');

        if (null === $viewBox) {
            return null;
        }

        $parts = preg_split('/[\s,]+/', trim($viewBox));
        if (false === $parts || 4 !== count($parts)) {
            return null;
        }

        return [
            (float) $parts[0],
            (float) $parts[1],
            (float) $parts[2],
            (float) $parts[3],
        ];
    }

    /**
     * Sets the viewBox of an SVG document.
     *
     * @param Document $document The SVG document
     * @param float    $minX     The minimum X coordinate
     * @param float    $minY     The minimum Y coordinate
     * @param float    $width    The width of the viewBox
     * @param float    $height   The height of the viewBox
     *
     * @return Document The modified document (for method chaining)
     */
    public static function setViewBox(Document $document, float $minX, float $minY, float $width, float $height): Document
    {
        $root = $document->getRootElement();
        if (null === $root) {
            return $document;
        }
        $root->setAttribute('viewBox', "{$minX} {$minY} {$width} {$height}");

        return $document;
    }

    /**
     * Sets the preserveAspectRatio attribute.
     *
     * @param Document $document The SVG document
     * @param string   $value    The preserveAspectRatio value (e.g., 'xMidYMid meet', 'none')
     *
     * @return Document The modified document (for method chaining)
     */
    public static function setPreserveAspectRatio(Document $document, string $value): Document
    {
        $root = $document->getRootElement();
        if (null === $root) {
            return $document;
        }
        $root->setAttribute('preserveAspectRatio', $value);

        return $document;
    }

    /**
     * Calculates the bounding box of all content in the document.
     *
     * This method traverses all elements and calculates their bounds based on
     * their geometric attributes. Currently supports: rect, circle, ellipse, line.
     *
     * @param Document $document The SVG document
     *
     * @return array{minX: float, minY: float, maxX: float, maxY: float}|null The bounding box or null if no bounds found
     */
    public static function getContentBounds(Document $document): ?array
    {
        $minX = PHP_FLOAT_MAX;
        $minY = PHP_FLOAT_MAX;
        $maxX = PHP_FLOAT_MIN;
        $maxY = PHP_FLOAT_MIN;

        $foundAny = false;

        $root = $document->getRootElement();
        if (null === $root) {
            return null;
        }

        $elements = $root->getChildren();
        self::calculateBoundsRecursive($elements, $minX, $minY, $maxX, $maxY, $foundAny);

        if (!$foundAny) {
            return null;
        }

        return [
            'minX' => $minX,
            'minY' => $minY,
            'maxX' => $maxX,
            'maxY' => $maxY,
        ];
    }

    /**
     * Recursively calculates bounds for elements.
     *
     * @param array<ElementInterface> $elements  Elements to process
     * @param float                   &$minX     Reference to minimum X
     * @param float                   &$minY     Reference to minimum Y
     * @param float                   &$maxX     Reference to maximum X
     * @param float                   &$maxY     Reference to maximum Y
     * @param bool                    &$foundAny Reference to flag indicating if any bounds were found
     */
    private static function calculateBoundsRecursive(
        array $elements,
        float &$minX,
        float &$minY,
        float &$maxX,
        float &$maxY,
        bool &$foundAny,
    ): void {
        foreach ($elements as $element) {
            $bounds = self::getElementBounds($element);

            if (null !== $bounds) {
                $minX = min($minX, $bounds['minX']);
                $minY = min($minY, $bounds['minY']);
                $maxX = max($maxX, $bounds['maxX']);
                $maxY = max($maxY, $bounds['maxY']);
                $foundAny = true;
            }

            // Recurse into child elements
            if ($element instanceof ContainerElementInterface) {
                self::calculateBoundsRecursive(
                    $element->getChildren(),
                    $minX,
                    $minY,
                    $maxX,
                    $maxY,
                    $foundAny
                );
            }
        }
    }

    /**
     * Gets the bounds of a single element based on its type and attributes.
     *
     * @param ElementInterface $element The element to get bounds for
     *
     * @return array{minX: float, minY: float, maxX: float, maxY: float}|null The bounds or null if not calculable
     */
    private static function getElementBounds(ElementInterface $element): ?array
    {
        return match ($element::class) {
            RectElement::class => self::getRectBounds($element),
            CircleElement::class => self::getCircleBounds($element),
            EllipseElement::class => self::getEllipseBounds($element),
            LineElement::class => self::getLineBounds($element),
            default => null,
        };
    }

    /**
     * Gets bounds for a rect element.
     *
     * @return array{minX: float, minY: float, maxX: float, maxY: float}|null
     */
    private static function getRectBounds(ElementInterface $element): ?array
    {
        $x = (float) ($element->getAttribute('x') ?? 0);
        $y = (float) ($element->getAttribute('y') ?? 0);
        $width = $element->getAttribute('width');
        $height = $element->getAttribute('height');

        if (null === $width || null === $height) {
            return null;
        }

        return [
            'minX' => $x,
            'minY' => $y,
            'maxX' => $x + (float) $width,
            'maxY' => $y + (float) $height,
        ];
    }

    /**
     * Gets bounds for a circle element.
     *
     * @return array{minX: float, minY: float, maxX: float, maxY: float}|null
     */
    private static function getCircleBounds(ElementInterface $element): ?array
    {
        $cx = (float) ($element->getAttribute('cx') ?? 0);
        $cy = (float) ($element->getAttribute('cy') ?? 0);
        $r = $element->getAttribute('r');

        if (null === $r) {
            return null;
        }

        $radius = (float) $r;

        return [
            'minX' => $cx - $radius,
            'minY' => $cy - $radius,
            'maxX' => $cx + $radius,
            'maxY' => $cy + $radius,
        ];
    }

    /**
     * Gets bounds for an ellipse element.
     *
     * @return array{minX: float, minY: float, maxX: float, maxY: float}|null
     */
    private static function getEllipseBounds(ElementInterface $element): ?array
    {
        $cx = (float) ($element->getAttribute('cx') ?? 0);
        $cy = (float) ($element->getAttribute('cy') ?? 0);
        $rx = $element->getAttribute('rx');
        $ry = $element->getAttribute('ry');

        if (null === $rx || null === $ry) {
            return null;
        }

        $radiusX = (float) $rx;
        $radiusY = (float) $ry;

        return [
            'minX' => $cx - $radiusX,
            'minY' => $cy - $radiusY,
            'maxX' => $cx + $radiusX,
            'maxY' => $cy + $radiusY,
        ];
    }

    /**
     * Gets bounds for a line element.
     *
     * @return array{minX: float, minY: float, maxX: float, maxY: float}
     */
    private static function getLineBounds(ElementInterface $element): array
    {
        $x1 = (float) ($element->getAttribute('x1') ?? 0);
        $y1 = (float) ($element->getAttribute('y1') ?? 0);
        $x2 = (float) ($element->getAttribute('x2') ?? 0);
        $y2 = (float) ($element->getAttribute('y2') ?? 0);

        return [
            'minX' => min($x1, $x2),
            'minY' => min($y1, $y2),
            'maxX' => max($x1, $x2),
            'maxY' => max($y1, $y2),
        ];
    }

    /**
     * Fits the viewBox to the content bounds with optional padding.
     *
     * @param Document $document The SVG document
     * @param float    $padding  Optional padding around the content (default: 0)
     *
     * @return Document The modified document (for method chaining)
     */
    public static function fitViewBoxToContent(Document $document, float $padding = 0): Document
    {
        $bounds = self::getContentBounds($document);

        if (null === $bounds) {
            return $document;
        }

        $minX = $bounds['minX'] - $padding;
        $minY = $bounds['minY'] - $padding;
        $width = ($bounds['maxX'] - $bounds['minX']) + (2 * $padding);
        $height = ($bounds['maxY'] - $bounds['minY']) + (2 * $padding);

        self::setViewBox($document, $minX, $minY, $width, $height);

        return $document;
    }

    /**
     * Crops the SVG to its content bounds.
     *
     * This is an alias for fitViewBoxToContent with 0 padding and also
     * removes fixed dimensions.
     *
     * @param Document $document The SVG document
     * @param float    $padding  Optional padding around the content (default: 0)
     *
     * @return Document The modified document (for method chaining)
     */
    public static function cropToContent(Document $document, float $padding = 0): Document
    {
        self::fitViewBoxToContent($document, $padding);

        // Also remove fixed dimensions to make it truly cropped
        $root = $document->getRootElement();
        if (null !== $root) {
            $root->removeAttribute('width');
            $root->removeAttribute('height');
        }

        return $document;
    }

    /**
     * Sets the aspect ratio of the viewBox.
     *
     * This adjusts the viewBox dimensions to match the desired aspect ratio
     * while maintaining the current viewBox position.
     *
     * @param Document $document The SVG document
     * @param float    $ratio    The desired aspect ratio (width/height, e.g., 16/9)
     *
     * @return Document The modified document (for method chaining)
     */
    public static function setAspectRatio(Document $document, float $ratio): Document
    {
        $viewBox = self::getViewBox($document);

        if (null === $viewBox) {
            // If no viewBox, create a default one with the desired ratio
            self::setViewBox($document, 0, 0, $ratio * 100, 100);

            return $document;
        }

        [$minX, $minY, $width, $height] = $viewBox;

        $currentRatio = $width / $height;

        if ($ratio > $currentRatio) {
            // Need to increase width
            $newWidth = $height * $ratio;
            self::setViewBox($document, $minX, $minY, $newWidth, $height);
        } else {
            // Need to increase height
            $newHeight = $width / $ratio;
            self::setViewBox($document, $minX, $minY, $width, $newHeight);
        }

        return $document;
    }
}
