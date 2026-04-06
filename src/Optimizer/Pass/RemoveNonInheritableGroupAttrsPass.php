<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Removes non-inheritable attributes from group elements.
 *
 * Group elements (<g>) should only have inheritable presentation attributes.
 * This pass removes attributes that don't make sense on groups because they
 * don't inherit to children.
 *
 * Example:
 * Before: <g transform="..." x="10" y="20">...</g>
 * After:  <g transform="...">...</g>
 */
final class RemoveNonInheritableGroupAttrsPass extends AbstractOptimizerPass
{
    // Attributes that are NOT inheritable and should be removed from <g> elements
    private const array NON_INHERITABLE_ATTRS = [
        'x', 'y', 'width', 'height',
        'x1', 'y1', 'x2', 'y2',
        'cx', 'cy', 'r', 'rx', 'ry',
        'fx', 'fy',
        'd', 'points',
        'viewBox', 'preserveAspectRatio',
        'offset',
    ];

    public function getName(): string
    {
        return 'remove-non-inheritable-group-attrs';
    }

    protected function processElement(ElementInterface $element): void
    {
        // Only process <g> elements
        if ('g' === $element->getTagName()) {
            foreach (self::NON_INHERITABLE_ATTRS as $attr) {
                if ($element->hasAttribute($attr)) {
                    $element->removeAttribute($attr);
                }
            }
        }
    }
}
