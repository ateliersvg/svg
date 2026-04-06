<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes useless stroke and fill attributes.
 *
 * This pass removes redundant stroke and fill attributes:
 * - Remove stroke="none" if fill exists (element is visible anyway)
 * - Remove stroke attributes if stroke-width="0"
 * - Remove fill on elements where fill doesn't apply (line, etc.)
 */
final class RemoveUselessStrokeAndFillPass extends AbstractOptimizerPass
{
    /**
     * Elements that don't use fill.
     */
    private const array NO_FILL_ELEMENTS = [
        'line',
        'polyline',
    ];

    public function getName(): string
    {
        return 'remove-useless-stroke-and-fill';
    }

    /**
     * Processes an element to remove useless stroke/fill.
     */
    protected function processElement(ElementInterface $element): void
    {
        $tagName = $element->getTagName();

        // Remove stroke attributes if stroke-width is 0
        if ($this->hasStrokeWidth($element) && $this->isStrokeWidthZero($element)) {
            $this->removeStrokeAttributes($element);
        }

        // Remove fill on elements that don't support it
        if (in_array($tagName, self::NO_FILL_ELEMENTS, true)) {
            if ($element->hasAttribute('fill')) {
                $element->removeAttribute('fill');
            }
            if ($element->hasAttribute('fill-opacity')) {
                $element->removeAttribute('fill-opacity');
            }
        }
    }

    /**
     * Checks if element has stroke-width attribute.
     */
    private function hasStrokeWidth(ElementInterface $element): bool
    {
        return $element->hasAttribute('stroke-width');
    }

    /**
     * Checks if stroke-width is zero.
     */
    private function isStrokeWidthZero(ElementInterface $element): bool
    {
        $strokeWidth = $element->getAttribute('stroke-width');
        assert(null !== $strokeWidth);

        $value = trim($strokeWidth);

        // Remove units and convert to float
        $numericValue = (float) preg_replace('/[^0-9.-]/', '', $value);

        return 0.0 === $numericValue;
    }

    /**
     * Removes all stroke-related attributes.
     */
    private function removeStrokeAttributes(ElementInterface $element): void
    {
        $strokeAttrs = [
            'stroke',
            'stroke-width',
            'stroke-linecap',
            'stroke-linejoin',
            'stroke-miterlimit',
            'stroke-dasharray',
            'stroke-dashoffset',
            'stroke-opacity',
        ];

        foreach ($strokeAttrs as $attr) {
            if ($element->hasAttribute($attr)) {
                $element->removeAttribute($attr);
            }
        }
    }
}
