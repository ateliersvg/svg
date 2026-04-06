<?php

declare(strict_types=1);

namespace Atelier\Svg\Validation;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Tracks and analyzes references between SVG elements.
 *
 * This class builds a comprehensive map of:
 * - All elements with IDs
 * - All references to IDs (href, url(), etc.)
 * - Dependency graph between elements
 * - Circular reference detection
 *
 * Example usage:
 * ```php
 * $tracker = new ReferenceTracker($document);
 * $broken = $tracker->findBrokenReferences();
 * $circular = $tracker->findCircularReferences();
 * $deps = $tracker->getDependencies('gradient1');
 * ```
 */
final class ReferenceTracker
{
    /** @var array<string, ElementInterface> Map of ID to element */
    private array $elementsById = [];

    /** @var array<string, array<ReferenceInfo>> Map of referenced ID to list of referencing elements */
    private array $referenceMap = [];

    /** @var array<string, array<string>> Dependency graph: ID => [dependent IDs] */
    private array $dependencyGraph = [];

    /** @var array<string, int> Map of ID to occurrence count for duplicate detection */
    private array $idCounts = [];

    /**
     * Attributes that may contain references to IDs.
     */
    private const array REFERENCE_ATTRIBUTES = [
        'href',
        'xlink:href',
        'fill',
        'stroke',
        'filter',
        'clip-path',
        'mask',
        'marker-start',
        'marker-mid',
        'marker-end',
        'style',
    ];

    public function __construct(private readonly Document $document)
    {
        $this->build();
    }

    /**
     * Builds the reference tracking data structures.
     */
    private function build(): void
    {
        $root = $this->document->getRootElement();
        if (null === $root) {
            return;
        }

        // First pass: collect all elements with IDs
        $this->collectIds($root);

        // Second pass: collect all references
        $this->collectReferences($root);

        // Build dependency graph
        $this->buildDependencyGraph();
    }

    /**
     * Recursively collects all elements with IDs.
     */
    private function collectIds(ElementInterface $element): void
    {
        $id = $element->getId();
        if (null !== $id) {
            // Track ID count for duplicate detection
            $this->idCounts[$id] = ($this->idCounts[$id] ?? 0) + 1;

            // Store first occurrence (or overwrite for duplicates)
            if (!isset($this->elementsById[$id])) {
                $this->elementsById[$id] = $element;
            }
        }

        // Process children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->collectIds($child);
            }
        }
    }

    /**
     * Recursively collects all references to IDs.
     */
    private function collectReferences(ElementInterface $element): void
    {
        foreach (self::REFERENCE_ATTRIBUTES as $attr) {
            $value = $element->getAttribute($attr);
            if (null === $value) {
                continue;
            }

            $refs = $this->extractReferences($value);
            foreach ($refs as $refId) {
                if (!isset($this->referenceMap[$refId])) {
                    $this->referenceMap[$refId] = [];
                }

                $this->referenceMap[$refId][] = new ReferenceInfo(
                    element: $element,
                    attribute: $attr,
                    referencedId: $refId,
                    value: $value
                );
            }
        }

        // Process children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->collectReferences($child);
            }
        }
    }

    /**
     * Extracts all ID references from an attribute value.
     *
     * @return array<string> List of referenced IDs
     */
    private function extractReferences(string $value): array
    {
        $refs = [];

        // Match url(#id) pattern
        if (preg_match_all('/url\(#([^)]+)\)/', $value, $matches)) {
            $refs = array_merge($refs, $matches[1]);
        }

        // Match #id pattern (for href attributes)
        if (str_starts_with($value, '#')) {
            $refs[] = substr($value, 1);
        }

        return array_unique($refs);
    }

    /**
     * Builds the dependency graph from the reference map.
     */
    private function buildDependencyGraph(): void
    {
        foreach ($this->referenceMap as $referencedId => $references) {
            if (!isset($this->dependencyGraph[$referencedId])) {
                $this->dependencyGraph[$referencedId] = [];
            }

            foreach ($references as $ref) {
                $refElementId = $ref->element->getId();
                if (null !== $refElementId) {
                    $this->dependencyGraph[$referencedId][] = $refElementId;
                }
            }
        }
    }

    /**
     * Finds all broken references (references to non-existent IDs).
     *
     * @return array<BrokenReference> List of broken references
     */
    public function findBrokenReferences(): array
    {
        $broken = [];

        foreach ($this->referenceMap as $refId => $references) {
            if (!isset($this->elementsById[$refId])) {
                foreach ($references as $ref) {
                    $broken[] = new BrokenReference(
                        referencedId: $refId,
                        referencingElement: $ref->element,
                        attribute: $ref->attribute,
                        value: $ref->value
                    );
                }
            }
        }

        return $broken;
    }

    /**
     * Finds all circular references in the dependency graph.
     *
     * @return array<array<string>> List of circular dependency chains
     */
    public function findCircularReferences(): array
    {
        $cycles = [];
        $visited = [];
        $recursionStack = [];

        foreach (array_keys($this->dependencyGraph) as $id) {
            if (!isset($visited[$id])) {
                $this->detectCycle($id, $visited, $recursionStack, [], $cycles);
            }
        }

        return $cycles;
    }

    /**
     * Detects cycles using depth-first search.
     *
     * @param array<string, bool>  $visited
     * @param array<string, bool>  $recursionStack
     * @param array<string>        $path
     * @param array<array<string>> $cycles
     */
    private function detectCycle(
        string $id,
        array &$visited,
        array &$recursionStack,
        array $path,
        array &$cycles,
    ): void {
        $visited[$id] = true;
        $recursionStack[$id] = true;
        $path[] = $id;

        if (isset($this->dependencyGraph[$id])) {
            foreach ($this->dependencyGraph[$id] as $dependentId) {
                if (!isset($visited[$dependentId])) {
                    $this->detectCycle($dependentId, $visited, $recursionStack, $path, $cycles);
                } elseif (isset($recursionStack[$dependentId]) && $recursionStack[$dependentId]) {
                    // Found a cycle - extract the cycle from path
                    $cycleStart = array_search($dependentId, $path, true);
                    if (false !== $cycleStart) {
                        $cycle = array_slice($path, (int) $cycleStart);
                        $cycle[] = $dependentId; // Close the cycle
                        $cycles[] = $cycle;
                    }
                }
            }
        }

        $recursionStack[$id] = false;
    }

    /**
     * Gets all dependencies of a given ID (elements that reference it).
     *
     * @return array<string> List of dependent IDs
     */
    public function getDependencies(string $id): array
    {
        return $this->dependencyGraph[$id] ?? [];
    }

    /**
     * Gets all elements that a given ID depends on (elements it references).
     *
     * @return array<string> List of dependency IDs
     */
    public function getDependsOn(string $id): array
    {
        $element = $this->elementsById[$id] ?? null;
        if (null === $element) {
            return [];
        }

        $dependsOn = [];

        foreach (self::REFERENCE_ATTRIBUTES as $attr) {
            $value = $element->getAttribute($attr);
            if (null === $value) {
                continue;
            }

            $refs = $this->extractReferences($value);
            $dependsOn = array_merge($dependsOn, $refs);
        }

        return array_unique($dependsOn);
    }

    /**
     * Gets all references to a specific ID.
     *
     * @return array<ReferenceInfo>
     */
    public function getReferencesTo(string $id): array
    {
        return $this->referenceMap[$id] ?? [];
    }

    /**
     * Checks if an ID is referenced by any element.
     */
    public function isReferenced(string $id): bool
    {
        return isset($this->referenceMap[$id]) && !empty($this->referenceMap[$id]);
    }

    /**
     * Gets all duplicate IDs in the document.
     *
     * @return array<string, int> Map of duplicate ID to occurrence count
     */
    public function getDuplicateIds(): array
    {
        return array_filter($this->idCounts, fn (int $count): bool => $count > 1);
    }

    /**
     * Gets all IDs in the document.
     *
     * @return array<string>
     */
    public function getAllIds(): array
    {
        return array_keys($this->elementsById);
    }

    /**
     * Gets an element by its ID.
     */
    public function getElementById(string $id): ?ElementInterface
    {
        return $this->elementsById[$id] ?? null;
    }

    /**
     * Gets all unreferenced IDs (IDs that exist but are never referenced).
     *
     * @return array<string>
     */
    public function getUnreferencedIds(): array
    {
        $allIds = array_keys($this->elementsById);
        $referencedIds = array_keys($this->referenceMap);

        return array_diff($allIds, $referencedIds);
    }

    /**
     * Gets the full dependency graph.
     *
     * @return array<string, array<string>>
     */
    public function getDependencyGraph(): array
    {
        return $this->dependencyGraph;
    }
}
