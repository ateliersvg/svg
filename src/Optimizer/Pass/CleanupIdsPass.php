<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes unused IDs and optionally minifies them.
 *
 * This pass can:
 * - Remove IDs that are not referenced anywhere
 * - Minify IDs to shorter names (a, b, c, aa, ab, etc.)
 * - Preserve specific IDs based on patterns
 * - Add a prefix to all IDs
 */
final readonly class CleanupIdsPass implements OptimizerPassInterface
{
    private const array REFERENCE_ATTRIBUTES = [
        'href',
        'xlink:href',
        'clip-path',
        'fill',
        'stroke',
        'filter',
        'mask',
        'marker-start',
        'marker-mid',
        'marker-end',
        'begin',
        'end',
    ];

    /**
     * Creates a new CleanupIdsPass.
     *
     * @param bool          $remove   Whether to remove unused IDs (default: true)
     * @param bool          $minify   Whether to minify IDs (default: false)
     * @param string        $prefix   Prefix to add to all IDs (default: '')
     * @param array<string> $preserve Array of ID patterns to preserve (default: [])
     */
    public function __construct(private bool $remove = true, private bool $minify = false, private string $prefix = '', private array $preserve = [])
    {
    }

    public function getName(): string
    {
        return 'cleanup-ids';
    }

    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Collect all IDs and references
        $ids = $this->collectIds($rootElement);
        $references = $this->collectReferences($rootElement);

        // Remove unused IDs
        if ($this->remove) {
            $this->removeUnusedIds($rootElement, $ids, $references);
            // Update ids list after removal
            $ids = $this->collectIds($rootElement);
        }

        // Minify IDs
        if ($this->minify) {
            $mapping = $this->createIdMapping($ids);
            $this->applyIdMapping($rootElement, $mapping);
        } elseif ('' !== $this->prefix) {
            // Just add prefix without minifying
            $mapping = [];
            foreach ($ids as $id) {
                if (!$this->shouldPreserveId($id)) {
                    $mapping[$id] = $this->prefix.$id;
                }
            }
            $this->applyIdMapping($rootElement, $mapping);
        }
    }

    /**
     * Collects all IDs in the document.
     *
     * @return array<string>
     */
    private function collectIds(ElementInterface $element): array
    {
        $ids = [];

        if ($element->hasAttribute('id')) {
            $id = $element->getAttribute('id');
            if (null !== $id && '' !== $id) {
                $ids[] = $id;
            }
        }

        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $ids = array_merge($ids, $this->collectIds($child));
            }
        }

        return $ids;
    }

    /**
     * Collects all ID references in the document.
     *
     * @return array<string>
     */
    private function collectReferences(ElementInterface $element): array
    {
        $refs = [];

        // Check reference attributes
        foreach (self::REFERENCE_ATTRIBUTES as $attr) {
            if ($element->hasAttribute($attr)) {
                $value = $element->getAttribute($attr);
                if (null !== $value) {
                    $refs = array_merge($refs, $this->extractIdReferences($value));
                }
            }
        }

        // Check all attributes for url() references
        foreach ($element->getAttributes() as $name => $value) {
            if (!in_array($name, self::REFERENCE_ATTRIBUTES, true)) {
                $refs = array_merge($refs, $this->extractIdReferences($value));
            }
        }

        // Recurse
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $refs = array_merge($refs, $this->collectReferences($child));
            }
        }

        return array_unique($refs);
    }

    /**
     * Extracts ID references from an attribute value.
     *
     * @return array<string>
     */
    private function extractIdReferences(string $value): array
    {
        $refs = [];

        // Check for #id references
        if (str_starts_with($value, '#')) {
            $refs[] = substr($value, 1);
        }

        // Check for url(#id) references
        if (preg_match_all('/url\(#([^)]+)\)/', $value, $matches)) {
            $refs = array_merge($refs, $matches[1]);
        }

        return $refs;
    }

    /**
     * Removes unused IDs from the document.
     *
     * @param array<string> $ids
     * @param array<string> $references
     */
    private function removeUnusedIds(ElementInterface $element, array $ids, array $references): void
    {
        if ($element->hasAttribute('id')) {
            $id = $element->getAttribute('id');
            if (null !== $id && !in_array($id, $references, true) && !$this->shouldPreserveId($id)) {
                $element->removeAttribute('id');
            }
        }

        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->removeUnusedIds($child, $ids, $references);
            }
        }
    }

    /**
     * Creates a mapping of old IDs to new minified IDs.
     *
     * @param array<string> $ids
     *
     * @return array<string, string>
     */
    private function createIdMapping(array $ids): array
    {
        $mapping = [];
        $counter = 0;

        foreach ($ids as $id) {
            if ($this->shouldPreserveId($id)) {
                // Keep preserved IDs as-is
                continue;
            }

            $newId = $this->prefix.$this->generateMinifiedId($counter);
            $mapping[$id] = $newId;
            ++$counter;
        }

        return $mapping;
    }

    /**
     * Generates a minified ID from a counter.
     * Generates: a, b, c, ..., z, aa, ab, ac, ..., az, ba, ...
     */
    private function generateMinifiedId(int $counter): string
    {
        $id = '';
        do {
            $id = chr(97 + ($counter % 26)).$id;
            $counter = intdiv($counter, 26) - 1;
        } while ($counter >= 0);

        return $id;
    }

    /**
     * Applies the ID mapping to the document.
     *
     * @param array<string, string> $mapping
     */
    private function applyIdMapping(ElementInterface $element, array $mapping): void
    {
        // Replace ID
        if ($element->hasAttribute('id')) {
            $id = $element->getAttribute('id');
            if (null !== $id && isset($mapping[$id])) {
                $element->setAttribute('id', $mapping[$id]);
            }
        }

        // Replace references in attributes
        foreach (self::REFERENCE_ATTRIBUTES as $attr) {
            if ($element->hasAttribute($attr)) {
                $value = $element->getAttribute($attr);
                if (null !== $value) {
                    $newValue = $this->replaceIdReferences($value, $mapping);
                    if ($newValue !== $value) {
                        $element->setAttribute($attr, $newValue);
                    }
                }
            }
        }

        // Replace url() references in all attributes
        foreach ($element->getAttributes() as $name => $value) {
            if (!in_array($name, self::REFERENCE_ATTRIBUTES, true)) {
                $newValue = $this->replaceIdReferences($value, $mapping);
                if ($newValue !== $value) {
                    $element->setAttribute($name, $newValue);
                }
            }
        }

        // Recurse
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->applyIdMapping($child, $mapping);
            }
        }
    }

    /**
     * Replaces ID references in a value.
     *
     * @param array<string, string> $mapping
     */
    private function replaceIdReferences(string $value, array $mapping): string
    {
        // Replace #id references
        if (str_starts_with($value, '#')) {
            $id = substr($value, 1);
            if (isset($mapping[$id])) {
                return '#'.$mapping[$id];
            }
        }

        // Replace url(#id) references
        return preg_replace_callback('/url\(#([^)]+)\)/', function ($matches) use ($mapping) {
            $id = $matches[1];
            if (isset($mapping[$id])) {
                return 'url(#'.$mapping[$id].')';
            }

            return $matches[0];
        }, $value) ?? $value;
    }

    /**
     * Checks if an ID should be preserved.
     */
    private function shouldPreserveId(string $id): bool
    {
        foreach ($this->preserve as $pattern) {
            if (fnmatch($pattern, $id)) {
                return true;
            }
        }

        return false;
    }
}
