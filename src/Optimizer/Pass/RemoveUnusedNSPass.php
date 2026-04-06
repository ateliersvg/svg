<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes unused namespace declarations.
 *
 * This pass:
 * - Scans the document for used namespace prefixes
 * - Removes xmlns:* declarations that aren't used anywhere
 * - Keeps essential namespaces (svg, xlink, etc.)
 *
 * Example:
 * Before: <svg xmlns:foo="..." xmlns:bar="..."><circle/></svg>
 * After:  <svg><circle/></svg>
 * (assuming foo and bar prefixes weren't used)
 *
 * Benefits:
 * - Reduces file size
 * - Cleaner namespace declarations
 * - Only keeps what's actually needed
 */
final readonly class RemoveUnusedNSPass implements OptimizerPassInterface
{
    /**
     * Namespaces to always keep (essential for SVG).
     */
    private const array KEEP_NAMESPACES = [
        'svg',
        'xlink',
        'xmlns', // The default namespace
    ];

    /**
     * Creates a new RemoveUnusedNSPass.
     *
     * @param bool $keepEssential Whether to always keep essential namespaces (default: true)
     */
    public function __construct(
        private bool $keepEssential = true,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-unused-ns';
    }

    /**
     * Optimizes the document by removing unused namespaces.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Step 1: Collect all used namespace prefixes
        $usedPrefixes = $this->collectUsedNamespacePrefixes($rootElement);

        // Step 2: Remove unused xmlns declarations from root element
        $this->removeUnusedNamespaceDeclarations($rootElement, $usedPrefixes);
    }

    /**
     * Collects all namespace prefixes used in the document.
     *
     * @param ElementInterface $element The element to scan
     *
     * @return array<string, bool> Map of prefix => true
     */
    private function collectUsedNamespacePrefixes(ElementInterface $element): array
    {
        $prefixes = [];

        // Check element tag name for prefix
        $tagPrefix = $this->extractPrefix($element->getTagName());
        if (null !== $tagPrefix) {
            $prefixes[$tagPrefix] = true;
        }

        // Check all attributes for prefixes
        foreach ($element->getAttributes() as $name => $value) {
            // Skip xmlns declarations themselves
            if (str_starts_with($name, 'xmlns')) {
                continue;
            }

            $attrPrefix = $this->extractPrefix($name);
            if (null !== $attrPrefix) {
                $prefixes[$attrPrefix] = true;
            }

            // Check if the attribute value references a namespace (like xlink:href or url(#prefix:id))
            // This is common in href attributes that reference IDs with namespaces
            if (is_string($value) && str_contains($value, ':')) {
                // Extract prefix from url(#prefix:id) references
                if (preg_match('/url\(#([^:)]+):/', $value, $matches)) {
                    $prefixes[$matches[1]] = true;
                } elseif (preg_match('/#([^:)]+):/', $value, $matches)) {
                    // Extract prefix from #prefix:id references
                    $prefixes[$matches[1]] = true;
                } else {
                    // Try direct extraction
                    $valuePrefix = $this->extractPrefix($value);
                    if (null !== $valuePrefix && !str_contains($valuePrefix, '(') && !str_contains($valuePrefix, '#')) {
                        $prefixes[$valuePrefix] = true;
                    }
                }
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $childPrefixes = $this->collectUsedNamespacePrefixes($child);
                $prefixes = array_merge($prefixes, $childPrefixes);
            }
        }

        return $prefixes;
    }

    /**
     * Extracts the namespace prefix from a name.
     *
     * @param string $name The name (tag or attribute)
     *
     * @return string|null The prefix, or null if no prefix
     */
    private function extractPrefix(string $name): ?string
    {
        if (str_contains($name, ':')) {
            $parts = explode(':', $name, 2);

            return $parts[0];
        }

        return null;
    }

    /**
     * Removes unused namespace declarations from an element.
     *
     * @param ElementInterface    $element      The element to clean
     * @param array<string, bool> $usedPrefixes Used namespace prefixes
     */
    private function removeUnusedNamespaceDeclarations(ElementInterface $element, array $usedPrefixes): void
    {
        $attributes = $element->getAttributes();

        foreach ($attributes as $name => $value) {
            // Check if this is a namespace declaration
            if (str_starts_with($name, 'xmlns:')) {
                $prefix = substr($name, 6); // Remove 'xmlns:' prefix

                // Check if we should keep this namespace
                $shouldKeep = isset($usedPrefixes[$prefix]);

                // Always keep essential namespaces if configured
                if ($this->keepEssential && in_array($prefix, self::KEEP_NAMESPACES, true)) {
                    $shouldKeep = true;
                }

                // Remove if not used
                if (!$shouldKeep) {
                    $element->removeAttribute($name);
                }
            }
        }
    }
}
