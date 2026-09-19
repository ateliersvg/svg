<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Structural\GroupElement;

/**
 * Moves a group's transform down onto its children, so the group can be collapsed.
 *
 * Only `transform` moves. An inherited presentation attribute such as `fill` already
 * reaches every child for free, so copying it onto each of them costs bytes and buys
 * nothing: the group is one attribute, the children are N. A transform is different
 * because it composes rather than inherits, and pushing it down is what lets
 * CollapseGroupsPass remove the group and the transform passes merge what is left.
 *
 * Complement of MoveAttributesToGroupPass, which moves common child attributes up.
 *
 * Matches SVGO's `moveGroupAttrsToElems` plugin, including its guards.
 */
final readonly class MoveGroupAttrsToElemsPass implements OptimizerPassInterface
{
    /**
     * Elements that honour a transform of their own.
     *
     * A child outside this list cannot carry what the group is giving away, so the
     * whole group is left alone rather than half moved.
     */
    private const array TRANSFORMABLE = [
        'a', 'circle', 'ellipse', 'foreignObject', 'g', 'image', 'line', 'path',
        'polygon', 'polyline', 'rect', 'svg', 'switch', 'text', 'use',
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

    /**
     * Walks top down, so a transform pushed onto a nested group is pushed on again
     * from there and reaches the leaves in the right order.
     */
    private function processElement(ElementInterface $element): void
    {
        if ($element instanceof GroupElement) {
            $this->moveTransform($element);
        }

        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->processElement($child);
            }
        }
    }

    private function moveTransform(GroupElement $group): void
    {
        if (!$group->hasChildren()) {
            return;
        }

        $transform = $group->getAttribute('transform');

        if (null === $transform || '' === trim($transform)) {
            return;
        }

        if (!$this->isMovable($group)) {
            return;
        }

        foreach ($group->getChildren() as $child) {
            $own = $child->getAttribute('transform');

            // The group applies before the child, so it goes in front of it.
            $child->setAttribute('transform', null === $own || '' === trim($own)
                ? $transform
                : $transform.' '.$own);
        }

        $group->removeAttribute('transform');
    }

    private function isMovable(GroupElement $group): bool
    {
        // A reference resolves in the group's own coordinate system. Moving the
        // transform out from under it would move what it points at.
        foreach ($group->getAttributes() as $value) {
            if (\is_string($value) && str_contains($value, 'url(')) {
                return false;
            }
        }

        foreach ($group->getChildren() as $child) {
            // A child with an id may be reached by a <use> that never sees the group.
            if (null !== $child->getId()) {
                return false;
            }

            if (!\in_array($child->getTagName(), self::TRANSFORMABLE, true)) {
                return false;
            }
        }

        return true;
    }
}
