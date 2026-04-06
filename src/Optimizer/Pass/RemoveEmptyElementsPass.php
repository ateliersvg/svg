<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes empty elements with no meaningful content.
 *
 * This pass removes elements that:
 * - Have no children (for container elements)
 * - Have no meaningful attributes (except id, class, and event handlers)
 *
 * The pass is configurable to specify which element types should be checked.
 * By default, it checks: g, text, tspan, defs, style, script
 */
final class RemoveEmptyElementsPass implements OptimizerPassInterface
{
    use PreservingAttributesTrait;

    /** @var list<string> Default element types to check for emptiness */
    private const array DEFAULT_CHECKABLE_ELEMENTS = [
        'g',
        'text',
        'tspan',
        'defs',
        'style',
        'script',
    ];

    /** @var list<string> */
    private array $checkableElements;

    /** @var list<string> */
    private array $preservingAttributes;

    /**
     * Creates a new RemoveEmptyElementsPass.
     *
     * @param list<string>|null $checkableElements    Element types to check (null for defaults)
     * @param list<string>|null $preservingAttributes Attributes that preserve elements (null for defaults)
     */
    public function __construct(?array $checkableElements = null, ?array $preservingAttributes = null)
    {
        $this->checkableElements = $checkableElements ?? self::DEFAULT_CHECKABLE_ELEMENTS;
        $this->preservingAttributes = $preservingAttributes ?? $this->getDefaultPreservingAttributes();
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-empty-elements';
    }

    /**
     * Optimizes the document by removing empty elements.
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
     * Recursively processes elements to remove empty ones.
     *
     * @param ElementInterface $element The element to process
     */
    private function processElement(ElementInterface $element): void
    {
        // Process children first (bottom-up) if this is a container
        if ($element instanceof ContainerElementInterface) {
            $toRemove = [];

            // Single pass: recursively process and collect elements to remove
            foreach ($element->getChildren() as $child) {
                $this->processElement($child);

                if ($this->shouldRemoveElement($child)) {
                    $toRemove[] = $child;
                }
            }

            // Remove all marked elements in one pass
            foreach ($toRemove as $child) {
                $element->removeChild($child);
            }
        }
    }

    /**
     * Determines if an element should be removed as empty.
     *
     * @param ElementInterface $element The element to check
     *
     * @return bool True if the element should be removed
     */
    private function shouldRemoveElement(ElementInterface $element): bool
    {
        $tagName = $element->getTagName();

        // Only check elements in our checkable list
        if (!in_array($tagName, $this->checkableElements, true)) {
            return false;
        }

        // If element has preserving attributes, keep it
        if ($this->hasPreservingAttributes($element, $this->preservingAttributes)) {
            return false;
        }

        // For container elements, check if they're empty
        if ($element instanceof ContainerElementInterface) {
            // Remove if no children and no other meaningful attributes
            return !$element->hasChildren();
        }

        // For non-container elements in the checkable list (like style, script)
        // we'd need text content support to determine if they're truly empty
        // For now, keep them if they're not containers
        return false;
    }
}
