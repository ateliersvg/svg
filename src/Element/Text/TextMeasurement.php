<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Text;

use Atelier\Svg\Geometry\BoundingBox;

/**
 * Advanced text measurement and layout utilities for SVG text elements.
 *
 * Provides improved text measurement capabilities beyond basic estimation,
 * including character-specific metrics, line breaking algorithms, and
 * text fitting utilities.
 *
 * Note: These measurements are approximations since accurate text rendering
 * requires actual font metrics. The utilities use common font metric ratios
 * and character width tables for better accuracy than simple estimation.
 *
 * Example usage:
 * ```php
 * // Measure text with improved accuracy
 * $metrics = TextMeasurement::measure($text, 'Arial', 16);
 *
 * // Fit text to a bounding box
 * TextMeasurement::fitToBox($text, 200, 100);
 *
 * // Break text into lines with justification
 * $lines = TextMeasurement::breakLines($content, 200, 'Arial', 16);
 *
 * // Calculate optimal font size to fit
 * $fontSize = TextMeasurement::calculateFitSize($content, 200, 100);
 * ```
 *
 * @see https://www.w3.org/TR/SVG11/text.html
 */
final readonly class TextMeasurement
{
    /**
     * Character width ratios for common fonts (relative to font size).
     * Based on typical proportions for sans-serif fonts.
     */
    private const array CHAR_WIDTHS = [
        // Narrow characters
        'i' => 0.25, 'j' => 0.25, 'l' => 0.25, 't' => 0.3, 'f' => 0.3,
        'I' => 0.25, 'J' => 0.5,
        '!' => 0.25, '.' => 0.25, ',' => 0.25, ':' => 0.25, ';' => 0.25,
        '\'' => 0.2, '`' => 0.2,

        // Wide characters
        'm' => 0.9, 'w' => 0.85,
        'M' => 0.9, 'W' => 0.9,
        '@' => 1.0,

        // Medium-wide characters
        'A' => 0.7, 'B' => 0.65, 'C' => 0.7, 'D' => 0.7, 'E' => 0.6,
        'F' => 0.55, 'G' => 0.75, 'H' => 0.7, 'K' => 0.65, 'L' => 0.55,
        'N' => 0.7, 'O' => 0.75, 'P' => 0.6, 'Q' => 0.75, 'R' => 0.65,
        'S' => 0.65, 'T' => 0.6, 'U' => 0.7, 'V' => 0.65, 'X' => 0.65,
        'Y' => 0.6, 'Z' => 0.6,

        // Lowercase
        'a' => 0.55, 'b' => 0.6, 'c' => 0.5, 'd' => 0.6, 'e' => 0.55,
        'g' => 0.6, 'h' => 0.6, 'k' => 0.55, 'n' => 0.6, 'o' => 0.6,
        'p' => 0.6, 'q' => 0.6, 'r' => 0.4, 's' => 0.5, 'u' => 0.6,
        'v' => 0.55, 'x' => 0.55, 'y' => 0.55, 'z' => 0.5,

        // Digits
        '0' => 0.6, '1' => 0.6, '2' => 0.6, '3' => 0.6, '4' => 0.6,
        '5' => 0.6, '6' => 0.6, '7' => 0.6, '8' => 0.6, '9' => 0.6,

        // Space
        ' ' => 0.3,
    ];

    /**
     * Default character width ratio for characters not in the table.
     */
    private const float DEFAULT_CHAR_WIDTH = 0.6;

    /**
     * Font-specific multipliers for more accurate measurements.
     */
    private const array FONT_MULTIPLIERS = [
        'monospace' => 0.6,
        'courier' => 0.6,
        'courier new' => 0.6,
        'arial' => 0.55,
        'helvetica' => 0.55,
        'verdana' => 0.6,
        'times' => 0.5,
        'times new roman' => 0.5,
        'georgia' => 0.55,
    ];

    /**
     * Default font multiplier for unknown fonts.
     */
    private const float DEFAULT_FONT_MULTIPLIER = 0.55;

    /**
     * Text metrics result.
     *
     * @property float       $width       Estimated text width
     * @property float       $height      Text height (based on font size and line height)
     * @property float       $baseline    Distance from top to baseline
     * @property int         $lines       Number of lines
     * @property BoundingBox $boundingBox The bounding box
     */
    public BoundingBox $boundingBox;

    public function __construct(
        public float $width,
        public float $height,
        public float $baseline,
        public int $lines = 1,
        public float $x = 0,
        public float $y = 0,
    ) {
        // Calculate bounding box based on anchor (default: baseline at y)
        $this->boundingBox = new BoundingBox(
            $x,
            $y - $baseline,
            $x + $width,
            $y - $baseline + $height
        );
    }

    /**
     * Measure text using improved character-based metrics.
     *
     * @param TextElement $text       The text element to measure
     * @param string|null $fontFamily Optional font family override
     * @param float|null  $fontSize   Optional font size override
     * @param float       $lineHeight Line height multiplier (default: 1.2)
     */
    public static function measure(
        TextElement $text,
        ?string $fontFamily = null,
        ?float $fontSize = null,
        float $lineHeight = 1.2,
    ): self {
        $content = $text->getTextContent() ?? '';
        $fontSize ??= (float) ($text->getAttribute('font-size') ?? 16);
        $fontFamily ??= $text->getAttribute('font-family') ?? 'sans-serif';

        $width = self::calculateTextWidth($content, $fontFamily, $fontSize);
        $height = $fontSize;
        $baseline = $fontSize * 0.8; // Approximation

        $x = (float) ($text->getAttribute('x') ?? 0);
        $y = (float) ($text->getAttribute('y') ?? 0);

        return new self($width, $height, $baseline, 1, $x, $y);
    }

    /**
     * Calculate text width using character-specific metrics.
     *
     * @param string $text       The text to measure
     * @param string $fontFamily Font family name
     * @param float  $fontSize   Font size in pixels
     */
    public static function calculateTextWidth(
        string $text,
        string $fontFamily = 'sans-serif',
        float $fontSize = 16,
    ): float {
        $width = 0;
        $fontMultiplier = self::getFontMultiplier($fontFamily);

        $chars = mb_str_split($text);
        foreach ($chars as $char) {
            $charWidth = self::CHAR_WIDTHS[$char] ?? self::DEFAULT_CHAR_WIDTH;
            $width += $charWidth * $fontSize * $fontMultiplier;
        }

        return $width;
    }

    /**
     * Get font-specific multiplier for width calculations.
     */
    private static function getFontMultiplier(string $fontFamily): float
    {
        $family = strtolower(trim($fontFamily));

        return self::FONT_MULTIPLIERS[$family] ?? self::DEFAULT_FONT_MULTIPLIER;
    }

    /**
     * Break text into lines that fit within a maximum width.
     *
     * @param string $text       Text to break
     * @param float  $maxWidth   Maximum width in pixels
     * @param string $fontFamily Font family name
     * @param float  $fontSize   Font size in pixels
     * @param bool   $breakWords Whether to break words if necessary
     *
     * @return array<string> Array of lines
     */
    public static function breakLines(
        string $text,
        float $maxWidth,
        string $fontFamily = 'sans-serif',
        float $fontSize = 16,
        bool $breakWords = false,
    ): array {
        $words = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        assert(false !== $words);

        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine.$word;
            $testWidth = self::calculateTextWidth($testLine, $fontFamily, $fontSize);

            if ($testWidth <= $maxWidth || '' === $currentLine) {
                $currentLine = $testLine;
            } else {
                // Line would be too long
                if ($breakWords && !preg_match('/^\s+$/', $word)) {
                    // Break the word if necessary
                    if ('' !== $currentLine) {
                        $lines[] = $currentLine;
                        $currentLine = '';
                    }

                    // Add word character by character
                    $chars = mb_str_split($word);
                    foreach ($chars as $char) {
                        $testLine = $currentLine.$char;
                        $testWidth = self::calculateTextWidth($testLine, $fontFamily, $fontSize);

                        if ($testWidth <= $maxWidth || '' === $currentLine) {
                            $currentLine = $testLine;
                        } else {
                            $lines[] = $currentLine;
                            $currentLine = $char;
                        }
                    }
                } else {
                    // Start new line
                    if ('' !== $currentLine) {
                        $lines[] = $currentLine;
                    }
                    $currentLine = preg_match('/^\s+$/', $word) ? '' : $word;
                }
            }
        }

        if ('' !== $currentLine) {
            $lines[] = $currentLine;
        }

        return array_filter($lines, fn ($line) => '' !== trim((string) $line));
    }

    /**
     * Calculate the optimal font size to fit text within dimensions.
     *
     * @param string $text       Text to fit
     * @param float  $maxWidth   Maximum width
     * @param float  $maxHeight  Maximum height
     * @param string $fontFamily Font family name
     * @param float  $lineHeight Line height multiplier
     * @param float  $minSize    Minimum font size to try
     * @param float  $maxSize    Maximum font size to try
     *
     * @return float Optimal font size
     */
    public static function calculateFitSize(
        string $text,
        float $maxWidth,
        float $maxHeight,
        string $fontFamily = 'sans-serif',
        float $lineHeight = 1.2,
        float $minSize = 8,
        float $maxSize = 72,
    ): float {
        // Binary search for optimal size
        $low = $minSize;
        $high = $maxSize;
        $bestSize = $minSize;

        while ($high - $low > 0.5) {
            $mid = ($low + $high) / 2;
            $lines = self::breakLines($text, $maxWidth, $fontFamily, $mid);
            $totalHeight = count($lines) * $mid * $lineHeight;

            if ($totalHeight <= $maxHeight) {
                $bestSize = $mid;
                $low = $mid;
            } else {
                $high = $mid;
            }
        }

        return $bestSize;
    }

    /**
     * Fit text element to a bounding box by adjusting font size.
     *
     * @param TextElement $text       Text element to modify
     * @param float       $maxWidth   Maximum width
     * @param float       $maxHeight  Maximum height
     * @param float       $lineHeight Line height multiplier
     */
    public static function fitToBox(
        TextElement $text,
        float $maxWidth,
        float $maxHeight,
        float $lineHeight = 1.2,
    ): void {
        $content = $text->getTextContent() ?? '';
        $fontFamily = $text->getAttribute('font-family') ?? 'sans-serif';

        $fontSize = self::calculateFitSize(
            $content,
            $maxWidth,
            $maxHeight,
            $fontFamily,
            $lineHeight
        );

        $text->setAttribute('font-size', (string) round($fontSize, 2));

        // Wrap text if needed
        $lines = self::breakLines($content, $maxWidth, $fontFamily, $fontSize);

        if (count($lines) > 1) {
            TextProcessor::wrapText($text, $maxWidth, $lineHeight);
        }
    }

    /**
     * Justify text to fill a specific width.
     *
     * Calculates letter spacing needed to justify text to exact width.
     *
     * @param string $text        Text to justify
     * @param float  $targetWidth Target width in pixels
     * @param string $fontFamily  Font family name
     * @param float  $fontSize    Font size in pixels
     *
     * @return float Letter spacing value to achieve target width
     */
    public static function calculateJustification(
        string $text,
        float $targetWidth,
        string $fontFamily = 'sans-serif',
        float $fontSize = 16,
    ): float {
        $currentWidth = self::calculateTextWidth($text, $fontFamily, $fontSize);
        $charCount = max(1, mb_strlen($text) - 1); // Spaces between characters

        return ($targetWidth - $currentWidth) / $charCount;
    }

    /**
     * Measure multi-line text with line height.
     *
     * @param array<string> $lines      Array of text lines
     * @param string        $fontFamily Font family name
     * @param float         $fontSize   Font size in pixels
     * @param float         $lineHeight Line height multiplier
     * @param float         $x          X coordinate
     * @param float         $y          Y coordinate
     */
    public static function measureMultiLine(
        array $lines,
        string $fontFamily = 'sans-serif',
        float $fontSize = 16,
        float $lineHeight = 1.2,
        float $x = 0,
        float $y = 0,
    ): self {
        $maxWidth = 0;
        foreach ($lines as $line) {
            $width = self::calculateTextWidth($line, $fontFamily, $fontSize);
            $maxWidth = max($maxWidth, $width);
        }

        $height = count($lines) * $fontSize * $lineHeight;
        $baseline = $fontSize * 0.8;

        return new self($maxWidth, $height, $baseline, count($lines), $x, $y);
    }

    /**
     * Calculate baseline position for vertical alignment.
     *
     * @param string $alignment       Alignment type: 'top', 'middle', 'bottom', 'baseline'
     * @param float  $fontSize        Font size in pixels
     * @param float  $containerHeight Container height for alignment
     *
     * @return float Baseline offset from container top
     */
    public static function calculateBaselineOffset(
        string $alignment,
        float $fontSize,
        float $containerHeight,
    ): float {
        return match ($alignment) {
            'top' => $fontSize * 0.8, // Place baseline near top
            'middle' => $containerHeight / 2 + $fontSize * 0.3, // Visual center
            'bottom' => $containerHeight - $fontSize * 0.2, // Place baseline near bottom
            'baseline' => $fontSize * 0.8, // Default baseline
            default => $fontSize * 0.8,
        };
    }

    /**
     * Estimate text width for a single line (fast approximation).
     *
     * @param string $text         Text to measure
     * @param float  $fontSize     Font size in pixels
     * @param float  $avgCharWidth Average character width ratio (default: 0.55)
     */
    public static function estimateWidth(
        string $text,
        float $fontSize = 16,
        float $avgCharWidth = 0.55,
    ): float {
        return mb_strlen($text) * $fontSize * $avgCharWidth;
    }

    /**
     * Check if text fits within dimensions without wrapping.
     *
     * @param string $text       Text to check
     * @param float  $maxWidth   Maximum width
     * @param string $fontFamily Font family name
     * @param float  $fontSize   Font size in pixels
     */
    public static function fitsInWidth(
        string $text,
        float $maxWidth,
        string $fontFamily = 'sans-serif',
        float $fontSize = 16,
    ): bool {
        $width = self::calculateTextWidth($text, $fontFamily, $fontSize);

        return $width <= $maxWidth;
    }
}
