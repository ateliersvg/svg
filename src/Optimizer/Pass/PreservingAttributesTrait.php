<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Trait providing common logic for checking preserving attributes.
 *
 * Many optimization passes need to check if an element has attributes that
 * should prevent removal (e.g., id, class, event handlers). This trait
 * consolidates that logic to avoid duplication.
 *
 * Attributes that preserve an element:
 * - id: Might be referenced by CSS selectors or JavaScript
 * - class: Might be styled via external CSS
 * - Event handlers: onclick, onload, onmouseover, etc.
 */
trait PreservingAttributesTrait
{
    /**
     * Default attributes that preserve an element even when empty.
     *
     * @var list<string>
     */
    private const DEFAULT_PRESERVING_ATTRIBUTES = [
        'id',           // Might be referenced
        'class',        // Might be styled via CSS
        'onclick',      // Event handlers
        'onload',
        'onmouseover',
        'onmouseout',
        'onmousemove',
        'onmousedown',
        'onmouseup',
        'onfocus',
        'onblur',
    ];

    /**
     * Checks if an element has attributes that should preserve it.
     *
     * @param ElementInterface $element              The element to check
     * @param list<string>     $preservingAttributes The list of preserving attribute names
     *
     * @return bool True if the element has any preserving attributes
     */
    protected function hasPreservingAttributes(ElementInterface $element, array $preservingAttributes): bool
    {
        foreach ($preservingAttributes as $attr) {
            if ($element->hasAttribute($attr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the default list of preserving attributes.
     *
     * @return list<string>
     */
    protected function getDefaultPreservingAttributes(): array
    {
        return self::DEFAULT_PRESERVING_ATTRIBUTES;
    }
}
