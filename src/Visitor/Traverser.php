<?php

declare(strict_types=1);

namespace Atelier\Svg\Visitor;

use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Traverses an element tree and applies a visitor to each element.
 *
 * The Traverser implements a depth-first traversal of the SVG element tree,
 * visiting each element and its children recursively.
 */
final readonly class Traverser
{
    /**
     * Creates a new Traverser with the given visitor.
     *
     * @param VisitorInterface $visitor The visitor to apply to each element
     */
    public function __construct(
        private VisitorInterface $visitor,
    ) {
    }

    /**
     * Traverses the element tree starting from the given element.
     *
     * This method visits the element and then recursively traverses all children
     * if the element is a container.
     *
     * @param ElementInterface $element The root element to start traversal from
     */
    public function traverse(ElementInterface $element): void
    {
        // Visit the current element
        $this->visitor->visit($element);

        // If this is a container element, recursively traverse all children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->traverse($child);
            }
        }
    }
}
