<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

/**
 * Abstract base class for SVG container elements that can have children.
 * Extends AbstractElement and adds child management functionality.
 */
abstract class AbstractContainerElement extends AbstractElement implements ContainerElementInterface
{
    /** @var array<ElementInterface> Child elements */
    private array $children = [];

    /**
     * Adds a child element to this container.
     *
     * @param ElementInterface $child The child element to add
     */
    public function appendChild(ElementInterface $child): static
    {
        $this->children[] = $child;
        $child->setParent($this);

        return $this;
    }

    /**
     * Adds a child element to the beginning of this container's children.
     *
     * @param ElementInterface $child The child element to prepend
     */
    public function prependChild(ElementInterface $child): static
    {
        array_unshift($this->children, $child);
        $child->setParent($this);

        return $this;
    }

    /**
     * Removes a child element from this container.
     *
     * @param ElementInterface $child The child element to remove
     */
    public function removeChild(ElementInterface $child): static
    {
        $key = array_search($child, $this->children, true);
        if (false !== $key) {
            unset($this->children[$key]);
            $this->children = array_values($this->children); // Re-index
            $child->setParent(null);
        }

        return $this;
    }

    /**
     * Gets all child elements.
     *
     * @return array<ElementInterface> Array of child elements
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Checks if this container has any children.
     *
     * @return bool True if the container has children, false otherwise
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    /**
     * Gets the number of child elements.
     *
     * @return int The number of children
     */
    public function getChildCount(): int
    {
        return count($this->children);
    }

    /**
     * Removes all child elements.
     */
    public function clearChildren(): static
    {
        foreach ($this->children as $child) {
            $child->setParent(null);
        }
        $this->children = [];

        return $this;
    }

    /**
     * Creates a deep clone of this element, including all children.
     * Each child is recursively cloned.
     *
     * @param callable|null $transform Optional callback to transform each cloned element
     */
    public function cloneDeep(?callable $transform = null): static
    {
        // First, clone this element (shallow)
        $clone = $this->clone();

        // Then clone all children
        foreach ($this->getChildren() as $child) {
            $childClone = $child instanceof ContainerElementInterface
                ? $child->cloneDeep($transform)
                : $child->clone();

            if (null !== $transform) {
                $childClone = $transform($childClone) ?? $childClone;
            }

            if ($childClone instanceof ElementInterface) {
                $clone->appendChild($childClone);
            }
        }

        return $clone;
    }

    // ========================================
    // Layout Methods
    // ========================================

    /**
     * Get a layout helper for arranging children within this container.
     */
    public function layout(): \Atelier\Svg\Layout\LayoutBuilder
    {
        return new \Atelier\Svg\Layout\LayoutBuilder($this);
    }
}
