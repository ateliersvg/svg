<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Removes redundant SVG attributes such as version and xml:space="preserve".
 */
final class RemoveRedundantSvgAttributesPass extends AbstractOptimizerPass
{
    public function __construct(
        private readonly bool $removeVersionAttribute = true,
        private readonly bool $removeXmlSpacePreserve = true,
    ) {
    }

    public function getName(): string
    {
        return 'remove-redundant-svg-attributes';
    }

    protected function processElement(ElementInterface $element): void
    {
        $tagName = strtolower($element->getTagName());

        if ($this->removeVersionAttribute && 'svg' === $tagName && $element->hasAttribute('version')) {
            $element->removeAttribute('version');
        }

        if ($this->removeXmlSpacePreserve && $element->hasAttribute('xml:space')) {
            $value = $element->getAttribute('xml:space');

            if (null !== $value && 'preserve' === strtolower(trim($value))) {
                $element->removeAttribute('xml:space');
            }
        }
    }
}
