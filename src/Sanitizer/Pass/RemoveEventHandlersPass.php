<?php

declare(strict_types=1);

namespace Atelier\Svg\Sanitizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

final class RemoveEventHandlersPass implements SanitizerPassInterface
{
    public function getName(): string
    {
        return 'remove-event-handlers';
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
        foreach ($element->getAttributes() as $name => $value) {
            if (str_starts_with(strtolower($name), 'on')) {
                $element->removeAttribute($name);
            }
        }

        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->processElement($child);
            }
        }
    }
}
