<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Text;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <textPath> element.
 *
 * The textPath element renders text along the shape of a path element.
 * It is typically used within a text element.
 *
 * @see https://www.w3.org/TR/SVG11/text.html#TextPathElement
 */
final class TextPathElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('textPath');
    }

    /**
     * Sets the reference to the path element.
     *
     * @param string $href The reference to the path (usually in the form '#path-id')
     */
    public function setHref(string $href): static
    {
        $this->setAttribute('href', $href);

        return $this;
    }

    /**
     * Gets the reference to the path element.
     *
     * @return string|null The href value, or null if not set
     */
    public function getHref(): ?string
    {
        return $this->getAttribute('href');
    }

    /**
     * Sets the offset along the path where text rendering begins.
     *
     * @param string|int|float $startOffset The start offset
     */
    public function setStartOffset(string|int|float $startOffset): static
    {
        $this->setAttribute('startOffset', (string) $startOffset);

        return $this;
    }

    /**
     * Gets the offset along the path where text rendering begins.
     *
     * @return Length|null The start offset as a Length object, or null if not set
     */
    public function getStartOffset(): ?Length
    {
        $value = $this->getAttribute('startOffset');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the method by which text should be rendered along the path.
     *
     * @param string $method The method ('align' or 'stretch')
     */
    public function setMethod(string $method): static
    {
        $this->setAttribute('method', $method);

        return $this;
    }

    /**
     * Gets the method by which text should be rendered along the path.
     *
     * @return string|null The method value, or null if not set
     */
    public function getMethod(): ?string
    {
        return $this->getAttribute('method');
    }

    /**
     * Sets the spacing method for glyphs.
     *
     * @param string $spacing The spacing method ('auto' or 'exact')
     */
    public function setSpacing(string $spacing): static
    {
        $this->setAttribute('spacing', $spacing);

        return $this;
    }

    /**
     * Gets the spacing method for glyphs.
     *
     * @return string|null The spacing value, or null if not set
     */
    public function getSpacing(): ?string
    {
        return $this->getAttribute('spacing');
    }

    /**
     * Sets the text content of this element.
     *
     * @param string $content The text content
     */
    public function setTextContent(string $content): static
    {
        $this->setAttribute('textContent', $content);

        return $this;
    }

    /**
     * Gets the text content of this element.
     *
     * @return string|null The text content, or null if not set
     */
    public function getTextContent(): ?string
    {
        return $this->getAttribute('textContent');
    }

    /**
     * Sets the baseline shift for superscript/subscript positioning.
     *
     * @param string|int|float $shift The baseline shift (e.g., 'super', 'sub', '5px', '50%')
     */
    public function setBaselineShift(string|int|float $shift): static
    {
        $this->setAttribute('baseline-shift', (string) $shift);

        return $this;
    }

    /**
     * Gets the baseline shift value.
     *
     * @return string|null The baseline shift value, or null if not set
     */
    public function getBaselineShift(): ?string
    {
        return $this->getAttribute('baseline-shift');
    }

    /**
     * Sets the letter spacing (spacing between characters).
     *
     * @param string|int|float $spacing The letter spacing (e.g., '2px', '0.1em')
     */
    public function setLetterSpacing(string|int|float $spacing): static
    {
        $this->setAttribute('letter-spacing', (string) $spacing);

        return $this;
    }

    /**
     * Gets the letter spacing value.
     *
     * @return string|null The letter spacing value, or null if not set
     */
    public function getLetterSpacing(): ?string
    {
        return $this->getAttribute('letter-spacing');
    }

    /**
     * Sets the word spacing (spacing between words).
     *
     * @param string|int|float $spacing The word spacing (e.g., '5px', '0.2em')
     */
    public function setWordSpacing(string|int|float $spacing): static
    {
        $this->setAttribute('word-spacing', (string) $spacing);

        return $this;
    }

    /**
     * Gets the word spacing value.
     *
     * @return string|null The word spacing value, or null if not set
     */
    public function getWordSpacing(): ?string
    {
        return $this->getAttribute('word-spacing');
    }

    /**
     * Sets the text decoration (underline, overline, line-through).
     *
     * @param string $decoration The text decoration (e.g., 'underline', 'overline', 'line-through', 'none')
     */
    public function setTextDecoration(string $decoration): static
    {
        $this->setAttribute('text-decoration', $decoration);

        return $this;
    }

    /**
     * Gets the text decoration value.
     *
     * @return string|null The text decoration value, or null if not set
     */
    public function getTextDecoration(): ?string
    {
        return $this->getAttribute('text-decoration');
    }

    /**
     * Sets the font size.
     *
     * @param string|int|float $size The font size (e.g., '16px', '1.2em', 14)
     */
    public function setFontSize(string|int|float $size): static
    {
        $this->setAttribute('font-size', (string) $size);

        return $this;
    }

    /**
     * Sets the font family.
     *
     * @param string $family The font family (e.g., 'Arial', 'sans-serif')
     */
    public function setFontFamily(string $family): static
    {
        $this->setAttribute('font-family', $family);

        return $this;
    }

    /**
     * Sets the font weight.
     *
     * @param string|int $weight The font weight (e.g., 'bold', 'normal', 700)
     */
    public function setFontWeight(string|int $weight): static
    {
        $this->setAttribute('font-weight', (string) $weight);

        return $this;
    }

    /**
     * Sets the font style.
     *
     * @param string $style The font style (e.g., 'normal', 'italic', 'oblique')
     */
    public function setFontStyle(string $style): static
    {
        $this->setAttribute('font-style', $style);

        return $this;
    }

    /**
     * Sets the text anchor alignment.
     *
     * @param string $anchor The text anchor (e.g., 'start', 'middle', 'end')
     */
    public function setTextAnchor(string $anchor): static
    {
        $this->setAttribute('text-anchor', $anchor);

        return $this;
    }

    /**
     * Sets the dominant baseline alignment.
     *
     * @param string $baseline The dominant baseline (e.g., 'auto', 'middle', 'hanging', 'central')
     */
    public function setDominantBaseline(string $baseline): static
    {
        $this->setAttribute('dominant-baseline', $baseline);

        return $this;
    }
}
