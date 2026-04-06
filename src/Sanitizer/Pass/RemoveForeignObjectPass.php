<?php

declare(strict_types=1);

namespace Atelier\Svg\Sanitizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

final class RemoveForeignObjectPass implements SanitizerPassInterface
{
    public function getName(): string
    {
        return 'remove-foreign-object';
    }

    public function sanitize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        $this->processElement($rootElement);
    }

    private function processElement(ElementInterface $element): void
    {
        if (!$element instanceof ContainerElementInterface) {
            return;
        }

        $childrenToRemove = [];

        foreach ($element->getChildren() as $child) {
            if ('foreignObject' === $child->getTagName()) {
                $childrenToRemove[] = $child;
            } else {
                $this->processElement($child);
            }
        }

        foreach ($childrenToRemove as $child) {
            $element->removeChild($child);
        }
    }
}
