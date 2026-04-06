<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Text;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;

/**
 * Represents an SVG <text> element.
 *
 * The text element defines a graphics element consisting of text.
 * It can contain tspan elements for complex text formatting.
 *
 * @see https://www.w3.org/TR/SVG11/text.html#TextElement
 */
final class TextElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('text');
    }

    /**
     * Creates a new text element at the given position with optional content.
     *
     * @param string|int|float $x       The x coordinate
     * @param string|int|float $y       The y coordinate
     * @param string|null      $content The text content
     */
    public static function create(
        string|int|float $x,
        string|int|float $y,
        ?string $content = null,
    ): static {
        $text = (new static())->setPosition($x, $y);
        if (null !== $content) {
            $text->setTextContent($content);
        }

        return $text;
    }

    /**
     * Sets the x-axis coordinate of the starting point of the text baseline.
     *
     * @param string|int|float $x The x coordinate
     */
    public function setX(string|int|float $x): static
    {
        $this->setAttribute('x', (string) $x);

        return $this;
    }

    /**
     * Gets the x-axis coordinate of the starting point of the text baseline.
     *
     * @return Length|null The x coordinate as a Length object, or null if not set
     */
    public function getX(): ?Length
    {
        $value = $this->getAttribute('x');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the y-axis coordinate of the starting point of the text baseline.
     *
     * @param string|int|float $y The y coordinate
     */
    public function setY(string|int|float $y): static
    {
        $this->setAttribute('y', (string) $y);

        return $this;
    }

    /**
     * Gets the y-axis coordinate of the starting point of the text baseline.
     *
     * @return Length|null The y coordinate as a Length object, or null if not set
     */
    public function getY(): ?Length
    {
        $value = $this->getAttribute('y');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the shift along the x-axis on the current text position.
     *
     * @param string|int|float $dx The x-axis shift
     */
    public function setDx(string|int|float $dx): static
    {
        $this->setAttribute('dx', (string) $dx);

        return $this;
    }

    /**
     * Gets the shift along the x-axis on the current text position.
     *
     * @return string|null The dx value, or null if not set
     */
    public function getDx(): ?string
    {
        return $this->getAttribute('dx');
    }

    /**
     * Sets the shift along the y-axis on the current text position.
     *
     * @param string|int|float $dy The y-axis shift
     */
    public function setDy(string|int|float $dy): static
    {
        $this->setAttribute('dy', (string) $dy);

        return $this;
    }

    /**
     * Gets the shift along the y-axis on the current text position.
     *
     * @return string|null The dy value, or null if not set
     */
    public function getDy(): ?string
    {
        return $this->getAttribute('dy');
    }

    /**
     * Sets the rotation to be applied to each glyph.
     *
     * @param string|int|float $rotate The rotation value(s)
     */
    public function setRotate(string|int|float $rotate): static
    {
        $this->setAttribute('rotate', (string) $rotate);

        return $this;
    }

    /**
     * Gets the rotation to be applied to each glyph.
     *
     * @return string|null The rotate value, or null if not set
     */
    public function getRotate(): ?string
    {
        return $this->getAttribute('rotate');
    }

    /**
     * Sets the width that the text should be rendered into.
     *
     * @param string|int|float $textLength The text length
     */
    public function setTextLength(string|int|float $textLength): static
    {
        $this->setAttribute('textLength', (string) $textLength);

        return $this;
    }

    /**
     * Gets the width that the text should be rendered into.
     *
     * @return Length|null The text length as a Length object, or null if not set
     */
    public function getTextLength(): ?Length
    {
        $value = $this->getAttribute('textLength');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Sets the method for adjusting the spacing and/or glyphs.
     *
     * @param string $lengthAdjust The adjustment method ('spacing' or 'spacingAndGlyphs')
     */
    public function setLengthAdjust(string $lengthAdjust): static
    {
        $this->setAttribute('lengthAdjust', $lengthAdjust);

        return $this;
    }

    /**
     * Gets the method for adjusting the spacing and/or glyphs.
     *
     * @return string|null The length adjustment method, or null if not set
     */
    public function getLengthAdjust(): ?string
    {
        return $this->getAttribute('lengthAdjust');
    }

    /**
     * Sets the position of the text using x and y coordinates.
     *
     * @param string|int|float $x The x coordinate
     * @param string|int|float $y The y coordinate
     */
    public function setPosition(string|int|float $x, string|int|float $y): static
    {
        $this->setX($x);
        $this->setY($y);

        return $this;
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
