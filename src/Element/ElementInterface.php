<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

/**
 * Base interface for all SVG elements.
 *
 * Defines the fundamental operations that all SVG elements must support,
 * including attribute management, parent tracking, and visitor pattern support.
 */
interface ElementInterface
{
    /**
     * Gets the tag name of this element.
     *
     * @return string The SVG tag name (e.g., 'rect', 'circle', 'path')
     */
    public function getTagName(): string;

    /**
     * Gets the value of an attribute.
     *
     * @param string $name The attribute name
     *
     * @return string|null The attribute value, or null if not set
     */
    public function getAttribute(string $name): ?string;

    /**
     * Sets the value of an attribute.
     *
     * @param string           $name  The attribute name
     * @param string|int|float $value The attribute value
     *
     * @return $this For method chaining
     */
    public function setAttribute(string $name, string|int|float $value): static;

    /**
     * Removes an attribute.
     *
     * @param string $name The attribute name
     *
     * @return $this For method chaining
     */
    public function removeAttribute(string $name): static;

    /**
     * Checks if an attribute exists.
     *
     * @param string $name The attribute name
     *
     * @return bool True if the attribute is set, false otherwise
     */
    public function hasAttribute(string $name): bool;

    /**
     * Gets all attributes as an associative array.
     *
     * @return array<string, string> The attributes
     */
    public function getAttributes(): array;

    /**
     * Gets the parent element.
     *
     * @return ElementInterface|null The parent element, or null if this is the root
     */
    public function getParent(): ?ElementInterface;

    /**
     * Sets the parent element.
     *
     * @param ElementInterface|null $parent The parent element
     *
     * @return $this For method chaining
     */
    public function setParent(?ElementInterface $parent): static;

    /**
     * Sets the id attribute of this element.
     *
     * @param string $id The ID to set
     *
     * @return $this For method chaining
     */
    public function setId(string $id): static;

    /**
     * Gets the id attribute of this element.
     *
     * @return string|null The ID or null if not set
     */
    public function getId(): ?string;

    /**
     * Adds one or more CSS classes to this element.
     *
     * @param string $className Space-separated class names to add
     *
     * @return $this For method chaining
     */
    public function addClass(string $className): static;

    /**
     * Removes one or more CSS classes from this element.
     *
     * @param string $className Space-separated class names to remove
     *
     * @return $this For method chaining
     */
    public function removeClass(string $className): static;

    /**
     * Checks if this element has a specific CSS class.
     *
     * @param string $className The class name to check
     */
    public function hasClass(string $className): bool;

    /**
     * Toggles a CSS class on this element.
     *
     * @param string $className The class name to toggle
     *
     * @return $this For method chaining
     */
    public function toggleClass(string $className): static;

    /**
     * Gets all CSS classes for this element as an array.
     *
     * @return array<string> Array of class names
     */
    public function getClasses(): array;

    /**
     * Creates a shallow copy of the element.
     *
     * @return static The cloned element
     */
    public function clone(): static;
}
