<?php

declare(strict_types=1);

namespace Atelier\Svg\Visitor;

use Atelier\Svg\Element\ElementInterface;

/**
 * Interface for visitors in the Visitor pattern.
 *
 * Visitors allow performing operations on elements without modifying their classes.
 * Each visitor can implement different operations on the element tree, such as
 * transformations, rendering, optimization, validation, etc.
 */
interface VisitorInterface
{
    /**
     * Visits an element.
     *
     * @param ElementInterface $element The element to visit
     *
     * @return mixed The result of visiting the element
     */
    public function visit(ElementInterface $element): mixed;
}
