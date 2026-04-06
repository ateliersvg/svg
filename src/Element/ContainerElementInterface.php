<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

/**
 * Interface for SVG elements that can contain child elements.
 *
 * This interface extends ElementInterface to add child management capabilities.
 * Examples of container elements include: <g>, <svg>, <defs>, <symbol>, etc.
 */
interface ContainerElementInterface extends ElementInterface
{
    /**
     * Adds a child element.
     *
     * @param ElementInterface $child The child element to add
     *
     * @return $this For method chaining
     */
    public function appendChild(ElementInterface $child): static;

    /**
     * Removes a child element.
     *
     * @param ElementInterface $child The child element to remove
     *
     * @return $this For method chaining
     */
    public function removeChild(ElementInterface $child): static;

    /**
     * Gets all child elements.
     *
     * @return array<ElementInterface> The child elements
     */
    public function getChildren(): array;

    /**
     * Checks if this element has any children.
     *
     * @return bool True if the element has children, false otherwise
     */
    public function hasChildren(): bool;

    /**
     * Gets the number of child elements.
     *
     * @return int The number of children
     */
    public function getChildCount(): int;

    /**
     * Removes all child elements.
     *
     * @return $this For method chaining
     */
    public function clearChildren(): static;

    /**
     * Creates a deep copy of the element and its children.
     *
     * @param callable|null $transform Optional callback to transform each cloned element
     *
     * @return static The cloned element with cloned children
     */
    public function cloneDeep(?callable $transform = null): static;
}
