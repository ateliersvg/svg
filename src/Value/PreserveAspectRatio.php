<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an SVG preserveAspectRatio value.
 *
 * Controls how an element's viewBox is mapped to the viewport.
 * Format: <align> [<meetOrSlice>]
 * - align: 'none' or a combination of X and Y alignment (e.g., xMidYMid, xMinYMax)
 * - meetOrSlice: 'meet' (default) or 'slice'
 *
 * @see https://www.w3.org/TR/SVG11/coords.html#PreserveAspectRatioAttribute
 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/preserveAspectRatio
 */
final readonly class PreserveAspectRatio implements \Stringable
{
    // Valid alignment values
    public const string ALIGN_NONE = 'none';

    // X alignment options
    public const string X_MIN = 'xMin';
    public const string X_MID = 'xMid';
    public const string X_MAX = 'xMax';

    // Y alignment options
    public const string Y_MIN = 'YMin';
    public const string Y_MID = 'YMid';
    public const string Y_MAX = 'YMax';

    // Scaling options
    public const string MEET = 'meet';
    public const string SLICE = 'slice';

    // Default value according to SVG spec
    public const string DEFAULT = 'xMidYMid meet';

    private const array VALID_X_ALIGNMENTS = [self::X_MIN, self::X_MID, self::X_MAX];
    private const array VALID_Y_ALIGNMENTS = [self::Y_MIN, self::Y_MID, self::Y_MAX];
    private const array VALID_SCALING = [self::MEET, self::SLICE];

    /**
     * Private constructor, use static factory methods.
     */
    private function __construct(
        private string $align,
        private ?string $xAlign,
        private ?string $yAlign,
        private string $meetOrSlice,
    ) {
    }

    /**
     * Parses a string representation of preserveAspectRatio.
     *
     * @param string|null $value The preserveAspectRatio string to parse
     *
     * @throws InvalidArgumentException if the value is invalid
     */
    public static function parse(?string $value = null): self
    {
        // Handle null or empty input - use default
        if (null === $value || '' === trim($value)) {
            return self::parse(self::DEFAULT);
        }

        $parts = preg_split('/\s+/', trim($value), 2);

        assert(false !== $parts);

        $align = $parts[0];
        $meetOrSlice = $parts[1] ?? self::MEET;

        // Validate meetOrSlice
        if (!in_array($meetOrSlice, self::VALID_SCALING, true)) {
            throw new InvalidArgumentException(sprintf("Invalid meetOrSlice value: '%s'", $meetOrSlice));
        }

        // Handle 'none' align
        if (self::ALIGN_NONE === $align) {
            return new self(self::ALIGN_NONE, null, null, $meetOrSlice);
        }

        // Match X and Y alignment values
        if (preg_match('/^(xMin|xMid|xMax)(YMin|YMid|YMax)$/i', $align, $matches)) {
            $xAlign = $matches[1];
            $yAlign = $matches[2];

            // Case-sensitive validation
            if (!in_array($xAlign, self::VALID_X_ALIGNMENTS, true)
                || !in_array($yAlign, self::VALID_Y_ALIGNMENTS, true)) {
                throw new InvalidArgumentException(sprintf("Invalid alignment value: '%s'", $align));
            }

            return new self($align, $xAlign, $yAlign, $meetOrSlice);
        }

        throw new InvalidArgumentException(sprintf("Invalid preserveAspectRatio format: '%s'", $value));
    }

    /**
     * Creates a preserveAspectRatio with "none" alignment.
     */
    public static function none(string $meetOrSlice = self::MEET): self
    {
        return new self(self::ALIGN_NONE, null, null, $meetOrSlice);
    }

    /**
     * Creates a preserveAspectRatio with specific X and Y alignment.
     */
    public static function fromAlignment(string $xAlign, string $yAlign, string $meetOrSlice = self::MEET): self
    {
        if (!in_array($xAlign, self::VALID_X_ALIGNMENTS, true)) {
            throw new InvalidArgumentException(sprintf("Invalid X alignment: '%s'", $xAlign));
        }

        if (!in_array($yAlign, self::VALID_Y_ALIGNMENTS, true)) {
            throw new InvalidArgumentException(sprintf("Invalid Y alignment: '%s'", $yAlign));
        }

        if (!in_array($meetOrSlice, self::VALID_SCALING, true)) {
            throw new InvalidArgumentException(sprintf("Invalid meetOrSlice: '%s'", $meetOrSlice));
        }

        return new self($xAlign.$yAlign, $xAlign, $yAlign, $meetOrSlice);
    }

    /**
     * Creates the default preserveAspectRatio (xMidYMid meet).
     */
    public static function default(): self
    {
        return self::parse(self::DEFAULT);
    }

    /**
     * Returns true if alignment is 'none'.
     */
    public function isNone(): bool
    {
        return self::ALIGN_NONE === $this->align;
    }

    /**
     * Returns the alignment string.
     */
    public function getAlign(): string
    {
        return $this->align;
    }

    /**
     * Returns the X alignment or null if align is 'none'.
     */
    public function getXAlign(): ?string
    {
        return $this->xAlign;
    }

    /**
     * Returns the Y alignment or null if align is 'none'.
     */
    public function getYAlign(): ?string
    {
        return $this->yAlign;
    }

    /**
     * Returns the meetOrSlice value.
     */
    public function getMeetOrSlice(): string
    {
        return $this->meetOrSlice;
    }

    /**
     * Returns true if the scaling method is 'meet'.
     */
    public function isMeet(): bool
    {
        return self::MEET === $this->meetOrSlice;
    }

    /**
     * Returns true if the scaling method is 'slice'.
     */
    public function isSlice(): bool
    {
        return self::SLICE === $this->meetOrSlice;
    }

    /**
     * Serializes to string format.
     */
    public function toString(): string
    {
        if (self::MEET === $this->meetOrSlice) {
            // 'meet' is the default, so it can be omitted
            return $this->align;
        }

        return $this->align.' '.$this->meetOrSlice;
    }

    /**
     * Magic method for string conversion.
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
