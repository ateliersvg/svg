<?php

declare(strict_types=1);

namespace Atelier\Svg\Selector;

use Atelier\Svg\Element\ElementInterface;

/**
 * Matches SVG elements against CSS-like selectors.
 *
 * Supports basic selectors:
 * - ID: #myId
 * - Class: .myClass
 * - Tag: rect, circle, path
 * - Attribute: [attr="value"]
 * - Universal: *
 */
final class SelectorMatcher
{
    /**
     * Matches an element against a selector string.
     */
    public function matches(ElementInterface $element, string $selector): bool
    {
        $selector = trim($selector);

        // Universal selector
        if ('*' === $selector) {
            return true;
        }

        // ID selector: #myId
        if (str_starts_with($selector, '#')) {
            $id = substr($selector, 1);

            return $element->getAttribute('id') === $id;
        }

        // Class selector: .myClass
        if (str_starts_with($selector, '.')) {
            $className = substr($selector, 1);

            return $element->hasClass($className);
        }

        // Attribute selector: [attr], [attr="value"], [attr^="value"], [attr$="value"], [attr*="value"]
        if (preg_match('/^\[([a-zA-Z0-9\-:]+)(?:([~|^$*]?=)"([^"]*)")?\]$/', $selector, $matches)) {
            return $this->matchesAttribute($element, $matches[1], $matches[2] ?? null, $matches[3] ?? null);
        }

        // Tag selector: rect, circle, etc.
        return $element->getTagName() === $selector;
    }

    /**
     * Matches an element against an attribute selector.
     */
    private function matchesAttribute(
        ElementInterface $element,
        string $attribute,
        ?string $operator,
        ?string $value,
    ): bool {
        $attrValue = $element->getAttribute($attribute);

        // [attr] - just check existence
        if (null === $operator) {
            return null !== $attrValue;
        }

        // If attribute doesn't exist, no match
        if (null === $attrValue || null === $value) {
            return false;
        }

        return match ($operator) {
            '=' => $attrValue === $value,           // [attr="value"] - exact match
            '~=' => $this->matchesWordInList($attrValue, $value), // [attr~="value"] - word in space-separated list
            '|=' => $this->matchesDashPrefix($attrValue, $value), // [attr|="value"] - exact or dash-prefixed
            '^=' => str_starts_with($attrValue, $value), // [attr^="value"] - starts with
            '$=' => str_ends_with($attrValue, $value),   // [attr$="value"] - ends with
            '*=' => str_contains($attrValue, $value),    // [attr*="value"] - contains
            default => false,
        };
    }

    /**
     * Checks if a word appears in a space-separated list.
     */
    private function matchesWordInList(string $list, string $word): bool
    {
        $words = preg_split('/\s+/', trim($list));
        assert(false !== $words);

        return in_array($word, $words, true);
    }

    /**
     * Checks if value is exactly the given string or starts with it followed by a dash.
     */
    private function matchesDashPrefix(string $value, string $prefix): bool
    {
        return $value === $prefix || str_starts_with($value, $prefix.'-');
    }
}
