<?php

declare(strict_types=1);

namespace Atelier\Svg\Value\Style;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Value\Style;

/**
 * Fluent helper for managing element styles.
 *
 * Provides a chainable API for setting, getting, and manipulating styles on an element.
 *
 * @example
 * $element->style()
 *     ->set('fill', '#3b82f6')
 *     ->set('stroke', '#1e40af')
 *     ->set('opacity', '0.8')
 *     ->apply();
 */
final readonly class StyleBuilder implements \Stringable
{
    private Style $style;

    public function __construct(
        private AbstractElement $element,
    ) {
        $this->style = $element->getStyle();
    }

    /**
     * Sets a style property.
     */
    public function set(string $property, string $value): self
    {
        $this->style->set($property, $value);

        return $this;
    }

    /**
     * Gets a style property value.
     */
    public function get(string $property): ?string
    {
        return $this->style->get($property);
    }

    /**
     * Removes a style property.
     */
    public function remove(string $property): self
    {
        $this->style->remove($property);

        return $this;
    }

    /**
     * Checks if a property exists.
     */
    public function has(string $property): bool
    {
        return $this->style->has($property);
    }

    /**
     * Sets the fill color.
     */
    public function fill(string $color): self
    {
        return $this->set('fill', $color);
    }

    /**
     * Sets the stroke color.
     */
    public function stroke(string $color): self
    {
        return $this->set('stroke', $color);
    }

    /**
     * Sets the stroke width.
     */
    public function strokeWidth(float|int $width): self
    {
        return $this->set('stroke-width', (string) $width);
    }

    /**
     * Sets the opacity.
     */
    public function opacity(float $opacity): self
    {
        return $this->set('opacity', (string) $opacity);
    }

    /**
     * Sets the fill opacity.
     */
    public function fillOpacity(float $opacity): self
    {
        return $this->set('fill-opacity', (string) $opacity);
    }

    /**
     * Sets the stroke opacity.
     */
    public function strokeOpacity(float $opacity): self
    {
        return $this->set('stroke-opacity', (string) $opacity);
    }

    /**
     * Sets the font family.
     */
    public function fontFamily(string $family): self
    {
        return $this->set('font-family', $family);
    }

    /**
     * Sets the font size.
     */
    public function fontSize(string $size): self
    {
        return $this->set('font-size', $size);
    }

    /**
     * Sets the font weight.
     */
    public function fontWeight(string|int $weight): self
    {
        return $this->set('font-weight', (string) $weight);
    }

    /**
     * Sets the display property.
     */
    public function display(string $value): self
    {
        return $this->set('display', $value);
    }

    /**
     * Sets the visibility property.
     */
    public function visibility(string $value): self
    {
        return $this->set('visibility', $value);
    }

    /**
     * Merges styles from another Style object or array.
     *
     * @param Style|array<string, string> $styles
     */
    public function merge(Style|array $styles): self
    {
        if (is_array($styles)) {
            $styles = Style::fromArray($styles);
        }

        $this->style->merge($styles);

        return $this;
    }

    /**
     * Clears all styles.
     */
    public function clear(): self
    {
        $this->style->clear();

        return $this;
    }

    /**
     * Gets all styles as an array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->style->toArray();
    }

    /**
     * Gets the underlying Style object.
     */
    public function getStyle(): Style
    {
        return $this->style;
    }

    /**
     * Applies the accumulated styles to the element.
     * This writes the style attribute back to the element.
     */
    public function apply(): AbstractElement
    {
        if ($this->style->isEmpty()) {
            $this->element->removeAttribute('style');
        } else {
            $this->element->setAttribute('style', $this->style->toString());
        }

        return $this->element;
    }

    /**
     * Gets the string representation of the styles.
     */
    public function toString(): string
    {
        return $this->style->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
