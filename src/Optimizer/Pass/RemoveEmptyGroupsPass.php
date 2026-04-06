<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\Structural\GroupElement;

/**
 * Removes empty <g> elements that do not carry meaningful attributes.
 *
 * Useful when source SVGs include editor artefacts such as nested empty groups
 * that only bloat the markup. Groups with IDs, classes, or event handlers are
 * preserved since they might be referenced elsewhere.
 */
final class RemoveEmptyGroupsPass implements OptimizerPassInterface
{
    use PreservingAttributesTrait;

    /** @var list<string> */
    private array $preservingAttributes;

    /**
     * Attributes that can be safely propagated from a group to its children when unwrapping.
     * These are presentation attributes that don't rely on inheritance order.
     *
     * @var list<string>
     */
    private const array PROPAGATABLE_ATTRIBUTES = [
        'fill',
        'stroke',
        'stroke-width',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-dasharray',
        'stroke-dashoffset',
        'fill-opacity',
        'stroke-opacity',
        'opacity',
        'color',
        'vector-effect',
    ];

    /**
     * @param list<string>|null $preservingAttributes      attribute names that prevent removal
     * @param bool              $unwrapAttributeLessGroups whether to unwrap groups that have children but no attributes
     */
    public function __construct(?array $preservingAttributes = null, private bool $unwrapAttributeLessGroups = true)
    {
        $this->preservingAttributes = $preservingAttributes ?? $this->getDefaultPreservingAttributes();
    }

    public function getName(): string
    {
        return 'remove-empty-groups';
    }

    public function optimize(Document $document): void
    {
        $root = $document->getRootElement();

        if (null === $root) {
            return;
        }

        $this->processContainer($root);
    }

    private function processContainer(ContainerElementInterface $element): void
    {
        $children = $element->getChildren();

        foreach ($children as $child) {
            if ($child instanceof ContainerElementInterface) {
                $this->processContainer($child);
            }
        }

        $updatedChildren = [];
        $changed = false;

        foreach ($children as $child) {
            if ($child instanceof GroupElement) {
                if ($this->shouldRemoveGroup($child)) {
                    $changed = true;
                    continue;
                }

                if ($this->shouldUnwrapGroup($child)) {
                    $changed = true;

                    $this->propagateAttributes($child);

                    $grandChildren = $child->getChildren();
                    if (!empty($grandChildren)) {
                        $child->clearChildren();
                        foreach ($grandChildren as $grandChild) {
                            $updatedChildren[] = $grandChild;
                        }
                    }

                    continue;
                }
            }

            $updatedChildren[] = $child;
        }

        if ($changed) {
            $element->clearChildren();
            foreach ($updatedChildren as $child) {
                $element->appendChild($child);
            }
        }
    }

    private function shouldRemoveGroup(GroupElement $group): bool
    {
        if ($group->hasChildren()) {
            return false;
        }

        return !$this->hasPreservingAttributes($group, $this->preservingAttributes);
    }

    private function shouldUnwrapGroup(GroupElement $group): bool
    {
        if (!$group->hasChildren()) {
            return false;
        }

        $attributes = $group->getAttributes();

        if (empty($attributes)) {
            return $this->unwrapAttributeLessGroups;
        }

        if ($this->hasPreservingAttributes($group, $this->preservingAttributes)) {
            return false;
        }

        foreach (array_keys($attributes) as $name) {
            if (!in_array($name, self::PROPAGATABLE_ATTRIBUTES, true)) {
                return false;
            }
        }

        return true;
    }

    private function propagateAttributes(GroupElement $group): void
    {
        $attributes = $group->getAttributes();

        if (empty($attributes)) {
            return;
        }

        foreach ($group->getChildren() as $child) {
            foreach ($attributes as $name => $value) {
                assert(in_array($name, self::PROPAGATABLE_ATTRIBUTES, true));

                if (!$child->hasAttribute($name)) {
                    $child->setAttribute($name, $value);
                }
            }
        }
    }
}
