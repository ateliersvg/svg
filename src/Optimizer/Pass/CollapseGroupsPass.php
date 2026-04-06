<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Structural\GroupElement;

/**
 * Optimization pass that collapses unnecessary group elements.
 *
 * This pass optimizes the document structure by:
 * - Removing empty <g> elements
 * - Collapsing groups with only one child
 * - Merging group attributes with child when possible
 */
final class CollapseGroupsPass implements OptimizerPassInterface
{
    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'collapse-groups';
    }

    /**
     * Optimizes the document by collapsing unnecessary groups.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Process the root element if it's a container
        $this->collapseGroupsInElement($rootElement);
    }

    /**
     * Recursively collapses groups in an element and its children.
     *
     * @param ContainerElementInterface $element The element to process
     */
    private function collapseGroupsInElement(ContainerElementInterface $element): void
    {
        $children = $element->getChildren();
        $childrenToRemove = [];
        $childrenToAdd = [];

        foreach ($children as $child) {
            // First, recursively process children if they are containers
            if ($child instanceof ContainerElementInterface) {
                $this->collapseGroupsInElement($child);
            }

            // Check if this child is a group that should be collapsed
            if ($child instanceof GroupElement) {
                if (!$child->hasChildren()) {
                    // Remove empty groups
                    $childrenToRemove[] = $child;
                } elseif (1 === $child->getChildCount()) {
                    // Collapse groups with only one child
                    $singleChild = $child->getChildren()[0];

                    // Merge group attributes into the child if possible
                    $this->mergeAttributes($child, $singleChild);

                    // Mark group for removal and child for addition
                    $childrenToRemove[] = $child;
                    $childrenToAdd[] = $singleChild;
                }
            }
        }

        // Remove collapsed groups
        foreach ($childrenToRemove as $childToRemove) {
            $element->removeChild($childToRemove);
        }

        // Add unwrapped children
        foreach ($childrenToAdd as $childToAdd) {
            $element->appendChild($childToAdd);
        }
    }

    /**
     * Merges attributes from a group into its child element.
     *
     * This method attempts to merge attributes from a parent group into its child.
     * Some attributes like transform need special handling to be properly combined.
     *
     * @param GroupElement     $group The group element
     * @param ElementInterface $child The child element
     */
    private function mergeAttributes(GroupElement $group, ElementInterface $child): void
    {
        $groupAttributes = $group->getAttributes();

        foreach ($groupAttributes as $name => $value) {
            // Special handling for transform attribute
            if ('transform' === $name) {
                $childTransform = $child->getAttribute('transform');

                if (null !== $childTransform) {
                    // Combine transforms: group transform is applied first, then child transform
                    $child->setAttribute('transform', $value.' '.$childTransform);
                } else {
                    $child->setAttribute('transform', $value);
                }
            } else {
                // For other attributes, only set if child doesn't have it
                if (!$child->hasAttribute($name)) {
                    $child->setAttribute($name, $value);
                }
            }
        }
    }
}
