<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Builder;

use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TspanElement;

/**
 * Builder utility for creating tspan elements with gap control.
 *
 * This class provides an easy way to add multiple tspan elements to a text element
 * with automatic positioning and gap control between spans.
 *
 * Example:
 * ```php
 * $text = new TextElement();
 * $text->setPosition(10, 20);
 *
 * $builder = new TspanBuilder($text);
 * $builder->add('Hello', 0, ['fill' => '#000'])
 *         ->add('World', 10, ['fill' => '#f00'])
 *         ->add('!', 5, ['fill' => '#00f']);
 * ```
 */
final class TspanBuilder
{
    private float $currentX = 0;
    private float $currentY = 0;
    private float $defaultGap = 0; // Rough estimate for character width

    public function __construct(private readonly TextElement $textElement, private float $avgCharWidth = 8.0)
    {
        // Initialize position from text element
        $x = $this->textElement->getX();
        $y = $this->textElement->getY();
        $this->currentX = $x ? $x->getValue() : 0;
        $this->currentY = $y ? $y->getValue() : 0;
    }

    /**
     * Sets the default gap to use for subsequent tspan elements.
     *
     * @param float $gap The default gap in pixels
     */
    public function setDefaultGap(float $gap): static
    {
        $this->defaultGap = $gap;

        return $this;
    }

    /**
     * Gets the default gap.
     *
     * @return float The default gap in pixels
     */
    public function getDefaultGap(): float
    {
        return $this->defaultGap;
    }

    /**
     * Sets the average character width for estimation.
     *
     * @param float $width The average character width in pixels
     */
    public function setAvgCharWidth(float $width): static
    {
        $this->avgCharWidth = $width;

        return $this;
    }

    /**
     * Gets the current X position.
     *
     * @return float The current X position
     */
    public function getCurrentX(): float
    {
        return $this->currentX;
    }

    /**
     * Gets the current Y position.
     *
     * @return float The current Y position
     */
    public function getCurrentY(): float
    {
        return $this->currentY;
    }

    /**
     * Adds a tspan element with optional gap and styles.
     *
     * @param string               $content The text content for the tspan
     * @param float|null           $gap     The gap before this tspan (uses default if null, or 0 for first tspan)
     * @param array<string, mixed> $styles  Attributes to apply to the tspan
     */
    public function add(string $content, ?float $gap = null, array $styles = []): static
    {
        // If gap is null, use default gap (but 0 for first tspan)
        if (null === $gap) {
            $gap = $this->textElement->hasChildren() ? $this->defaultGap : 0;
        }

        $tspan = new TspanElement();
        $tspan->setTextContent($content);

        // Set position with gap
        $tspan->setX($this->currentX + $gap);
        $tspan->setY($this->currentY);

        // Apply styles
        foreach ($styles as $attr => $value) {
            if (is_scalar($value) || $value instanceof \Stringable) {
                $tspan->setAttribute($attr, (string) $value);
            }
        }

        $this->textElement->appendChild($tspan);

        // Update current position for next tspan
        // Estimate the width of this content
        $estimatedWidth = strlen($content) * $this->avgCharWidth;
        $this->currentX += $gap + $estimatedWidth;

        return $this;
    }

    /**
     * Adds a tspan at a specific absolute position.
     *
     * @param string               $content The text content for the tspan
     * @param float                $x       The absolute X position
     * @param float|null           $y       The absolute Y position (uses current Y if null)
     * @param array<string, mixed> $styles  Attributes to apply to the tspan
     */
    public function addAt(string $content, float $x, ?float $y = null, array $styles = []): static
    {
        $tspan = new TspanElement();
        $tspan->setTextContent($content);
        $tspan->setX($x);
        $tspan->setY($y ?? $this->currentY);

        // Apply styles
        foreach ($styles as $attr => $value) {
            if (is_scalar($value) || $value instanceof \Stringable) {
                $tspan->setAttribute($attr, (string) $value);
            }
        }

        $this->textElement->appendChild($tspan);

        // Update current position
        $estimatedWidth = strlen($content) * $this->avgCharWidth;
        $this->currentX = $x + $estimatedWidth;
        if (null !== $y) {
            $this->currentY = $y;
        }

        return $this;
    }

    /**
     * Adds multiple tspans distributed evenly across a total width.
     *
     * @param array<string>        $contents     Array of text content strings
     * @param float                $totalWidth   Total width to distribute spans across
     * @param array<string, mixed> $commonStyles Common styles to apply to all tspans
     */
    public function distributeEvenly(array $contents, float $totalWidth, array $commonStyles = []): static
    {
        $count = count($contents);
        if (0 === $count) {
            return $this;
        }

        $startX = $this->currentX;
        $spacing = $count > 1 ? $totalWidth / ($count - 1) : 0;

        foreach ($contents as $index => $content) {
            $x = $startX + ($index * $spacing);
            $this->addAt($content, $x, null, $commonStyles);
        }

        return $this;
    }

    /**
     * Adds multiple tspans stacked vertically with specified line height.
     *
     * @param array<string>        $contents     Array of text content strings
     * @param float                $lineHeight   Line height in pixels
     * @param array<string, mixed> $commonStyles Common styles to apply to all tspans
     */
    public function stackVertically(array $contents, float $lineHeight, array $commonStyles = []): static
    {
        $startX = $this->textElement->getX()?->getValue() ?? 0;
        $startY = $this->currentY;

        foreach ($contents as $index => $content) {
            $y = $startY + ($index * $lineHeight);
            $this->addAt($content, $startX, $y, $commonStyles);
        }

        return $this;
    }

    /**
     * Resets the current position to the text element's original position.
     */
    public function reset(): static
    {
        $x = $this->textElement->getX();
        $y = $this->textElement->getY();
        $this->currentX = $x ? $x->getValue() : 0;
        $this->currentY = $y ? $y->getValue() : 0;

        return $this;
    }

    /**
     * Gets the underlying text element.
     *
     * @return TextElement The text element being built
     */
    public function getTextElement(): TextElement
    {
        return $this->textElement;
    }
}
