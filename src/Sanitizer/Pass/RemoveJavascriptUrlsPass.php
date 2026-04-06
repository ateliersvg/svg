<?php

declare(strict_types=1);

namespace Atelier\Svg\Sanitizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

final class RemoveJavascriptUrlsPass implements SanitizerPassInterface
{
    private const array URL_ATTRIBUTES = [
        'href',
        'xlink:href',
        'src',
        'from',
        'to',
        'values',
    ];

    public function getName(): string
    {
        return 'remove-javascript-urls';
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
        foreach (self::URL_ATTRIBUTES as $attr) {
            $value = $element->getAttribute($attr);

            if (null === $value) {
                continue;
            }

            $normalized = strtolower(trim(preg_replace('/\s+/', '', $value) ?? ''));

            if (str_starts_with($normalized, 'javascript:')
                || (str_starts_with($normalized, 'data:') && !str_starts_with($normalized, 'data:image/'))
            ) {
                $element->removeAttribute($attr);
            }
        }

        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->processElement($child);
            }
        }
    }
}
