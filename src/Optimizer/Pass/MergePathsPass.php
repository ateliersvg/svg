<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\PathElement;

/**
 * Optimization pass that merges consecutive path elements with identical styling.
 *
 * This pass:
 * - Merges consecutive <path> elements that have the same styling attributes
 * - Reduces DOM size by combining path data
 * - Reduces attribute duplication
 * - Enables better compression and smaller file size
 *
 * Paths are only merged when they:
 * - Are adjacent siblings in the same container
 * - Have identical fill, stroke, and other styling attributes
 * - Have no ID attribute (can't merge referenced elements)
 * - Have no transform differences (or both have the same transform)
 */
final readonly class MergePathsPass implements OptimizerPassInterface
{
    /**
     * Creates a new MergePathsPass.
     *
     * @param bool $ignoreClass Whether to merge paths even with different class attributes (default: false)
     */
    public function __construct(
        private bool $ignoreClass = false,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'merge-paths';
    }

    /**
     * Optimizes the document by merging consecutive paths with identical styling.
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
        $this->mergePathsInElement($rootElement);
    }

    /**
     * Recursively merges paths in an element and its children.
     *
     * @param ContainerElementInterface $element The element to process
     */
    private function mergePathsInElement(ContainerElementInterface $element): void
    {
        // First, recursively process children if they are containers
        $children = $element->getChildren();
        foreach ($children as $child) {
            if ($child instanceof ContainerElementInterface) {
                $this->mergePathsInElement($child);
            }
        }

        // Now merge consecutive paths in this element
        $this->mergeConsecutivePaths($element);
    }

    /**
     * Merges consecutive path elements in a container.
     *
     * @param ContainerElementInterface $element The container element
     */
    private function mergeConsecutivePaths(ContainerElementInterface $element): void
    {
        $children = $element->getChildren();
        $childCount = count($children);

        if ($childCount < 2) {
            return; // Need at least 2 children to merge
        }

        $i = 0;
        while ($i < $childCount) {
            $currentChild = $children[$i];

            // Skip if not a path element
            if (!$currentChild instanceof PathElement) {
                ++$i;
                continue;
            }

            // Find consecutive paths that can be merged with this one
            $pathsToMerge = [$currentChild];
            $j = $i + 1;

            while ($j < $childCount) {
                $nextChild = $children[$j];

                // Stop if not a path element
                if (!$nextChild instanceof PathElement) {
                    break;
                }

                // Check if this path can be merged with the first one
                if ($this->canMergePaths($currentChild, $nextChild)) {
                    $pathsToMerge[] = $nextChild;
                    ++$j;
                } else {
                    break;
                }
            }

            // If we found paths to merge, merge them
            if (count($pathsToMerge) > 1) {
                $this->mergePaths($element, $pathsToMerge);

                // Re-get children as the array has changed
                $children = $element->getChildren();
                $childCount = count($children);

                // Don't increment i, as we may have more paths to merge at this position
                continue;
            }

            ++$i;
        }
    }

    /**
     * Checks if two path elements can be merged.
     *
     * @param PathElement $path1 The first path
     * @param PathElement $path2 The second path
     *
     * @return bool True if the paths can be merged, false otherwise
     */
    private function canMergePaths(PathElement $path1, PathElement $path2): bool
    {
        // Can't merge if either has ID (referenced elements)
        if ($path1->hasAttribute('id') || $path2->hasAttribute('id')) {
            return false;
        }

        // Can't merge if either has event handlers
        $eventAttributes = ['onclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove', 'onmouseout'];
        foreach ($eventAttributes as $attr) {
            if ($path1->hasAttribute($attr) || $path2->hasAttribute($attr)) {
                return false;
            }
        }

        // Can't merge if either has animations
        // (This would be checked by looking for child elements, but PathElement can't have children)

        // Check styling attributes that must match
        $compareAttrs = [
            'fill',
            'stroke',
            'stroke-width',
            'stroke-linecap',
            'stroke-linejoin',
            'stroke-dasharray',
            'stroke-dashoffset',
            'stroke-miterlimit',
            'opacity',
            'fill-opacity',
            'stroke-opacity',
            'fill-rule',
            'visibility',
            'display',
            'transform',
            'style',
        ];

        // Add class to comparison unless ignoreClass is true
        if (!$this->ignoreClass) {
            $compareAttrs[] = 'class';
        }

        foreach ($compareAttrs as $attr) {
            $val1 = $path1->getAttribute($attr);
            $val2 = $path2->getAttribute($attr);

            // Both null is OK, otherwise they must match
            if ($val1 !== $val2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Merges multiple path elements into the first one.
     *
     * @param ContainerElementInterface $container The container element
     * @param array<PathElement>        $paths     The paths to merge (must have at least 2)
     */
    private function mergePaths(ContainerElementInterface $container, array $paths): void
    {
        assert(count($paths) >= 2);

        // Merge all path data into the first path
        $firstPath = $paths[0];
        $mergedPathData = [];

        foreach ($paths as $path) {
            $pathData = $path->getPathData();
            if (null !== $pathData && '' !== $pathData) {
                $mergedPathData[] = trim($pathData);
            }
        }

        // Set the merged path data
        $firstPath->setPathData(implode(' ', $mergedPathData));

        // Remove all other paths
        for ($i = 1; $i < count($paths); ++$i) {
            $container->removeChild($paths[$i]);
        }
    }
}
