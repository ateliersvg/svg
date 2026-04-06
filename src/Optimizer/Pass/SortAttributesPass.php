<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that sorts element attributes alphabetically.
 *
 * This pass reorders attributes alphabetically, which can improve gzip/brotli
 * compression by grouping similar attribute patterns together. It also produces
 * consistent output that's easier to diff.
 *
 * Priority attributes (like id and class) can be kept at the beginning of the
 * attribute list for readability.
 *
 * Benefits:
 * - Better gzip/brotli compression
 * - Consistent output (easier to diff)
 * - Cleaner appearance
 */
final class SortAttributesPass extends AbstractOptimizerPass
{
    /**
     * Creates a new SortAttributesPass.
     *
     * @param array<string> $priorityOrder Attributes that should appear first (in order)
     */
    public function __construct(
        private readonly array $priorityOrder = ['id', 'class'],
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'sort-attributes';
    }

    /**
     * Processes an element to sort its attributes.
     *
     * @param ElementInterface $element The element to process
     */
    protected function processElement(ElementInterface $element): void
    {
        // Sort attributes on this element
        $this->sortAttributes($element);
    }

    /**
     * Sorts the attributes of an element.
     *
     * @param ElementInterface $element The element whose attributes to sort
     */
    private function sortAttributes(ElementInterface $element): void
    {
        $attributes = $element->getAttributes();

        if (count($attributes) <= 1) {
            // No need to sort 0 or 1 attributes
            return;
        }

        // Separate priority and regular attributes
        $priorityAttrs = [];
        $regularAttrs = [];

        foreach ($attributes as $name => $value) {
            if (in_array($name, $this->priorityOrder, true)) {
                $priorityAttrs[$name] = $value;
            } else {
                $regularAttrs[$name] = $value;
            }
        }

        // Sort regular attributes alphabetically
        ksort($regularAttrs, SORT_STRING);

        // Build final sorted attribute list
        $sortedAttributes = [];

        // Add priority attributes in specified order
        foreach ($this->priorityOrder as $priorityName) {
            if (isset($priorityAttrs[$priorityName])) {
                $sortedAttributes[$priorityName] = $priorityAttrs[$priorityName];
            }
        }

        // Add sorted regular attributes
        foreach ($regularAttrs as $name => $value) {
            $sortedAttributes[$name] = $value;
        }

        // Check if the order actually changed
        if (array_keys($sortedAttributes) === array_keys($attributes)) {
            // Already in correct order
            return;
        }

        // Remove all attributes
        foreach ($attributes as $name => $value) {
            $element->removeAttribute($name);
        }

        // Re-add in sorted order
        foreach ($sortedAttributes as $name => $value) {
            $element->setAttribute($name, $value);
        }
    }
}
