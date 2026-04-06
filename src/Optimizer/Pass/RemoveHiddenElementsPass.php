<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes hidden elements from the SVG document.
 *
 * This pass removes elements that are not visible:
 * - Elements with display="none"
 * - Elements with visibility="hidden" (configurable)
 * - Elements with opacity="0" (configurable)
 *
 * Elements with an id attribute are preserved by default, as they might be
 * shown dynamically via CSS or JavaScript.
 */
final readonly class RemoveHiddenElementsPass implements OptimizerPassInterface
{
    /**
     * Creates a new RemoveHiddenElementsPass.
     *
     * @param bool $removeDisplayNone      Remove elements with display="none" (default: true)
     * @param bool $removeVisibilityHidden Remove elements with visibility="hidden" (default: true)
     * @param bool $removeOpacityZero      Remove elements with opacity="0" (default: false)
     * @param bool $preserveWithId         Preserve elements with id attribute (default: true)
     */
    public function __construct(
        private bool $removeDisplayNone = true,
        private bool $removeVisibilityHidden = true,
        private bool $removeOpacityZero = false,
        private bool $preserveWithId = true,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-hidden-elements';
    }

    /**
     * Optimizes the document by removing hidden elements.
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
     * Recursively processes elements to remove hidden ones.
     *
     * @param ElementInterface $element The element to process
     */
    private function processElement(ElementInterface $element): void
    {
        // Process children first if this is a container
        if ($element instanceof ContainerElementInterface) {
            // Use array copy to avoid modification during iteration
            $children = $element->getChildren();

            foreach ($children as $child) {
                $this->processElement($child);
            }

            // After processing children, check if any should be removed
            foreach ($children as $child) {
                if ($this->shouldRemoveElement($child)) {
                    $element->removeChild($child);
                }
            }
        }
    }

    /**
     * Determines if an element should be removed as hidden.
     *
     * @param ElementInterface $element The element to check
     *
     * @return bool True if the element should be removed
     */
    private function shouldRemoveElement(ElementInterface $element): bool
    {
        // Preserve elements with ID if configured to do so
        if ($this->preserveWithId && $element->hasAttribute('id')) {
            return false;
        }

        // Check display="none"
        if ($this->removeDisplayNone && $this->hasDisplayNone($element)) {
            return true;
        }

        // Check visibility="hidden"
        if ($this->removeVisibilityHidden && $this->hasVisibilityHidden($element)) {
            return true;
        }

        // Check opacity="0"
        if ($this->removeOpacityZero && $this->hasOpacityZero($element)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if an element has display="none".
     *
     * @param ElementInterface $element The element to check
     *
     * @return bool True if the element has display="none"
     */
    private function hasDisplayNone(ElementInterface $element): bool
    {
        $display = $element->getAttribute('display');

        return 'none' === $display;
    }

    /**
     * Checks if an element has visibility="hidden".
     *
     * @param ElementInterface $element The element to check
     *
     * @return bool True if the element has visibility="hidden"
     */
    private function hasVisibilityHidden(ElementInterface $element): bool
    {
        $visibility = $element->getAttribute('visibility');

        return 'hidden' === $visibility;
    }

    /**
     * Checks if an element has opacity="0".
     *
     * @param ElementInterface $element The element to check
     *
     * @return bool True if the element has opacity="0"
     */
    private function hasOpacityZero(ElementInterface $element): bool
    {
        $opacity = $element->getAttribute('opacity');
        if (null === $opacity) {
            return false;
        }

        // Handle both "0" and "0.0" formats
        return 0.0 === (float) $opacity;
    }
}
