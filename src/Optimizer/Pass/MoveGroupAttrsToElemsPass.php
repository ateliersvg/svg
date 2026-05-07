<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Structural\GroupElement;

/**
 * Moves inheritable presentation attributes from groups to their children.
 *
 * When a group has inheritable attributes (fill, stroke, etc.) and exists only
 * for grouping purposes (no id, class, or transforms that depend on group context),
 * those attributes can be moved to the children directly. This can enable
 * CollapseGroupsPass to then remove the group entirely.
 *
 * Complement of MoveAttributesToGroupPass (which moves common child attrs up).
 *
 * Equivalent to SVGO's `moveGroupAttrsToElems` plugin.
 */
final readonly class MoveGroupAttrsToElemsPass implements OptimizerPassInterface
{
    private const array INHERITABLE_ATTRIBUTES = [
        'fill',
        'fill-opacity',
        'fill-rule',
        'stroke',
        'stroke-dasharray',
        'stroke-dashoffset',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'stroke-opacity',
        'stroke-width',
        'opacity',
        'color',
        'font-family',
        'font-size',
        'font-style',
        'font-variant',
        'font-weight',
        'text-anchor',
        'text-decoration',
        'letter-spacing',
        'word-spacing',
        'clip-rule',
        'visibility',
        'cursor',
    ];

    public function getName(): string
    {
        return 'move-group-attrs-to-elems';
    }

    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        $this->processElement($rootElement);
    }

    private function processElement(ElementInterface $element): void
    {
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->processElement($child);
            }
        }

        if (!$element instanceof GroupElement) {
            return;
        }

        if (!$element->hasChildren()) {
            return;
        }

        $inheritableOnGroup = $this->getInheritableAttributes($element);

        if ([] === $inheritableOnGroup) {
            return;
        }

        // Move each inheritable attribute to children that don't already override it
        foreach ($inheritableOnGroup as $name => $value) {
            foreach ($element->getChildren() as $child) {
                if (!$child->hasAttribute($name)) {
                    $child->setAttribute($name, $value);
                }
            }

            $element->removeAttribute($name);
        }
    }

    /**
     * @return array<string, string>
     */
    private function getInheritableAttributes(GroupElement $group): array
    {
        $attrs = [];

        foreach (self::INHERITABLE_ATTRIBUTES as $name) {
            if ($group->hasAttribute($name)) {
                $value = $group->getAttribute($name);
                if (null !== $value) {
                    $attrs[$name] = $value;
                }
            }
        }

        return $attrs;
    }
}
