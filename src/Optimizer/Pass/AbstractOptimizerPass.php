<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Abstract base class for optimizer passes.
 *
 * Provides common traversal logic used by most optimizer passes:
 * - Document root element null-checking
 * - Recursive element tree traversal
 *
 * Subclasses only need to implement:
 * - getName(): Returns the pass name
 * - processElement(): Performs the actual optimization on each element
 */
abstract class AbstractOptimizerPass implements OptimizerPassInterface
{
    /**
     * Optimizes the document by traversing all elements.
     *
     * This method retrieves the root element, performs null-checking,
     * and initiates the recursive traversal.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        $this->traverseElement($rootElement);
    }

    /**
     * Traverses an element and its children recursively.
     *
     * This method:
     * 1. Calls processElement() on the current element
     * 2. Recursively traverses all children (if the element is a container)
     *
     * Override this method if you need different traversal behavior
     * (e.g., bottom-up instead of top-down).
     *
     * @param ElementInterface $element The element to traverse
     */
    protected function traverseElement(ElementInterface $element): void
    {
        $this->processElement($element);

        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->traverseElement($child);
            }
        }
    }

    /**
     * Processes a single element.
     *
     * Implement this method to define the optimization logic for each element.
     *
     * @param ElementInterface $element The element to process
     */
    abstract protected function processElement(ElementInterface $element): void;
}
