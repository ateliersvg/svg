<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes editor-specific metadata and namespaces.
 *
 * This pass removes metadata added by SVG editors like:
 * - Adobe Illustrator (xmlns:x, x:xmpmeta, etc.)
 * - Sketch (sketch:type, etc.)
 * - Inkscape (inkscape:*, sodipodi:*)
 * - CorelDRAW (corel-id, etc.)
 *
 * This metadata is only useful in the originating editor and adds unnecessary
 * file size when the SVG is used in production.
 *
 * Benefits:
 * - Significant file size reduction (can be 20-50% for some files)
 * - Cleaner SVG markup
 * - No functional impact on rendering
 */
final class RemoveEditorsNSDataPass implements OptimizerPassInterface
{
    /**
     * Editor-specific namespace prefixes to remove.
     */
    private const array EDITOR_NAMESPACES = [
        'sketch',     // Sketch
        'inkscape',   // Inkscape
        'sodipodi',   // Sodipodi (used by Inkscape)
        'illustrator', // Adobe Illustrator
        'x',          // Adobe XMP metadata
        'i',          // Adobe Illustrator
        'a',          // Adobe
        'xodm',       // XOD Metadata
        'coreldraw',  // CorelDRAW
        'corel-id',   // CorelDRAW IDs
        'serif',      // Serif DrawPlus
        'msvisio',    // Microsoft Visio
        'v',          // Microsoft Visio
    ];

    /**
     * Specific attribute patterns to remove (regardless of namespace).
     */
    private const array ATTRIBUTE_PATTERNS = [
        '/^xmlns:(sketch|inkscape|sodipodi|illustrator|x|i|a|xodm|coreldraw|serif|msvisio|v)$/i',
        '/^(sketch|inkscape|sodipodi):/i',
        '/^data-(name|tags|type)$/i', // Common editor metadata attributes
        '/^corel-id$/i', // CorelDRAW ID attribute
    ];

    /**
     * Element tag names to remove entirely.
     */
    private const array REMOVE_ELEMENTS = [
        'metadata',       // Generic metadata
        'sodipodi:namedview', // Inkscape view settings
        'inkscape:perspective', // Inkscape perspective
        'inkscape:grid',  // Inkscape grid
    ];

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-editors-ns-data';
    }

    /**
     * Optimizes the document by removing editor-specific data.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        $this->processElement($rootElement);
    }

    /**
     * Recursively processes elements to remove editor metadata.
     *
     * @param ElementInterface $element The element to process
     */
    private function processElement(ElementInterface $element): void
    {
        // Remove editor-specific attributes from this element
        $this->removeEditorAttributes($element);

        // Process children
        if ($element instanceof ContainerElementInterface) {
            // Use array copy to avoid modification during iteration
            $children = $element->getChildren();

            foreach ($children as $child) {
                // Check if this child should be removed entirely
                if ($this->shouldRemoveElement($child)) {
                    $element->removeChild($child);
                } else {
                    // Recurse to process the child
                    $this->processElement($child);
                }
            }
        }
    }

    /**
     * Removes editor-specific attributes from an element.
     *
     * @param ElementInterface $element The element to clean
     */
    private function removeEditorAttributes(ElementInterface $element): void
    {
        $attributes = $element->getAttributes();

        foreach ($attributes as $name => $value) {
            if ($this->isEditorAttribute($name)) {
                $element->removeAttribute($name);
            }
        }
    }

    /**
     * Checks if an attribute is editor-specific.
     *
     * @param string $attributeName The attribute name
     *
     * @return bool True if it's an editor attribute
     */
    private function isEditorAttribute(string $attributeName): bool
    {
        // Check against patterns
        foreach (self::ATTRIBUTE_PATTERNS as $pattern) {
            if (preg_match($pattern, $attributeName)) {
                return true;
            }
        }

        // Check if it starts with an editor namespace prefix
        foreach (self::EDITOR_NAMESPACES as $namespace) {
            if (str_starts_with($attributeName, $namespace.':')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if an element should be removed entirely.
     *
     * @param ElementInterface $element The element to check
     *
     * @return bool True if the element should be removed
     */
    private function shouldRemoveElement(ElementInterface $element): bool
    {
        $tagName = $element->getTagName();

        // Check against list of elements to remove
        foreach (self::REMOVE_ELEMENTS as $removeTag) {
            if (0 === strcasecmp($tagName, $removeTag)) {
                return true;
            }
        }

        // Check if tag name contains editor namespace prefix
        foreach (self::EDITOR_NAMESPACES as $namespace) {
            if (str_starts_with($tagName, $namespace.':')) {
                return true;
            }
        }

        return false;
    }
}
