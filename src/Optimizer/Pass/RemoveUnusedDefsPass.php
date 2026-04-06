<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes unused definitions from <defs> elements.
 *
 * This pass finds all elements with IDs inside <defs> and removes those that
 * are never referenced elsewhere in the document. It handles various reference
 * types including:
 * - href/xlink:href attributes (for <use>, <image>, etc.)
 * - url(#id) in attributes (for fill, stroke, clip-path, mask, filter, etc.)
 * - url(#id) in style attributes and <style> elements
 */
final class RemoveUnusedDefsPass implements OptimizerPassInterface
{
    /** @var array<string, bool> IDs that are referenced in the document */
    private array $referencedIds = [];

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-unused-defs';
    }

    /**
     * Optimizes the document by removing unused definitions.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Reset state for this optimization run
        $this->referencedIds = [];

        // First pass: collect all referenced IDs
        $this->collectReferencedIds($rootElement);

        // Second pass: remove unused definitions
        $this->removeUnusedDefs($rootElement, $document);
    }

    /**
     * Recursively collects all referenced IDs from elements and their attributes.
     *
     * @param ElementInterface $element The element to process
     */
    private function collectReferencedIds(ElementInterface $element): void
    {
        // Check all attributes for ID references
        foreach ($element->getAttributes() as $name => $value) {
            $this->extractIdsFromAttributeValue($value);
        }

        // Recurse to children if this is a container
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->collectReferencedIds($child);
            }
        }
    }

    /**
     * Extracts referenced IDs from an attribute value.
     *
     * Handles various reference formats:
     * - #id (for href, xlink:href)
     * - url(#id) (for fill, stroke, clip-path, mask, filter, etc.)
     * - Multiple url(#id) references in a single value
     *
     * @param string $value The attribute value to process
     */
    private function extractIdsFromAttributeValue(string $value): void
    {
        // Match href-style references: #id
        if (preg_match('/^#(.+)$/', $value, $matches)) {
            $this->referencedIds[$matches[1]] = true;
        }

        // Match url()-style references: url(#id)
        if (preg_match_all('/url\(#([^)]+)\)/', $value, $matches)) {
            foreach ($matches[1] as $id) {
                $this->referencedIds[$id] = true;
            }
        }
    }

    /**
     * Recursively removes unused definitions from <defs> elements.
     *
     * @param ElementInterface $element  The element to process
     * @param Document         $document The document being optimized
     */
    private function removeUnusedDefs(ElementInterface $element, Document $document): void
    {
        // If this is a <defs> element, check its children
        if ($element instanceof ContainerElementInterface && 'defs' === $element->getTagName()) {
            $children = $element->getChildren();

            foreach ($children as $child) {
                // If the child has an ID and it's not referenced, remove it
                if ($child->hasAttribute('id')) {
                    $id = $child->getAttribute('id');
                    if (null !== $id && !isset($this->referencedIds[$id])) {
                        $element->removeChild($child);
                        // Unregister from document if it was registered
                        $document->unregisterElementId($id);
                    }
                }
            }
        }

        // Recurse to children if this is a container
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->removeUnusedDefs($child, $document);
            }
        }
    }
}
