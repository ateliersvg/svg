<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an SVG <angle> value.
 *
 * Stores the angle internally in radians for easier mathematical operations,
 * but can parse degrees (deg, unitless), radians (rad), and gradians (grad).
 * Unitless input values are treated as degrees per the SVG specification.
 *
 * @see https://www.w3.org/TR/SVG11/types.html#DataTypeAngle
 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Content_type#angle
 */
final readonly class Angle implements \Stringable
{
    // Valid SVG angle units
    private const array VALID_UNITS = ['deg', 'rad', 'grad'];
    private const string UNIT_DEG = 'deg';
    private const string UNIT_RAD = 'rad';
    private const string UNIT_GRAD = 'grad';

    /**
     * Private constructor, use static factory methods.
     */
    private function __construct(
        private float $radians,
        private float $originalValue,
        private ?string $originalUnit,
    ) {
    }

    /**
     * Parses a string representation of an SVG <angle> into an Angle object.
     * Unitless numbers are interpreted as degrees.
     *
     * @param string|int|float $inputValue The string (e.g., "45deg", "1.57rad", "45") or number to parse.
     *
     * @throws InvalidArgumentException if the value cannot be parsed as a valid angle
     */
    public static function parse(string|int|float $inputValue): self
    {
        // Handle direct numeric input -> treat as degrees
        if (is_int($inputValue) || is_float($inputValue)) {
            $degrees = (float) $inputValue;

            return new self(deg2rad($degrees), $degrees, null);
        }

        $trimmedValue = trim((string) $inputValue);
        if ('' === $trimmedValue) {
            throw new InvalidArgumentException('Cannot parse empty string as Angle.');
        }

        // Regex: Optional sign, number (float/scientific notation), optional known unit
        $pattern = '/^([-+]?\d*\.?\d+(?:[eE][-+]?\d+)?)\s*([a-z]+)?$/i';

        if (preg_match($pattern, $trimmedValue, $matches)) {
            $numberValue = (float) $matches[1];
            // $matches[2] may be undefined or empty string
            $unit = $matches[2] ?? '';

            if ('' === $unit) {
                $unit = null;
            }

            if (null !== $unit && !in_array(strtolower($unit), self::VALID_UNITS, true)) {
                throw new InvalidArgumentException(sprintf("Invalid angle unit: '%s'", $unit));
            }

            $unitLower = null !== $unit ? strtolower($unit) : null;

            return match ($unitLower) {
                self::UNIT_RAD => new self($numberValue, $numberValue, self::UNIT_RAD),
                self::UNIT_GRAD => new self($numberValue * M_PI / 200.0, $numberValue, self::UNIT_GRAD),
                // Default case covers 'deg' and null (unitless)
                default => new self(deg2rad($numberValue), $numberValue, $unit),
            };
        }

        throw new InvalidArgumentException(sprintf("Invalid angle format: '%s'", $trimmedValue));
    }

    /** Creates an Angle object from a value in degrees. */
    public static function fromDegrees(float $degrees): self
    {
        return new self(deg2rad($degrees), $degrees, self::UNIT_DEG);
    }

    /** Creates an Angle object from a value in radians. */
    public static function fromRadians(float $radians): self
    {
        return new self($radians, $radians, self::UNIT_RAD);
    }

    /** Creates an Angle object from a value in gradians. */
    public static function fromGradians(float $gradians): self
    {
        return new self($gradians * M_PI / 200.0, $gradians, self::UNIT_GRAD);
    }

    public function toRadians(): float
    {
        return $this->radians;
    }

    public function toDegrees(): float
    {
        return rad2deg($this->radians);
    }

    public function toGradians(): float
    {
        return $this->radians * 200.0 / M_PI;
    }

    /** Gets the original numeric value that was parsed. */
    public function getOriginalValue(): float
    {
        return $this->originalValue;
    }

    /** Gets the original unit ('deg', 'rad', 'grad') or null if unitless/degrees was implied. */
    public function getOriginalUnit(): ?string
    {
        return $this->originalUnit;
    }

    /**
     * Formats a numeric value to a clean string representation.
     */
    private function formatNumber(float $value): string
    {
        // Check for "integer-like" floats to avoid scientific notation for small/large ints
        if (floor($value) === $value && !is_infinite($value) && !is_nan($value) && abs($value) < 1e15) {
            return (string) (int) $value;
        }

        $numberStr = sprintf('%.6F', $value);
        $numberStr = rtrim($numberStr, '0');

        return '-0' === $numberStr ? '0' : rtrim($numberStr, '.');
    }

    /**
     * Serializes the Angle object back to a string representation.
     * Preserves original unit if rad/grad, omits deg unit for consistency with SVG.
     */
    public function toString(): string
    {
        if (in_array($this->originalUnit, [self::UNIT_RAD, self::UNIT_GRAD], true)) {
            return $this->formatNumber($this->originalValue).$this->originalUnit;
        }

        return $this->formatNumber($this->toDegrees());
    }

    /** Magic method for string conversion. */
    public function __toString(): string
    {
        return $this->toString();
    }
}
