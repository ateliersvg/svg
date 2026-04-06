<?php

declare(strict_types=1);

namespace Atelier\Svg\Validation;

use Atelier\Svg\Element\ElementInterface;

/**
 * Represents a broken reference (reference to a non-existent ID).
 */
final readonly class BrokenReference
{
    public function __construct(
        public string $referencedId,
        public ElementInterface $referencingElement,
        public string $attribute,
        public string $value,
    ) {
    }

    /**
     * Gets a human-readable description of the broken reference.
     */
    public function getDescription(): string
    {
        $elementTag = $this->referencingElement->getTagName();
        $elementId = $this->referencingElement->getId();
        $elementDesc = $elementId ? "<{$elementTag} id=\"{$elementId}\">" : "<{$elementTag}>";

        return "Reference to '#{$this->referencedId}' in {$elementDesc} attribute '{$this->attribute}' not found";
    }
}
