<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes duplicate gradient and pattern definitions.
 *
 * This pass finds definitions in <defs> elements that are identical (same tag name,
 * same attributes except id, same children) and removes duplicates, updating all
 * references to point to the original definition.
 *
 * Benefits:
 * - Smaller file size (no duplicate definitions)
 * - Cleaner DOM structure
 * - Better maintainability
 */
final class RemoveDuplicateDefsPass implements OptimizerPassInterface
{
    /** @var array<string, ElementInterface> Map of content hash to original element */
    private array $defsMap = [];

    /** @var array<string, string> Map of duplicate ID to original ID */
    private array $idMapping = [];

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-duplicate-defs';
    }

    /**
     * Optimizes the document by removing duplicate definitions.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Reset state
        $this->defsMap = [];
        $this->idMapping = [];

        // First pass: find all defs and build duplicate map
        $this->findDuplicateDefs($rootElement);

        // Second pass: update all references
        if (!empty($this->idMapping)) {
            $this->updateReferences($rootElement);

            // Third pass: remove duplicates
            $this->removeDuplicates($rootElement, $document);
        }
    }

    /**
     * Recursively finds duplicate definitions in <defs> elements.
     *
     * @param ElementInterface $element The element to process
     */
    private function findDuplicateDefs(ElementInterface $element): void
    {
        // If this is a <defs> element, check its children
        if ($element instanceof ContainerElementInterface && 'defs' === $element->getTagName()) {
            foreach ($element->getChildren() as $child) {
                if ($child->hasAttribute('id')) {
                    $this->processDef($child);
                }
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->findDuplicateDefs($child);
            }
        }
    }

    /**
     * Processes a definition element to check for duplicates.
     *
     * @param ElementInterface $def The definition element
     */
    private function processDef(ElementInterface $def): void
    {
        $id = $def->getAttribute('id');
        assert(null !== $id);

        // Create a content hash for this definition
        $hash = $this->hashElement($def);

        // Check if we've seen this content before
        if (isset($this->defsMap[$hash])) {
            // This is a duplicate
            $originalDef = $this->defsMap[$hash];
            $originalId = $originalDef->getAttribute('id');

            if (null !== $originalId) {
                // Map this duplicate ID to the original ID
                $this->idMapping[$id] = $originalId;
            }
        } else {
            // This is the first occurrence
            $this->defsMap[$hash] = $def;
        }
    }

    /**
     * Creates a hash representing the content of an element.
     *
     * The hash includes the tag name, attributes (except id), and children.
     *
     * @param ElementInterface $element The element to hash
     *
     * @return string The content hash
     */
    private function hashElement(ElementInterface $element): string
    {
        $parts = [];

        // Add tag name
        $parts[] = $element->getTagName();

        // Add attributes (sorted and excluding id)
        $attributes = $element->getAttributes();
        unset($attributes['id']);
        ksort($attributes);

        foreach ($attributes as $name => $value) {
            $parts[] = $name.'='.$value;
        }

        // Add children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $parts[] = $this->hashElement($child);
            }
        }

        return md5(implode('|', $parts));
    }

    /**
     * Recursively updates all references to use original IDs.
     *
     * @param ElementInterface $element The element to process
     */
    private function updateReferences(ElementInterface $element): void
    {
        // Update all attributes that might contain ID references
        foreach ($element->getAttributes() as $name => $value) {
            $newValue = $this->updateReferencesInValue($value);
            if ($newValue !== $value) {
                $element->setAttribute($name, $newValue);
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->updateReferences($child);
            }
        }
    }

    /**
     * Updates ID references in an attribute value.
     *
     * @param string $value The attribute value
     *
     * @return string The updated value
     */
    private function updateReferencesInValue(string $value): string
    {
        // Update href-style references: #id
        $value = (string) preg_replace_callback(
            '/^#(.+)$/',
            function ($matches) {
                $id = $matches[1];

                return '#'.($this->idMapping[$id] ?? $id);
            },
            $value
        );

        // Update url()-style references: url(#id)
        $value = (string) preg_replace_callback(
            '/url\(#([^)]+)\)/',
            function ($matches) {
                $id = $matches[1];

                return 'url(#'.($this->idMapping[$id] ?? $id).')';
            },
            $value
        );

        return $value;
    }

    /**
     * Recursively removes duplicate definitions.
     *
     * @param ElementInterface $element  The element to process
     * @param Document         $document The document being optimized
     */
    private function removeDuplicates(ElementInterface $element, Document $document): void
    {
        // If this is a <defs> element, remove duplicate children
        if ($element instanceof ContainerElementInterface && 'defs' === $element->getTagName()) {
            $children = $element->getChildren();

            foreach ($children as $child) {
                if ($child->hasAttribute('id')) {
                    $id = $child->getAttribute('id');

                    if (null !== $id && isset($this->idMapping[$id])) {
                        // This is a duplicate, remove it
                        $element->removeChild($child);
                        $document->unregisterElementId($id);
                    }
                }
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->removeDuplicates($child, $document);
            }
        }
    }
}
