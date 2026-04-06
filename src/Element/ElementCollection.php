<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

/**
 * Collection of SVG elements with fluent filtering and batch operations.
 *
 * Provides a chainable interface for working with multiple elements at once,
 * similar to jQuery or other DOM manipulation libraries.
 *
 * @implements \IteratorAggregate<int, ElementInterface>
 */
final class ElementCollection implements \IteratorAggregate, \Countable
{
    /** @param array<ElementInterface> $elements */
    public function __construct(private array $elements = [])
    {
    }

    // ========================================================================
    // Array Access Methods
    // ========================================================================

    /**
     * Gets an element by index.
     */
    public function get(int $index): ?ElementInterface
    {
        return $this->elements[$index] ?? null;
    }

    /**
     * Gets the first element in the collection.
     */
    public function first(): ?ElementInterface
    {
        return $this->elements[0] ?? null;
    }

    /**
     * Gets the last element in the collection.
     */
    public function last(): ?ElementInterface
    {
        $count = count($this->elements);

        return $count > 0 ? $this->elements[$count - 1] : null;
    }

    /**
     * Converts the collection to an array.
     *
     * @return array<ElementInterface>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * Gets the number of elements in the collection.
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Checks if the collection is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Gets an iterator for the collection.
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    // ========================================================================
    // Filtering Methods
    // ========================================================================

    /**
     * Filters elements using a callback.
     *
     * @param callable $callback Function that receives an element and returns true to keep it
     */
    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->elements, $callback)));
    }

    /**
     * Filters elements by excluding those that match the callback (opposite of filter).
     *
     * @param callable $callback Function that receives an element and returns true to reject it
     */
    public function reject(callable $callback): self
    {
        return new self(array_values(array_filter($this->elements, fn ($el) => !$callback($el))));
    }

    /**
     * Filters elements where an attribute matches a value.
     *
     * @param string                     $attribute The attribute name
     * @param string                     $operator  Comparison operator: '=', '!=', '>', '<', '>=', '<=', 'contains'
     * @param string|int|float|bool|null $value     The value to compare against
     */
    public function where(string $attribute, string $operator, string|int|float|bool|null $value): self
    {
        return $this->filter(function (ElementInterface $element) use ($attribute, $operator, $value) {
            $attrValue = $element->getAttribute($attribute);

            return match ($operator) {
                '=' => $attrValue === $value,
                '!=' => $attrValue !== $value,
                '>' => null !== $attrValue && (float) $attrValue > (float) $value,
                '<' => null !== $attrValue && (float) $attrValue < (float) $value,
                '>=' => null !== $attrValue && (float) $attrValue >= (float) $value,
                '<=' => null !== $attrValue && (float) $attrValue <= (float) $value,
                'contains' => null !== $attrValue && str_contains((string) $attrValue, (string) $value),
                default => false,
            };
        });
    }

    /**
     * Filters to elements with a specific tag name.
     */
    public function ofType(string $tagName): self
    {
        return $this->filter(fn (ElementInterface $el) => $el->getTagName() === $tagName);
    }

    /**
     * Filters to elements that have a specific class.
     */
    public function withClass(string $className): self
    {
        return $this->filter(fn (ElementInterface $el) => $el->hasClass($className));
    }

    /**
     * Filters to elements that have a specific attribute.
     */
    public function withAttribute(string $attribute): self
    {
        return $this->filter(fn (ElementInterface $el) => $el->hasAttribute($attribute));
    }

    // ========================================================================
    // Mapping Methods
    // ========================================================================

    /**
     * Maps elements to new values using a callback.
     *
     * @return array<mixed>
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->elements);
    }

    /**
     * Extracts values of a specific attribute from all elements.
     *
     * @return array<mixed>
     */
    public function pluck(string $attribute): array
    {
        return $this->map(fn (ElementInterface $el) => $el->getAttribute($attribute));
    }

    /**
     * Reduces the collection to a single value using a callback.
     *
     * @param mixed $initial Initial value for the reduction
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->elements, $callback, $initial);
    }

    // ========================================================================
    // Iteration Methods
    // ========================================================================

    /**
     * Executes a callback for each element in the collection.
     */
    public function each(callable $callback): self
    {
        foreach ($this->elements as $index => $element) {
            $callback($element, $index);
        }

        return $this;
    }

    // ========================================================================
    // Batch Attribute Operations
    // ========================================================================

    /**
     * Sets an attribute on all elements in the collection.
     */
    public function setAttribute(string $name, string|int|float $value): self
    {
        foreach ($this->elements as $element) {
            $element->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * Alias for setAttribute() for more concise syntax.
     */
    public function attr(string $name, string|int|float $value): self
    {
        return $this->setAttribute($name, $value);
    }

    /**
     * Removes an attribute from all elements in the collection.
     */
    public function removeAttribute(string $name): self
    {
        foreach ($this->elements as $element) {
            $element->removeAttribute($name);
        }

        return $this;
    }

    // ========================================================================
    // Batch Class Operations
    // ========================================================================

    /**
     * Adds a class to all elements in the collection.
     */
    public function addClass(string $className): self
    {
        foreach ($this->elements as $element) {
            $element->addClass($className);
        }

        return $this;
    }

    /**
     * Removes a class from all elements in the collection.
     */
    public function removeClass(string $className): self
    {
        foreach ($this->elements as $element) {
            $element->removeClass($className);
        }

        return $this;
    }

    /**
     * Toggles a class on all elements in the collection.
     */
    public function toggleClass(string $className): self
    {
        foreach ($this->elements as $element) {
            $element->toggleClass($className);
        }

        return $this;
    }

    // ========================================================================
    // Batch Style Operations (Convenience Methods)
    // ========================================================================

    /**
     * Sets the fill attribute on all elements.
     */
    public function fill(string $color): self
    {
        return $this->setAttribute('fill', $color);
    }

    /**
     * Sets the stroke attribute on all elements.
     */
    public function stroke(string $color): self
    {
        return $this->setAttribute('stroke', $color);
    }

    /**
     * Sets the stroke-width attribute on all elements.
     */
    public function strokeWidth(int|float $width): self
    {
        return $this->setAttribute('stroke-width', (string) $width);
    }

    /**
     * Sets the opacity attribute on all elements.
     */
    public function opacity(float $opacity): self
    {
        return $this->setAttribute('opacity', (string) $opacity);
    }

    /**
     * Sets the transform attribute on all elements.
     */
    public function transform(string $transform): self
    {
        return $this->setAttribute('transform', $transform);
    }

    /**
     * Sets the fill-opacity attribute on all elements.
     */
    public function fillOpacity(float $opacity): self
    {
        return $this->setAttribute('fill-opacity', (string) $opacity);
    }

    /**
     * Sets the stroke-opacity attribute on all elements.
     */
    public function strokeOpacity(float $opacity): self
    {
        return $this->setAttribute('stroke-opacity', (string) $opacity);
    }

    /**
     * Sets the display attribute on all elements.
     */
    public function display(string $display): self
    {
        return $this->setAttribute('display', $display);
    }

    /**
     * Sets the visibility attribute on all elements.
     */
    public function visibility(string $visibility): self
    {
        return $this->setAttribute('visibility', $visibility);
    }

    /**
     * Sets the cursor attribute on all elements.
     */
    public function cursor(string $cursor): self
    {
        return $this->setAttribute('cursor', $cursor);
    }

    /**
     * Sets the pointer-events attribute on all elements.
     */
    public function pointerEvents(string $pointerEvents): self
    {
        return $this->setAttribute('pointer-events', $pointerEvents);
    }

    // ========================================================================
    // Batch DOM Operations
    // ========================================================================

    /**
     * Removes all elements in the collection from their parents.
     */
    public function remove(): self
    {
        foreach ($this->elements as $element) {
            $parent = $element->getParent();
            if ($parent instanceof ContainerElementInterface) {
                $parent->removeChild($element);
            }
        }

        return $this;
    }

    /**
     * Clones all elements in the collection.
     *
     * @return self A new collection containing clones
     */
    public function clone(): self
    {
        $clones = array_map(fn (ElementInterface $el) => $el->clone(), $this->elements);

        return new self($clones);
    }

    /**
     * Deep clones all elements in the collection (including children).
     *
     * @return self A new collection containing deep clones
     */
    public function cloneDeep(): self
    {
        $clones = array_map(function (ElementInterface $el) {
            if ($el instanceof ContainerElementInterface) {
                return $el->cloneDeep();
            }

            return $el->clone();
        }, $this->elements);

        return new self($clones);
    }
}
