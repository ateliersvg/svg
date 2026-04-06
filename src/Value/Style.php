<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

/**
 * Represents inline CSS styles as found in the style attribute.
 *
 * Parses and manipulates inline style declarations like "fill: red; stroke: blue".
 */
final class Style implements \Stringable
{
    /**
     * @param array<string, string> $properties
     */
    private function __construct(private array $properties = [])
    {
    }

    /**
     * Parses an inline style string.
     *
     * @param string|null $value The style attribute value (e.g., "fill: red; stroke: blue")
     */
    public static function parse(?string $value): self
    {
        if (null === $value || '' === trim($value)) {
            return new self([]);
        }

        $properties = [];
        $declarations = explode(';', $value);

        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if ('' === $declaration) {
                continue;
            }

            $parts = explode(':', $declaration, 2);
            if (2 === count($parts)) {
                $property = trim($parts[0]);
                $propertyValue = trim($parts[1]);
                $properties[$property] = $propertyValue;
            }
        }

        return new self($properties);
    }

    /**
     * Creates a Style from an array of properties.
     *
     * @param array<string, string> $properties
     */
    public static function fromArray(array $properties): self
    {
        return new self($properties);
    }

    /**
     * Gets a style property value.
     */
    public function get(string $property): ?string
    {
        return $this->properties[$property] ?? null;
    }

    /**
     * Sets a style property.
     */
    public function set(string $property, string $value): self
    {
        $this->properties[$property] = $value;

        return $this;
    }

    /**
     * Removes a style property.
     */
    public function remove(string $property): self
    {
        unset($this->properties[$property]);

        return $this;
    }

    /**
     * Checks if a property exists.
     */
    public function has(string $property): bool
    {
        return isset($this->properties[$property]);
    }

    /**
     * Gets all style properties.
     *
     * @return array<string, string>
     */
    public function getAll(): array
    {
        return $this->properties;
    }

    /**
     * Exports style properties as an array.
     * Alias for getAll() for consistency with action plan API.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->properties;
    }

    /**
     * Merges another style into this one.
     * Properties in $other will override existing properties.
     */
    public function merge(self $other): self
    {
        $this->properties = array_merge($this->properties, $other->properties);

        return $this;
    }

    /**
     * Clears all style properties.
     */
    public function clear(): self
    {
        $this->properties = [];

        return $this;
    }

    /**
     * Checks if the style is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->properties);
    }

    /**
     * Serializes the style to a string.
     */
    public function toString(): string
    {
        if (empty($this->properties)) {
            return '';
        }

        $declarations = [];
        foreach ($this->properties as $property => $value) {
            $declarations[] = "{$property}: {$value}";
        }

        return implode('; ', $declarations);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Creates a copy of this style.
     */
    public function copy(): self
    {
        return new self($this->properties);
    }
}
