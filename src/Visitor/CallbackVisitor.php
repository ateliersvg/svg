<?php

declare(strict_types=1);

namespace Atelier\Svg\Visitor;

use Atelier\Svg\Element\ElementInterface;

/**
 * Visitor that executes a callback on each element.
 *
 * Useful for custom traversal logic without creating a dedicated visitor class.
 */
final class CallbackVisitor extends AbstractVisitor
{
    /**
     * @param \Closure(ElementInterface): bool $callback The callback to execute on each element.
     *                                                   Return false to stop traversal.
     */
    public function __construct(
        private readonly \Closure $callback,
    ) {
    }

    protected function doVisit(ElementInterface $element): mixed
    {
        return ($this->callback)($element);
    }
}
