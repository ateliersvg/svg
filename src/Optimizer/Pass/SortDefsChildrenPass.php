<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Structural\DefsElement;

/**
 * Sorts children of <defs> elements for better gzip compression.
 *
 * Elements are sorted by tag name first, then by id attribute. This produces
 * a deterministic order that improves gzip's ability to find repeated patterns.
 *
 * Equivalent to SVGO's `sortDefsChildren` plugin.
 */
final readonly class SortDefsChildrenPass implements OptimizerPassInterface
{
    public function getName(): string
    {
        return 'sort-defs-children';
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
        if ($element instanceof DefsElement) {
            $this->sortChildren($element);
        }

        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->processElement($child);
            }
        }
    }

    private function sortChildren(DefsElement $defs): void
    {
        $children = $defs->getChildren();

        if (\count($children) <= 1) {
            return;
        }

        $sorted = $children;
        usort($sorted, static function (ElementInterface $a, ElementInterface $b): int {
            $tagCmp = $a->getTagName() <=> $b->getTagName();
            if (0 !== $tagCmp) {
                return $tagCmp;
            }

            return ($a->getAttribute('id') ?? '') <=> ($b->getAttribute('id') ?? '');
        });

        // Check if already sorted
        $alreadySorted = true;
        for ($i = 0, $count = \count($children); $i < $count; ++$i) {
            if ($children[$i] !== $sorted[$i]) {
                $alreadySorted = false;
                break;
            }
        }

        if ($alreadySorted) {
            return;
        }

        $defs->clearChildren();
        foreach ($sorted as $child) {
            $defs->appendChild($child);
        }
    }
}
