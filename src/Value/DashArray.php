<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an immutable SVG stroke-dasharray value.
 *
 * Parses a list of comma and/or whitespace separated lengths or the keyword 'none'.
 * Ensures values are non-negative numbers.
 * Handles the SVG rule where an odd number of values is repeated to form an even list.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/stroke-dasharray
 */
final readonly class DashArray implements \Stringable
{
    /**
     * Private constructor, use static factory methods.
     *
     * @param list<float> $values   the processed, validated, and potentially doubled list of values
     * @param bool        $isNone   indicates if the original value was 'none'
     * @param string      $rawValue the original string representation
     */
    private function __construct(public array $values, public bool $isNone, public string $rawValue)
    {
    }

    /**
     * Parses a string representation of an SVG stroke-dasharray into a DashArray object.
     * Handles 'none', comma/space separated non-negative numbers.
     *
     * @param string $dashArrayString The string to parse (e.g., "5 10", "5, 10", "5,3,2", "none", "").
     *
     * @throws InvalidArgumentException if the value contains invalid characters or negative numbers
     */
    public static function parse(string $dashArrayString): self
    {
        $trimmedValue = trim($dashArrayString);
        $rawValue = $trimmedValue; // Store original trimmed input

        if ('' === $trimmedValue || 'none' === strtolower($trimmedValue)) {
            return new self([], true, $rawValue); // Treat empty string same as 'none'
        }

        // Split by comma or whitespace, removing empty entries
        $parts = preg_split('/[\s,]+/', $trimmedValue, -1, PREG_SPLIT_NO_EMPTY);

        assert(false !== $parts);

        $values = [];
        foreach ($parts as $part) {
            if (!is_numeric($part)) {
                throw new InvalidArgumentException(sprintf("Invalid non-numeric value '%s' found in dash array: '%s'", $part, $rawValue));
            }
            $value = (float) $part;
            if ($value < 0.0) {
                throw new InvalidArgumentException(sprintf("Negative value '%s' found in dash array: '%s'", $part, $rawValue));
            }
            $values[] = $value;
        }

        // If an odd number of values is provided, the list of values is repeated to yield an even number of values.
        if (0 !== count($values) % 2) {
            $values = array_merge($values, $values);
        }

        return new self($values, false, $rawValue);
    }

    /**
     * Creates a DashArray object from an array of non-negative numbers.
     * Handles the odd number duplication rule.
     *
     * @param list<int|float> $values list of dash/gap lengths
     *
     * @throws InvalidArgumentException if array contains non-numeric or negative values
     */
    public static function fromArray(array $values): self
    {
        $validatedValues = [];
        $rawParts = [];
        foreach ($values as $value) {
            if (!is_int($value) && !is_float($value)) {
                throw new InvalidArgumentException(sprintf("Invalid non-numeric type '%s' found in input array.", gettype($value)));
            }
            $floatValue = (float) $value;
            if ($floatValue < 0.0) {
                throw new InvalidArgumentException(sprintf("Negative value '%s' found in input array.", $floatValue));
            }
            $validatedValues[] = $floatValue;
            $rawParts[] = (string) $floatValue; // For generating rawValue
        }

        $rawValue = implode(',', $rawParts); // Use comma for generated raw value

        // Handle odd number duplication for the effective values
        if (0 !== count($validatedValues) % 2) {
            $validatedValues = array_merge($validatedValues, $validatedValues);
        }

        return new self($validatedValues, false, $rawValue);
    }

    /** Creates a DashArray object representing 'none' (a solid line). */
    public static function none(): self
    {
        // Use canonical 'none' string for raw value
        return new self([], true, 'none');
    }

    /**
     * Gets the effective array of dash/gap lengths (already doubled if originally odd).
     *
     * @return list<float>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /** Returns true if the dash array represents 'none' (a solid line). */
    public function isNone(): bool
    {
        return $this->isNone;
    }

    /** Gets the original string value that was parsed or generated. */
    public function getRawValue(): string
    {
        return $this->rawValue;
    }

    /**
     * Serializes the DashArray object back to its original string representation.
     */
    public function toString(): string
    {
        return $this->rawValue;
    }

    /** Magic method for string conversion. */
    public function __toString(): string
    {
        return $this->toString();
    }
}
