<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes attributes with empty or whitespace-only values.
 *
 * This pass removes attributes that have no meaningful value (empty string or
 * only whitespace). Some attributes are preserved even when empty because an
 * empty value may be meaningful (e.g., alt="" for accessibility).
 */
final class RemoveEmptyAttrsPass extends AbstractOptimizerPass
{
    /** @var array<string> Default attributes to preserve even when empty */
    private const array DEFAULT_PRESERVE_ATTRS = [
        'alt',          // Empty alt is meaningful for accessibility
        'role',         // ARIA role attribute
        'aria-label',   // ARIA label
    ];

    /** @var array<string> */
    private readonly array $preserveAttrs;

    /**
     * Creates a new RemoveEmptyAttrsPass.
     *
     * @param array<string>|null $preserveAttrs Attributes to preserve even when empty (null for defaults)
     */
    public function __construct(?array $preserveAttrs = null)
    {
        $this->preserveAttrs = $preserveAttrs ?? self::DEFAULT_PRESERVE_ATTRS;
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-empty-attrs';
    }

    /**
     * Processes an element to remove empty attributes.
     *
     * @param ElementInterface $element The element to process
     */
    protected function processElement(ElementInterface $element): void
    {
        // Remove empty attributes from this element
        $this->removeEmptyAttributes($element);
    }

    /**
     * Removes empty attributes from an element.
     *
     * @param ElementInterface $element The element to process
     */
    private function removeEmptyAttributes(ElementInterface $element): void
    {
        $attributes = $element->getAttributes();

        foreach ($attributes as $name => $value) {
            // Skip attributes in the preserve list
            if (in_array($name, $this->preserveAttrs, true)) {
                continue;
            }

            // Remove if empty or only whitespace
            if ($this->isEmptyValue($value)) {
                $element->removeAttribute($name);
            }
        }
    }

    /**
     * Checks if a value is considered empty.
     *
     * A value is empty if it's an empty string or contains only whitespace.
     *
     * @param string $value The value to check
     *
     * @return bool True if the value is empty
     */
    private function isEmptyValue(string $value): bool
    {
        return '' === trim($value);
    }
}
