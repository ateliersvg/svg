<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an SVG <length> or <coordinate> value.
 *
 * It holds a numeric value and an optional unit identifier (px, em, %, etc.).
 * Unitless values are stored with a null unit. Per SVG spec, in many contexts,
 * unitless values are treated as being in the current user coordinate system
 * (effectively pixels before transformations). Percentages are relative to
 * viewport size (width, height, or diagonal) depending on the attribute.
 * Resolving units like em, ex, % requires context (font-size, viewport)
 * which is typically handled during rendering or layout, not within this
 * simple value object itself.
 *
 * @see https://www.w3.org/TR/SVG11/types.html#DataTypeLength
 * @see https://www.w3.org/TR/SVG2/types.html#InterfaceSVGLength
 */
final readonly class Length implements \Stringable
{
    private const string PARSE_PATTERN = '/^([-+]?\d*\.?\d+(?:[eE][-+]?\d+)?)\s*(em|ex|px|pt|pc|cm|mm|in|%)?$/i';

    private ?string $unit; // null means unitless (interpreted as user unit / px usually)

    /**
     * Private constructor, use the static parse method.
     */
    private function __construct(private float $value, ?string $unit)
    {
        $this->unit = (null === $unit || '' === $unit) ? null : strtolower($unit);
    }

    /**
     * Parses a string representation of an SVG <length> into a Length object.
     * Accepts numbers directly, treating them as unitless lengths.
     *
     * @param string|int|float $inputValue The string to parse (e.g., "10px", "5em", "50%", "10") or a number.
     *
     * @throws InvalidArgumentException if the value cannot be parsed as a valid length
     */
    public static function parse(string|int|float $inputValue): self
    {
        // Handle direct numeric input -> unitless
        if (is_int($inputValue) || is_float($inputValue)) {
            return new self((float) $inputValue, null);
        }

        $trimmedValue = trim((string) $inputValue);
        if ('' === $trimmedValue) {
            throw new InvalidArgumentException('Cannot parse an empty string as a Length.');
        }

        if (preg_match(self::PARSE_PATTERN, $trimmedValue, $matches)) {
            $numberValue = (float) $matches[1];
            // Unit is captured in group 2 if present, otherwise null
            $unitValue = $matches[2] ?? null;

            return new self($numberValue, $unitValue);
        }
        throw new InvalidArgumentException(sprintf("Invalid length format encountered: '%s'", $trimmedValue));
    }

    /**
     * Gets the numeric value component of the length.
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * Gets the unit identifier (e.g., "px", "em", "%") or null if unitless.
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * Checks if this length value is unitless.
     * Unitless lengths often resolve to user units (pixels in many contexts).
     */
    public function isUnitless(): bool
    {
        return null === $this->unit;
    }

    /**
     * Checks if this length value is a percentage.
     * Percentage resolution depends on the context (viewport width, height, etc.).
     */
    public function isPercentage(): bool
    {
        return '%' === $this->unit;
    }

    /**
     * Serializes the Length object back to its standard string representation.
     * Example: 10.5px, 50%, 100.
     */
    public function toString(): string
    {
        // Format number, removing unnecessary trailing zeros after decimal point
        $numberStr = sprintf('%.6F', $this->value); // Use fixed point with 6 decimals

        // Only remove trailing zeros if there's a decimal point
        if (str_contains($numberStr, '.')) {
            $numberStr = rtrim($numberStr, '0');
            $numberStr = rtrim($numberStr, '.');
        }

        if ('-0' === $numberStr) {
            $numberStr = '0';
        }

        return $numberStr.($this->unit ?? ''); // Append unit if it exists
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
