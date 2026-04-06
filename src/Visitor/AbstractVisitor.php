<?php

declare(strict_types=1);

namespace Atelier\Svg\Visitor;

use Atelier\Svg\Element\ElementInterface;

/**
 * Abstract base class for visitors in the Visitor pattern.
 *
 * This class provides a template for implementing visitors with optional
 * before and after hooks that can be used to perform actions before and
 * after visiting an element.
 */
abstract class AbstractVisitor implements VisitorInterface
{
    /**
     * Visits an element.
     *
     * This method calls beforeVisit(), then the abstract doVisit() method,
     * and finally afterVisit(). Subclasses should implement doVisit() to
     * define their specific visiting behavior.
     *
     * @param ElementInterface $element The element to visit
     *
     * @return mixed The result of visiting the element
     */
    public function visit(ElementInterface $element): mixed
    {
        $this->beforeVisit($element);
        $result = $this->doVisit($element);
        $this->afterVisit($element);

        return $result;
    }

    /**
     * Performs the actual visit operation.
     *
     * Subclasses must implement this method to define their specific
     * visiting behavior.
     *
     * @param ElementInterface $element The element to visit
     *
     * @return mixed The result of visiting the element
     */
    abstract protected function doVisit(ElementInterface $element): mixed;

    /**
     * Hook called before visiting an element.
     *
     * Subclasses can override this method to perform actions before
     * the element is visited.
     *
     * @param ElementInterface $element The element about to be visited
     */
    protected function beforeVisit(ElementInterface $element): void
    {
        // Default: do nothing
    }

    /**
     * Hook called after visiting an element.
     *
     * Subclasses can override this method to perform actions after
     * the element has been visited.
     *
     * @param ElementInterface $element The element that was visited
     */
    protected function afterVisit(ElementInterface $element): void
    {
        // Default: do nothing
    }
}
