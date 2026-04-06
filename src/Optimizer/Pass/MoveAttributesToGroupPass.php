<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that moves common attributes to parent group elements.
 *
 * This pass finds attributes that are identical across all children of a group
 * and moves them to the parent group element, reducing redundancy.
 *
 * Example:
 * Before: <g><rect fill="red"/><circle fill="red"/></g>
 * After:  <g fill="red"><rect/><circle/></g>
 */
final readonly class MoveAttributesToGroupPass implements OptimizerPassInterface
{
    /**
     * Attributes that can be inherited and moved to parent.
     */
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

    /**
     * Creates a new MoveAttributesToGroupPass.
     *
     * @param int $minChildrenCount Minimum number of children required to move attributes (default: 2)
     */
    public function __construct(private int $minChildrenCount = 2)
    {
    }

    public function getName(): string
    {
        return 'move-attributes-to-group';
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
     * Recursively processes elements to move common attributes.
     */
    private function processElement(ElementInterface $element): void
    {
        // Process children first (bottom-up)
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->processElement($child);
            }

            // Now check if we can move attributes from children to this element
            $this->moveCommonAttributesToParent($element);
        }
    }

    /**
     * Moves common attributes from children to parent.
     */
    private function moveCommonAttributesToParent(ContainerElementInterface $parent): void
    {
        $children = $parent->getChildren();

        if (count($children) < $this->minChildrenCount) {
            return;
        }

        // Find common attributes
        $commonAttrs = $this->findCommonAttributes($children);

        if (empty($commonAttrs)) {
            return;
        }

        // Move common attributes to parent
        foreach ($commonAttrs as $attrName => $attrValue) {
            // Don't override existing parent attributes
            if (!$parent->hasAttribute($attrName)) {
                $parent->setAttribute($attrName, $attrValue);

                // Remove from all children
                foreach ($children as $child) {
                    if ($child->hasAttribute($attrName)) {
                        $child->removeAttribute($attrName);
                    }
                }
            }
        }
    }

    /**
     * Finds attributes that are common to all children.
     *
     * @param array<ElementInterface> $children
     *
     * @return array<string, string>
     */
    private function findCommonAttributes(array $children): array
    {
        assert(!empty($children));

        // Start with first child's inheritable attributes
        $firstChild = $children[0];
        $commonAttrs = [];

        foreach (self::INHERITABLE_ATTRIBUTES as $attrName) {
            if ($firstChild->hasAttribute($attrName)) {
                $value = $firstChild->getAttribute($attrName);
                if (null !== $value) {
                    $commonAttrs[$attrName] = $value;
                }
            }
        }

        // Check if all other children have the same attributes
        for ($i = 1; $i < count($children); ++$i) {
            $child = $children[$i];
            $toRemove = [];

            foreach ($commonAttrs as $attrName => $attrValue) {
                if (!$child->hasAttribute($attrName)) {
                    $toRemove[] = $attrName;
                    continue;
                }

                $childValue = $child->getAttribute($attrName);
                if ($childValue !== $attrValue) {
                    $toRemove[] = $attrName;
                }
            }

            // Remove attributes that don't match
            foreach ($toRemove as $attrName) {
                unset($commonAttrs[$attrName]);
            }

            // If no common attributes left, we can stop
            if (empty($commonAttrs)) {
                break;
            }
        }

        return $commonAttrs;
    }
}
