<?php

declare(strict_types=1);

namespace Atelier\Svg;

use Atelier\Svg\Document\MergeStrategy;
use Atelier\Svg\Element\Accessibility\Accessibility;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementCollection;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Selector\SelectorMatcher;
use Atelier\Svg\Visitor\QueryVisitor;
use Atelier\Svg\Visitor\Traverser;

/**
 * Represents an SVG document.
 *
 * The Document class is the main entry point for creating and manipulating SVG documents.
 * It holds the root SVG element and provides convenience methods for document-level operations.
 */
final class Document implements \Stringable
{
    private ?SvgElement $rootElement = null;

    /** @var array<string, ElementInterface> */
    private array $elementsById = [];

    private bool $omitXmlDeclaration = false;

    public function __construct(?SvgElement $rootElement = null)
    {
        if (null !== $rootElement) {
            $this->setRootElement($rootElement);
        }
    }

    /**
     * Creates a new SVG document with default root element.
     */
    public static function create(float $width = 300, float $height = 150): self
    {
        $svg = new SvgElement();
        $svg->setAttribute('width', $width);
        $svg->setAttribute('height', $height);
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');

        return new self($svg);
    }

    /**
     * Gets the root SVG element.
     */
    public function getRootElement(): ?SvgElement
    {
        return $this->rootElement;
    }

    /**
     * Sets the root SVG element.
     */
    public function setRootElement(SvgElement $rootElement): self
    {
        $this->rootElement = $rootElement;
        $this->indexElement($rootElement);

        return $this;
    }

    public function getOmitXmlDeclaration(): bool
    {
        return $this->omitXmlDeclaration;
    }

    public function setOmitXmlDeclaration(bool $omit): self
    {
        $this->omitXmlDeclaration = $omit;

        return $this;
    }

    /**
     * Finds an element by its ID attribute.
     */
    public function getElementById(string $id): ?ElementInterface
    {
        return $this->elementsById[$id] ?? null;
    }

    /**
     * Registers an element with an ID for fast lookup.
     *
     * @throws InvalidArgumentException If an element with the given ID is already registered
     */
    public function registerElementId(string $id, ElementInterface $element): self
    {
        if (isset($this->elementsById[$id])) {
            throw new InvalidArgumentException(sprintf("An element with ID '%s' is already registered", $id));
        }

        $this->elementsById[$id] = $element;

        return $this;
    }

    /**
     * Unregisters an element ID.
     */
    public function unregisterElementId(string $id): self
    {
        unset($this->elementsById[$id]);

        return $this;
    }

    /**
     * Recursively indexes all elements with IDs from the given element.
     */
    private function indexElement(ElementInterface $element): void
    {
        if ($element->hasAttribute('id')) {
            $id = $element->getAttribute('id');
            if (null !== $id && '' !== $id) {
                try {
                    $this->registerElementId($id, $element);
                } catch (InvalidArgumentException) {
                    // Silently ignore duplicate IDs during initial indexing to allow
                    // parsing of malformed SVGs. Only the first element with a given ID
                    // will be indexed. Use hasDuplicateIds() to detect this condition.
                    // Uncomment the following line to log duplicate IDs during development:
                    // error_log("Duplicate ID found during indexing: {$id}");
                }
            }
        }

        // Index children if this is a container
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->indexElement($child);
            }
        }
    }

    /**
     * Converts the document to an SVG string.
     */
    public function toString(): string
    {
        if (null === $this->rootElement) {
            return '';
        }

        // This is a basic implementation
        // A full implementation would use the Dumper classes
        return sprintf('<%s/>', $this->rootElement->getTagName());
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Generates a unique ID that doesn't conflict with existing IDs in the document.
     *
     * @param string $prefix The prefix for the generated ID
     *
     * @return string The generated unique ID
     */
    public function generateUniqueId(string $prefix = 'el'): string
    {
        $counter = 1;
        $id = $prefix.'-'.$counter;

        while (isset($this->elementsById[$id])) {
            ++$counter;
            $id = $prefix.'-'.$counter;
        }

        return $id;
    }

    /**
     * Checks if the document has any duplicate IDs.
     *
     * @return bool True if duplicate IDs exist, false otherwise
     */
    public function hasDuplicateIds(): bool
    {
        return !empty($this->getDuplicateIds());
    }

    /**
     * Gets a list of duplicate IDs and their occurrence counts.
     *
     * @return array<string, int> Array mapping ID to occurrence count
     */
    public function getDuplicateIds(): array
    {
        $idCounts = [];

        // Count all IDs in the document
        $this->countIds($this->rootElement, $idCounts);

        // Filter to only duplicates
        return array_filter($idCounts, fn ($count) => $count > 1);
    }

    /**
     * Prefixes all IDs in the document with the given prefix.
     * Also updates all references (href, url(), etc.) to use the new IDs.
     *
     * @param string $prefix The prefix to add to all IDs
     */
    public function prefixAllIds(string $prefix): self
    {
        if (null === $this->rootElement) {
            return $this;
        }

        // Collect all current IDs
        $oldIds = array_keys($this->elementsById);

        // Create mapping of old IDs to new IDs
        $idMap = [];
        foreach ($oldIds as $oldId) {
            $idMap[$oldId] = $prefix.$oldId;
        }

        // Update all IDs and references
        $root = $this->rootElement;
        $this->updateIdsAndReferences($root, $idMap);

        // Rebuild ID registry
        $this->elementsById = [];
        $this->indexElement($root);

        return $this;
    }

    /**
     * Recursively counts IDs in the element tree.
     *
     * @param array<string, int> $idCounts
     */
    private function countIds(?ElementInterface $element, array &$idCounts): void
    {
        if (null === $element) {
            return;
        }

        if ($element->hasAttribute('id')) {
            $id = $element->getAttribute('id');
            if (null !== $id && '' !== $id) {
                $idCounts[$id] = ($idCounts[$id] ?? 0) + 1;
            }
        }

        // Recursively count children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->countIds($child, $idCounts);
            }
        }
    }

    /**
     * Recursively updates IDs and references in the element tree.
     *
     * @param array<string, string> $idMap
     */
    private function updateIdsAndReferences(ElementInterface $element, array $idMap): void
    {
        // Update this element's ID if it has one
        if ($element->hasAttribute('id')) {
            $oldId = $element->getAttribute('id');
            if ($oldId && isset($idMap[$oldId])) {
                $element->setAttribute('id', $idMap[$oldId]);
            }
        }

        // Update references in href attributes
        foreach (['href', 'xlink:href'] as $attr) {
            $value = $element->getAttribute($attr);
            if ($value && str_starts_with($value, '#')) {
                $refId = substr($value, 1);
                if (isset($idMap[$refId])) {
                    $element->setAttribute($attr, '#'.$idMap[$refId]);
                }
            }
        }

        // Update url() references in various attributes
        foreach (['fill', 'stroke', 'clip-path', 'mask', 'marker-start', 'marker-mid', 'marker-end', 'filter'] as $attr) {
            $value = $element->getAttribute($attr);
            if ($value && preg_match('/url\(#([^)]+)\)/', $value, $matches)) {
                $refId = $matches[1];
                if (isset($idMap[$refId])) {
                    $newValue = str_replace('#'.$refId, '#'.$idMap[$refId], $value);
                    $element->setAttribute($attr, $newValue);
                }
            }
        }

        // Recursively update children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->updateIdsAndReferences($child, $idMap);
            }
        }
    }

    // ========================================================================
    // Query Selector Methods
    // ========================================================================

    /**
     * Finds the first element that matches the given selector.
     *
     * Supported selectors:
     * - ID: #myId
     * - Class: .myClass
     * - Tag: rect, circle, path
     * - Attribute: [attr="value"]
     * - Universal: *
     *
     * @param string $selector CSS-like selector
     *
     * @return ElementInterface|null The first matching element, or null if none found
     */
    public function querySelector(string $selector): ?ElementInterface
    {
        if (null === $this->rootElement) {
            return null;
        }

        $matcher = new SelectorMatcher();
        $visitor = new QueryVisitor($selector, $matcher, findFirst: true);
        $traverser = new Traverser($visitor);
        $traverser->traverse($this->rootElement);

        return $visitor->getFirstMatch();
    }

    /**
     * Finds all elements that match the given selector.
     *
     * Supported selectors:
     * - ID: #myId
     * - Class: .myClass
     * - Tag: rect, circle, path
     * - Attribute: [attr="value"]
     * - Universal: *
     *
     * @param string $selector CSS-like selector
     *
     * @return ElementCollection Collection of matching elements
     */
    public function querySelectorAll(string $selector): ElementCollection
    {
        if (null === $this->rootElement) {
            return new ElementCollection([]);
        }

        $matcher = new SelectorMatcher();
        $visitor = new QueryVisitor($selector, $matcher);
        $traverser = new Traverser($visitor);
        $traverser->traverse($this->rootElement);

        return new ElementCollection($visitor->getMatches());
    }

    /**
     * Convenience method: finds all elements with a specific tag name.
     *
     * @return ElementCollection Collection of matching elements
     */
    public function findByTag(string $tagName): ElementCollection
    {
        return $this->querySelectorAll($tagName);
    }

    /**
     * Convenience method: finds all elements with a specific class.
     *
     * @return ElementCollection Collection of matching elements
     */
    public function findByClass(string $className): ElementCollection
    {
        return $this->querySelectorAll('.'.$className);
    }

    /**
     * Alias for querySelectorAll for a more fluent syntax.
     *
     * @return ElementCollection Collection of matching elements
     */
    public function select(string $selector): ElementCollection
    {
        return $this->querySelectorAll($selector);
    }

    // ========================================================================
    // Document Import Methods
    // ========================================================================

    /**
     * Imports an element from another document (or the same document).
     * Creates a clone of the element to avoid modifying the source.
     *
     * @param ElementInterface                                          $element The element to import
     * @param bool                                                      $deep    If true, imports all children recursively
     * @param array{prefix_ids?: string|bool, resolve_conflicts?: bool} $options Import options
     *
     * @return ElementInterface The imported (cloned) element
     */
    public function importElement(
        ElementInterface $element,
        bool $deep = true,
        array $options = [],
    ): ElementInterface {
        // Clone the element (deep or shallow)
        if ($deep && $element instanceof ContainerElementInterface) {
            $imported = $element->cloneDeep();
        } else {
            $imported = $element->clone();
        }

        // Handle ID conflicts
        $prefixIds = $options['prefix_ids'] ?? null;
        $resolveConflicts = $options['resolve_conflicts'] ?? true;

        if (is_string($prefixIds)) {
            // Prefix all IDs in the imported element tree
            $this->prefixElementIds($imported, $prefixIds);
        } elseif (true === $prefixIds || $resolveConflicts) {
            // Auto-resolve ID conflicts
            $this->resolveIdConflicts($imported);
        }

        return $imported;
    }

    /**
     * Imports multiple elements from another document.
     *
     * @param array<ElementInterface>                                   $elements Elements to import
     * @param bool                                                      $deep     If true, imports all children recursively
     * @param array{prefix_ids?: string|bool, resolve_conflicts?: bool} $options  Import options
     *
     * @return array<ElementInterface> The imported elements
     */
    public function importElements(
        array $elements,
        bool $deep = true,
        array $options = [],
    ): array {
        return array_map(
            fn (ElementInterface $el) => $this->importElement($el, $deep, $options),
            $elements
        );
    }

    /**
     * Prefixes all IDs in an element tree with the given prefix.
     */
    private function prefixElementIds(ElementInterface $element, string $prefix): void
    {
        // Collect all IDs that need to be prefixed
        $idMap = [];
        $this->collectIdsForPrefixing($element, $prefix, $idMap);

        // Update all IDs and references
        if (!empty($idMap)) {
            $this->updateIdsAndReferences($element, $idMap);
        }
    }

    /**
     * Collects all IDs in an element tree for prefixing.
     *
     * @param array<string, string> $idMap
     */
    private function collectIdsForPrefixing(ElementInterface $element, string $prefix, array &$idMap): void
    {
        if ($element->hasAttribute('id')) {
            $oldId = $element->getAttribute('id');
            if ($oldId) {
                $idMap[$oldId] = $prefix.$oldId;
            }
        }

        // Recursively collect from children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->collectIdsForPrefixing($child, $prefix, $idMap);
            }
        }
    }

    /**
     * Resolves ID conflicts in an imported element tree.
     * Renames any IDs that conflict with existing IDs in the document.
     */
    private function resolveIdConflicts(ElementInterface $element): void
    {
        $idMap = [];
        $this->findAndResolveConflicts($element, $idMap);

        if (!empty($idMap)) {
            $this->updateIdsAndReferences($element, $idMap);
        }
    }

    /**
     * Finds and generates new IDs for any conflicts.
     *
     * @param array<string, string> $idMap
     */
    private function findAndResolveConflicts(ElementInterface $element, array &$idMap): void
    {
        if ($element->hasAttribute('id')) {
            $oldId = $element->getAttribute('id');
            if ($oldId && isset($this->elementsById[$oldId])) {
                // ID conflict found - generate a new unique ID
                $newId = $this->generateUniqueId($oldId);
                $idMap[$oldId] = $newId;
            }
        }

        // Recursively check children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->findAndResolveConflicts($child, $idMap);
            }
        }
    }

    // ========================================================================
    // Document Merge & Composition Methods
    // ========================================================================

    /**
     * Merges multiple documents into a new document.
     *
     * @param array<Document>                                                                                        $documents Documents to merge
     * @param array{strategy?: MergeStrategy, spacing?: float, prefix_ids?: bool|string, symbol_ids?: array<string>} $options   Merge options
     *
     * @return self A new merged document
     */
    public static function merge(array $documents, array $options = []): self
    {
        if (empty($documents)) {
            return new self();
        }

        $strategy = $options['strategy'] ?? MergeStrategy::APPEND;
        $spacing = isset($options['spacing']) ? (float) $options['spacing'] : 0.0;
        $prefixIds = $options['prefix_ids'] ?? false;

        return match ($strategy) {
            MergeStrategy::APPEND => self::mergeAppend($documents, $prefixIds),
            MergeStrategy::SIDE_BY_SIDE => self::mergeSideBySide($documents, $spacing, $prefixIds),
            MergeStrategy::STACKED => self::mergeStacked($documents, $spacing, $prefixIds),
            MergeStrategy::SYMBOLS => self::mergeAsSymbols($documents, $options),
            MergeStrategy::GRID => self::mergeAsGrid($documents, $options),
        };
    }

    /**
     * Merges documents by appending all children.
     *
     * @param array<Document> $documents Documents to merge
     * @param bool|string     $prefixIds Prefix for IDs (string=prefix, true=auto, false=none)
     */
    private static function mergeAppend(array $documents, bool|string $prefixIds): self
    {
        $merged = new self();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $merged->setRootElement($svg);

        foreach ($documents as $i => $doc) {
            $prefix = is_string($prefixIds) ? $prefixIds : ($prefixIds ? "doc{$i}-" : false);

            if ($doc->getRootElement()) {
                foreach ($doc->getRootElement()->getChildren() as $child) {
                    $imported = $merged->importElement($child, deep: true, options: [
                        'prefix_ids' => $prefix,
                        'resolve_conflicts' => !$prefix,
                    ]);
                    $svg->appendChild($imported);
                }
            }
        }

        return $merged;
    }

    /**
     * Merges documents side-by-side horizontally.
     *
     * @param array<Document> $documents Documents to merge
     */
    private static function mergeSideBySide(array $documents, float $spacing, bool|string $prefixIds): self
    {
        $merged = new self();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $merged->setRootElement($svg);

        $xOffset = 0;
        $maxHeight = 0;
        $totalWidth = 0;

        foreach ($documents as $i => $doc) {
            if (!$doc->getRootElement()) {
                continue;
            }

            $group = new GroupElement();
            $group->setAttribute('transform', "translate({$xOffset}, 0)");

            $prefix = is_string($prefixIds) ? $prefixIds : ($prefixIds ? "doc{$i}-" : false);

            foreach ($doc->getRootElement()->getChildren() as $child) {
                $imported = $merged->importElement($child, deep: true, options: [
                    'prefix_ids' => $prefix,
                    'resolve_conflicts' => !$prefix,
                ]);
                $group->appendChild($imported);
            }

            $svg->appendChild($group);

            // Calculate dimensions for next offset
            $width = (float) ($doc->getRootElement()->getAttribute('width') ?? 100);
            $height = (float) ($doc->getRootElement()->getAttribute('height') ?? 100);
            $totalWidth += $width;
            if ($i < count($documents) - 1) {
                $totalWidth += $spacing;
            }
            $xOffset += $width + $spacing;
            $maxHeight = max($maxHeight, $height);
        }

        $svg->setAttribute('width', (string) $totalWidth);
        $svg->setAttribute('height', (string) $maxHeight);

        return $merged;
    }

    /**
     * Merges documents stacked vertically.
     *
     * @param array<Document> $documents Documents to merge
     */
    private static function mergeStacked(array $documents, float $spacing, bool|string $prefixIds): self
    {
        $merged = new self();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $merged->setRootElement($svg);

        $yOffset = 0;
        $maxWidth = 0;
        $totalHeight = 0;

        foreach ($documents as $i => $doc) {
            if (!$doc->getRootElement()) {
                continue;
            }

            $group = new GroupElement();
            $group->setAttribute('transform', "translate(0, {$yOffset})");

            $prefix = is_string($prefixIds) ? $prefixIds : ($prefixIds ? "doc{$i}-" : false);

            foreach ($doc->getRootElement()->getChildren() as $child) {
                $imported = $merged->importElement($child, deep: true, options: [
                    'prefix_ids' => $prefix,
                    'resolve_conflicts' => !$prefix,
                ]);
                $group->appendChild($imported);
            }

            $svg->appendChild($group);

            // Calculate dimensions for next offset
            $width = (float) ($doc->getRootElement()->getAttribute('width') ?? 100);
            $height = (float) ($doc->getRootElement()->getAttribute('height') ?? 100);
            $totalHeight += $height;
            if ($i < count($documents) - 1) {
                $totalHeight += $spacing;
            }
            $yOffset += $height + $spacing;
            $maxWidth = max($maxWidth, $width);
        }

        $svg->setAttribute('width', (string) $maxWidth);
        $svg->setAttribute('height', (string) $totalHeight);

        return $merged;
    }

    /**
     * Merges documents as symbols in a sprite.
     *
     * @param array<Document>      $documents Documents to merge
     * @param array<string, mixed> $options   Merge options
     */
    private static function mergeAsSymbols(array $documents, array $options): self
    {
        $merged = new self();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $merged->setRootElement($svg);

        $defs = new DefsElement();
        /** @var array<int|string, string> $symbolIds */
        $symbolIds = isset($options['symbol_ids']) && is_array($options['symbol_ids']) ? $options['symbol_ids'] : [];

        foreach ($documents as $i => $doc) {
            if (!$doc->getRootElement()) {
                continue;
            }

            $symbol = new SymbolElement();
            $symbolId = $symbolIds[$i] ?? "symbol-{$i}";
            $symbol->setAttribute('id', (string) $symbolId);

            // Copy viewBox from source if it exists
            $viewBox = $doc->getRootElement()->getAttribute('viewBox');
            if ($viewBox) {
                $symbol->setAttribute('viewBox', $viewBox);
            }

            // Import all children into symbol
            foreach ($doc->getRootElement()->getChildren() as $child) {
                $imported = $merged->importElement($child, deep: true, options: [
                    'resolve_conflicts' => true,
                ]);
                $symbol->appendChild($imported);
            }

            $defs->appendChild($symbol);
        }

        $svg->appendChild($defs);

        return $merged;
    }

    /**
     * Merges documents in a grid layout.
     *
     * @param array<Document>      $documents Documents to merge
     * @param array<string, mixed> $options   Merge options
     */
    private static function mergeAsGrid(array $documents, array $options): self
    {
        $columns = isset($options['columns']) && is_numeric($options['columns']) ? (int) $options['columns'] : 3;
        $spacing = isset($options['spacing']) && is_numeric($options['spacing']) ? (float) $options['spacing'] : 10.0;
        $prefixIds = $options['prefix_ids'] ?? false;

        $merged = new self();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $merged->setRootElement($svg);

        $col = 0;
        $row = 0;
        $maxWidth = 0;
        $rowHeight = 0;
        $totalHeight = 0;

        foreach ($documents as $i => $doc) {
            if (!$doc->getRootElement()) {
                continue;
            }

            $width = (float) ($doc->getRootElement()->getAttribute('width') ?? 100);
            $height = (float) ($doc->getRootElement()->getAttribute('height') ?? 100);

            $x = $col * ($width + $spacing);
            $y = $row * ($rowHeight + $spacing);

            $group = new GroupElement();
            $group->setAttribute('transform', "translate({$x}, {$y})");

            $prefix = is_string($prefixIds) ? $prefixIds : ($prefixIds ? "doc{$i}-" : false);

            foreach ($doc->getRootElement()->getChildren() as $child) {
                $imported = $merged->importElement($child, deep: true, options: [
                    'prefix_ids' => $prefix,
                    'resolve_conflicts' => !$prefix,
                ]);
                $group->appendChild($imported);
            }

            $svg->appendChild($group);

            $rowHeight = max($rowHeight, $height);
            $maxWidth = max($maxWidth, $x + $width);

            ++$col;
            if ($col >= $columns) {
                $col = 0;
                ++$row;
                $totalHeight = $y + $rowHeight;
            }
        }

        $svg->setAttribute('width', (string) $maxWidth);
        $svg->setAttribute('height', (string) $totalHeight);

        return $merged;
    }

    /**
     * Appends content from another document to this document.
     */
    public function append(Document $other): self
    {
        if (null === $this->rootElement || null === $other->getRootElement()) {
            return $this;
        }

        foreach ($other->getRootElement()->getChildren() as $child) {
            $imported = $this->importElement($child, deep: true);
            $this->rootElement->appendChild($imported);
        }

        return $this;
    }

    // ========================================================================
    // Group Management Methods
    // ========================================================================

    /**
     * Groups multiple elements into a new group element.
     *
     * @param array<ElementInterface> $elements   Elements to group
     * @param string|null             $id         Optional ID for the group
     * @param array<string, string>   $attributes Additional attributes for the group
     *
     * @return GroupElement The created group element
     */
    public function groupElements(
        array $elements,
        ?string $id = null,
        array $attributes = [],
    ): GroupElement {
        $group = new GroupElement();

        if (null !== $id) {
            $group->setAttribute('id', $id);
        }

        foreach ($attributes as $name => $value) {
            $group->setAttribute($name, $value);
        }

        foreach ($elements as $element) {
            $parent = $element->getParent();
            if ($parent instanceof ContainerElementInterface) {
                $parent->removeChild($element);
            }
            $group->appendChild($element);
        }

        return $group;
    }

    /**
     * Ungroups a group element, moving its children to the parent.
     */
    public function ungroup(GroupElement $group): self
    {
        $parent = $group->getParent();
        if (!$parent instanceof ContainerElementInterface) {
            return $this;
        }

        $children = $group->getChildren();

        // Move children to parent
        foreach ($children as $child) {
            $group->removeChild($child);
            $parent->appendChild($child);
        }

        // Remove empty group
        $parent->removeChild($group);

        return $this;
    }

    /**
     * Flattens nested groups up to a specified depth.
     *
     * @param int|null $maxDepth Maximum nesting depth (null = flatten all)
     */
    public function flattenGroups(?int $maxDepth = null): self
    {
        if (null === $this->rootElement) {
            return $this;
        }

        $this->flattenGroupsRecursive($this->rootElement, 0, $maxDepth ?? PHP_INT_MAX);

        return $this;
    }

    /**
     * Recursively flattens groups.
     */
    private function flattenGroupsRecursive(
        ContainerElementInterface $container,
        int $currentDepth,
        int $maxDepth,
    ): void {
        if ($currentDepth >= $maxDepth) {
            return;
        }

        // First, recursively flatten children
        $children = array_values($container->getChildren());
        foreach ($children as $child) {
            if ($child instanceof ContainerElementInterface) {
                $this->flattenGroupsRecursive($child, $currentDepth + 1, $maxDepth);
            }
        }

        // Then flatten groups at this level
        $children = array_values($container->getChildren());
        foreach ($children as $child) {
            if ($child instanceof GroupElement && $this->shouldFlattenGroup($child)) {
                // Move group's children to parent (copy first to avoid modification issues)
                $groupChildren = array_values($child->getChildren());
                foreach ($groupChildren as $groupChild) {
                    $child->removeChild($groupChild);
                    $container->appendChild($groupChild);
                }

                // Remove the now-empty group
                $container->removeChild($child);
            }
        }
    }

    /**
     * Determines if a group should be flattened.
     */
    private function shouldFlattenGroup(GroupElement $group): bool
    {
        // Don't flatten groups with important attributes
        $importantAttrs = ['id', 'class', 'transform', 'opacity', 'clip-path', 'mask'];

        foreach ($importantAttrs as $attr) {
            if ($group->hasAttribute($attr)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Optimizes the document using a preset configuration.
     *
     * @param string               $preset  One of: default, aggressive, safe, web
     * @param array<string, mixed> $options Additional optimization options
     *
     * @throws InvalidArgumentException If the preset name is unknown
     */
    public function optimize(string $preset = 'default', array $options = []): self
    {
        Optimizer\Optimizer::forDocument($this, $preset, $options);

        return $this;
    }

    /**
     * Removes unused definitions from the document.
     */
    public function cleanupDefs(): self
    {
        Optimizer\Optimizer::cleanupDefs($this);

        return $this;
    }

    /**
     * Rounds numeric values to the specified precision.
     */
    public function roundValues(int $precision = 2): self
    {
        Optimizer\Optimizer::roundValues($this, $precision);

        return $this;
    }

    /**
     * Optimizes colors in the document.
     */
    public function optimizeColors(): self
    {
        Optimizer\Optimizer::optimizeColors($this);

        return $this;
    }

    /**
     * Converts inline styles to attributes.
     */
    public function inlineStyles(): self
    {
        Optimizer\Optimizer::inlineStyles($this);

        return $this;
    }

    /**
     * Simplifies paths in the document.
     */
    public function simplifyPaths(float $tolerance = 0.5): self
    {
        Optimizer\Optimizer::simplifyPaths($this, $tolerance);

        return $this;
    }

    /**
     * Removes hidden elements from the document.
     */
    public function removeHidden(): self
    {
        Optimizer\Optimizer::removeHidden($this);

        return $this;
    }

    /**
     * Generates an analysis report for the document.
     *
     * @return array<string, mixed>
     */
    public function analyze(): array
    {
        return Optimizer\Analyzer::analyze($this);
    }

    /**
     * Prints a formatted analysis report.
     */
    public function printAnalysis(): string
    {
        return Optimizer\Analyzer::printReport($this);
    }

    /**
     * Applies a theme to the document.
     *
     * @param array<string, array<string, string>> $theme
     */
    public function applyTheme(array $theme): self
    {
        Value\Style\ThemeManager::applyTheme($this, $theme);

        return $this;
    }

    /**
     * Gets the root element (alias for getRootElement).
     */
    public function getRoot(): ?ElementInterface
    {
        return $this->getRootElement();
    }

    // ========================================================================
    // Accessibility Methods
    // ========================================================================

    /**
     * Sets the document-level title for accessibility.
     *
     * @param string $title The title text
     *
     * @return self The document (for method chaining)
     */
    public function setTitle(string $title): self
    {
        Accessibility::setTitle($this, $title);

        return $this;
    }

    /**
     * Sets the document-level description for accessibility.
     *
     * @param string $description The description text
     *
     * @return self The document (for method chaining)
     */
    public function setDescription(string $description): self
    {
        Accessibility::setDescription($this, $description);

        return $this;
    }

    /**
     * Checks the document for accessibility issues.
     *
     * @return array<array{severity: string, message: string, element?: string}> Array of accessibility issues
     */
    public function checkAccessibility(): array
    {
        return Accessibility::checkAccessibility($this);
    }

    /**
     * Automatically improves accessibility by fixing common issues.
     *
     * @param array<string, mixed> $options Options for improvement
     *
     * @return self The document (for method chaining)
     */
    public function improveAccessibility(array $options = []): self
    {
        Accessibility::improveAccessibility($this, $options);

        return $this;
    }

    // ========================================================================
    // Responsive Methods
    // ========================================================================

    /**
     * Makes the SVG responsive by removing fixed dimensions and ensuring viewBox is set.
     *
     * @return self The document (for method chaining)
     */
    public function makeResponsive(): self
    {
        Layout\LayoutManager::makeResponsive($this);

        return $this;
    }

    // ========================================================================
    // Style Management Methods
    // ========================================================================

    /**
     * Gets a style manager for document-level style operations.
     */
    public function styleManager(): Value\Style\StyleManager
    {
        return new Value\Style\StyleManager($this);
    }

    // ========================================================================
    // Validation Methods
    // ========================================================================

    /**
     * Validates the document using a validation profile.
     *
     * @param Validation\ValidationProfile|null $profile Validation profile (default: lenient)
     */
    public function validate(?Validation\ValidationProfile $profile = null): Validation\ValidationResult
    {
        $validator = new Validation\Validator($profile);

        return $validator->validate($this);
    }

    /**
     * Checks if the document is valid according to a profile.
     *
     * @param Validation\ValidationProfile|null $profile Validation profile (default: lenient)
     *
     * @return bool True if valid (no errors)
     */
    public function isValid(?Validation\ValidationProfile $profile = null): bool
    {
        return $this->validate($profile)->isValid();
    }

    /**
     * Finds all broken references in the document.
     *
     * @return array<Validation\BrokenReference>
     */
    public function findBrokenReferences(): array
    {
        $tracker = new Validation\ReferenceTracker($this);

        return $tracker->findBrokenReferences();
    }

    /**
     * Finds circular reference chains in the document.
     *
     * @return array<array<string>> List of circular dependency chains
     */
    public function findCircularReferences(): array
    {
        $tracker = new Validation\ReferenceTracker($this);

        return $tracker->findCircularReferences();
    }

    /**
     * Automatically fixes common validation issues.
     *
     * @param array<string, bool> $options Fix options
     *
     * @return array{broken_references: int, duplicate_ids: int} Number of fixes applied
     */
    public function autoFix(array $options = []): array
    {
        return Validation\DocumentValidator::autoFix($this, $options);
    }

    /**
     * Fixes broken references by removing them.
     *
     * @return int Number of broken references fixed
     */
    public function fixBrokenReferences(): int
    {
        return Validation\DocumentValidator::fixBrokenReferences($this);
    }

    /**
     * Fixes duplicate IDs by renaming duplicates.
     *
     * @return int Number of duplicate IDs fixed
     */
    public function fixDuplicateIds(): int
    {
        return Validation\DocumentValidator::fixDuplicateIds($this);
    }

    /**
     * Creates a group element and appends it to the root element.
     */
    public function g(): GroupElement
    {
        $group = new GroupElement();
        if ($root = $this->getRootElement()) {
            $root->appendChild($group);
        }

        return $group;
    }

    /**
     * Creates a rectangle element and appends it to the root element.
     *
     * @param array<string, mixed> $attributes
     */
    public function rect(float $x = 0, float $y = 0, float $width = 100, float $height = 100, array $attributes = []): RectElement
    {
        $rect = new RectElement();
        $rect->setAttribute('x', $x);
        $rect->setAttribute('y', $y);
        $rect->setAttribute('width', $width);
        $rect->setAttribute('height', $height);
        foreach ($attributes as $key => $value) {
            if (is_scalar($value) || $value instanceof \Stringable) {
                $rect->setAttribute($key, (string) $value);
            }
        }
        if ($root = $this->getRootElement()) {
            $root->appendChild($rect);
        }

        return $rect;
    }

    /**
     * Creates a circle element and appends it to the root element.
     *
     * @param array<string, mixed> $attributes
     */
    public function circle(float $cx = 0, float $cy = 0, float $r = 50, array $attributes = []): CircleElement
    {
        $circle = new CircleElement();
        $circle->setAttribute('cx', $cx);
        $circle->setAttribute('cy', $cy);
        $circle->setAttribute('r', $r);
        foreach ($attributes as $key => $value) {
            if (is_scalar($value) || $value instanceof \Stringable) {
                $circle->setAttribute($key, (string) $value);
            }
        }
        if ($root = $this->getRootElement()) {
            $root->appendChild($circle);
        }

        return $circle;
    }
}
