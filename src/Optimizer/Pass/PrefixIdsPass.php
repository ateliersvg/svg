<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that adds a prefix to all IDs and ID references.
 *
 * This pass is critical for component libraries and situations where multiple
 * SVGs are combined on the same page. Without unique IDs, references can conflict.
 *
 * This pass:
 * - Adds a prefix to all id attributes
 * - Updates all references to those IDs (url(#id), href="#id", etc.)
 * - Handles references in various attributes: fill, stroke, clip-path, mask, etc.
 *
 * Example:
 * Before: <linearGradient id="grad1"/><rect fill="url(#grad1)"/>
 * After:  <linearGradient id="prefix__grad1"/><rect fill="url(#prefix__grad1)"/>
 *
 * Benefits:
 * - Prevents ID conflicts when combining SVGs
 * - Essential for component libraries
 * - Safe for inline SVG in HTML
 */
final readonly class PrefixIdsPass implements OptimizerPassInterface
{
    /**
     * Attributes that can contain ID references.
     * These will be updated to use the prefixed IDs.
     */
    private const array ID_REFERENCE_ATTRIBUTES = [
        'fill',
        'stroke',
        'filter',
        'clip-path',
        'mask',
        'marker-start',
        'marker-mid',
        'marker-end',
        'href',
        'xlink:href',
    ];

    /**
     * Creates a new PrefixIdsPass.
     *
     * @param string|null $prefix    The prefix to add (if null, generates based on document hash)
     * @param string      $delimiter The delimiter between prefix and ID (default: '__')
     */
    public function __construct(
        private ?string $prefix = null,
        private string $delimiter = '__',
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'prefix-ids';
    }

    /**
     * Optimizes the document by prefixing IDs.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Determine the prefix to use
        $prefix = $this->prefix ?? $this->generatePrefix($document);

        if ('' === $prefix) {
            return; // No prefix, nothing to do
        }

        // Step 1: Collect all IDs in the document
        $ids = $this->collectIds($rootElement);

        if (empty($ids)) {
            return; // No IDs to prefix
        }

        // Step 2: Create mapping of old IDs to new prefixed IDs
        $idMapping = [];
        foreach ($ids as $id) {
            $idMapping[$id] = $prefix.$this->delimiter.$id;
        }

        // Step 3: Update all IDs and references
        $this->updateIdsAndReferences($rootElement, $idMapping);
    }

    /**
     * Generates a prefix based on document content.
     *
     * @param Document $document The document
     *
     * @return string Generated prefix
     */
    private function generatePrefix(Document $document): string
    {
        // Generate a short hash based on document content
        // This ensures different SVGs get different prefixes
        $content = $document->getRootElement()?->getTagName() ?? 'svg';
        $hash = substr(md5($content.microtime()), 0, 8);

        return 'svg_'.$hash;
    }

    /**
     * Collects all IDs from the document.
     *
     * @param ElementInterface $element The element to scan
     *
     * @return array<string> List of IDs
     */
    private function collectIds(ElementInterface $element): array
    {
        $ids = [];

        // Check if this element has an ID
        $id = $element->getAttribute('id');
        if (null !== $id && '' !== trim($id)) {
            $ids[] = $id;
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $childIds = $this->collectIds($child);
                $ids = array_merge($ids, $childIds);
            }
        }

        return $ids;
    }

    /**
     * Updates all IDs and references to use prefixed versions.
     *
     * @param ElementInterface      $element   The element to process
     * @param array<string, string> $idMapping Map of old ID to new ID
     */
    private function updateIdsAndReferences(ElementInterface $element, array $idMapping): void
    {
        // Update the ID attribute if present
        $id = $element->getAttribute('id');
        if (null !== $id && isset($idMapping[$id])) {
            $element->setAttribute('id', $idMapping[$id]);
        }

        // Update ID references in attributes
        foreach (self::ID_REFERENCE_ATTRIBUTES as $attrName) {
            $attrValue = $element->getAttribute($attrName);
            if (null !== $attrValue) {
                $updatedValue = $this->updateIdReferences($attrValue, $idMapping);
                if ($updatedValue !== $attrValue) {
                    $element->setAttribute($attrName, $updatedValue);
                }
            }
        }

        // Update style attribute if it contains ID references
        $style = $element->getAttribute('style');
        if (null !== $style) {
            $updatedStyle = $this->updateIdReferences($style, $idMapping);
            if ($updatedStyle !== $style) {
                $element->setAttribute('style', $updatedStyle);
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->updateIdsAndReferences($child, $idMapping);
            }
        }
    }

    /**
     * Updates ID references in an attribute value.
     *
     * @param string                $value     The attribute value
     * @param array<string, string> $idMapping Map of old ID to new ID
     *
     * @return string Updated value
     */
    private function updateIdReferences(string $value, array $idMapping): string
    {
        // Handle url(#id) references
        $value = preg_replace_callback(
            '/url\(#([^)]+)\)/',
            function ($matches) use ($idMapping) {
                $id = $matches[1];
                if (isset($idMapping[$id])) {
                    return 'url(#'.$idMapping[$id].')';
                }

                return $matches[0];
            },
            $value
        ) ?? $value;

        // Handle #id references (in href attributes)
        $value = preg_replace_callback(
            '/#([a-zA-Z][a-zA-Z0-9_-]*)/',
            function ($matches) use ($idMapping) {
                $id = $matches[1];
                if (isset($idMapping[$id])) {
                    return '#'.$idMapping[$id];
                }

                return $matches[0];
            },
            $value
        ) ?? $value;

        return $value;
    }
}
