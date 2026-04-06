<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes metadata and non-visual elements from an SVG document.
 *
 * This pass removes elements that don't contribute to the visual appearance of the SVG:
 * - <metadata> elements
 * - <desc> elements (optionally)
 * - <title> elements (optionally)
 * - Editor-specific data attributes
 */
final readonly class RemoveMetadataPass implements OptimizerPassInterface
{
    /**
     * Creates a new RemoveMetadataPass.
     *
     * @param bool $removeDesc  Whether to remove <desc> elements (default: true)
     * @param bool $removeTitle Whether to remove <title> elements (default: false)
     */
    public function __construct(private bool $removeDesc = true, private bool $removeTitle = false)
    {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-metadata';
    }

    /**
     * Optimizes the document by removing metadata elements.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        $this->removeMetadataFromElement($rootElement);
    }

    /**
     * Recursively removes metadata from an element and its children.
     *
     * @param ElementInterface $element The element to process
     */
    private function removeMetadataFromElement(ElementInterface $element): void
    {
        // Remove editor-specific attributes
        $this->removeEditorAttributes($element);

        // If this is a container, process children
        if ($element instanceof ContainerElementInterface) {
            $children = $element->getChildren();

            foreach ($children as $child) {
                $tagName = $child->getTagName();

                // Check if this child should be removed
                if ($this->shouldRemoveElement($tagName)) {
                    $element->removeChild($child);
                    continue;
                }

                // Recursively process remaining children
                $this->removeMetadataFromElement($child);
            }
        }
    }

    /**
     * Determines if an element with the given tag name should be removed.
     *
     * @param string $tagName The tag name to check
     *
     * @return bool True if the element should be removed
     */
    private function shouldRemoveElement(string $tagName): bool
    {
        // Always remove metadata
        if ('metadata' === $tagName) {
            return true;
        }

        // Optionally remove desc
        if ('desc' === $tagName && $this->removeDesc) {
            return true;
        }

        // Optionally remove title
        if ('title' === $tagName && $this->removeTitle) {
            return true;
        }

        return false;
    }

    /**
     * Removes editor-specific attributes from an element.
     *
     * @param ElementInterface $element The element to process
     */
    private function removeEditorAttributes(ElementInterface $element): void
    {
        // Common editor-specific attribute prefixes
        $editorPrefixes = [
            'inkscape:',
            'sodipodi:',
            'sketch:',
            'illustrator:',
        ];

        $attributes = $element->getAttributes();

        foreach ($attributes as $name => $value) {
            // Remove attributes with editor-specific prefixes
            foreach ($editorPrefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $element->removeAttribute($name);
                    break;
                }
            }
        }
    }
}
