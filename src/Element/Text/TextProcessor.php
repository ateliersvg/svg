<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Text;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\FilterBuilder;
use Atelier\Svg\Element\Builder\GradientBuilder;
use Atelier\Svg\Exception\RuntimeException;
use Atelier\Svg\Geometry\BoundingBox;

/**
 * Utility class for working with SVG text elements.
 *
 * Provides convenient methods for text manipulation, alignment, wrapping,
 * styling, and advanced effects like shadows, gradients, and patterns.
 *
 * Note: Text measurement features (width, height, bounding box) are approximations
 * since accurate text rendering requires a full font rendering engine.
 *
 * Example usage:
 * ```php
 * // Create text with alignment
 * $text = TextHelper::create('Hello World', 100, 50)
 *     ->alignCenter()
 *     ->setFont('Arial', 16)
 *     ->getText();
 *
 * // Wrap text to width
 * TextHelper::wrapText($text, 200);
 *
 * // Add shadow effect (requires Document)
 * $text = TextHelper::create('Shadow Text', 100, 50, $document)
 *     ->setFont('Arial', 32)
 *     ->addShadow(dx: 3, dy: 3, blur: 5, color: '#000', opacity: 0.5)
 *     ->getText();
 *
 * // Add gradient fill (requires Document)
 * $text = TextHelper::create('Gradient Text', 100, 100, $document)
 *     ->setFont('Arial', 48)
 *     ->setGradientFill([
 *         ['color' => '#FF6B6B', 'offset' => 0],
 *         ['color' => '#4ECDC4', 'offset' => 100]
 *     ])
 *     ->getText();
 *
 * // Add outline effect
 * $text = TextHelper::create('Outline Text', 100, 150)
 *     ->setFont('Arial', 32)
 *     ->addOutline('#000', width: 2, fillColor: '#fff')
 *     ->getText();
 * ```
 *
 * @see https://www.w3.org/TR/SVG11/text.html
 */
final class TextProcessor
{
    public function __construct(private readonly TextElement $text, private ?Document $document = null)
    {
    }

    /**
     * Create a new text element with content.
     *
     * @param string        $content  Text content
     * @param float         $x        X coordinate
     * @param float         $y        Y coordinate
     * @param Document|null $document Optional document for advanced features
     */
    public static function create(
        string $content,
        float $x = 0,
        float $y = 0,
        ?Document $document = null,
    ): self {
        $text = new TextElement();
        $text->setPosition($x, $y);
        $text->setTextContent($content);

        return new self($text, $document);
    }

    /**
     * Get the underlying text element.
     */
    public function getText(): TextElement
    {
        return $this->text;
    }

    /**
     * Set font properties.
     *
     * @param string      $family Font family
     * @param int|null    $size   Font size in pixels
     * @param string|null $weight Font weight (normal, bold, etc.)
     * @param string|null $style  Font style (normal, italic, etc.)
     */
    public function setFont(
        string $family,
        ?int $size = null,
        ?string $weight = null,
        ?string $style = null,
    ): self {
        $this->text->setAttribute('font-family', $family);

        if (null !== $size) {
            $this->text->setAttribute('font-size', (string) $size);
        }

        if (null !== $weight) {
            $this->text->setAttribute('font-weight', $weight);
        }

        if (null !== $style) {
            $this->text->setAttribute('font-style', $style);
        }

        return $this;
    }

    /**
     * Set text anchor (horizontal alignment).
     *
     * @param string $anchor start, middle, or end
     */
    public function setTextAnchor(string $anchor): self
    {
        $this->text->setAttribute('text-anchor', $anchor);

        return $this;
    }

    /**
     * Align text to the left (text-anchor: start).
     */
    public function alignLeft(): self
    {
        return $this->setTextAnchor('start');
    }

    /**
     * Align text to the center (text-anchor: middle).
     */
    public function alignCenter(): self
    {
        return $this->setTextAnchor('middle');
    }

    /**
     * Align text to the right (text-anchor: end).
     */
    public function alignRight(): self
    {
        return $this->setTextAnchor('end');
    }

    /**
     * Set vertical alignment using dominant-baseline.
     *
     * @param string $baseline auto, middle, hanging, alphabetic, etc
     */
    public function setDominantBaseline(string $baseline): self
    {
        $this->text->setAttribute('dominant-baseline', $baseline);

        return $this;
    }

    /**
     * Set letter spacing.
     *
     * @param float $spacing Letter spacing value
     */
    public function setLetterSpacing(float $spacing): self
    {
        $this->text->setAttribute('letter-spacing', (string) $spacing);

        return $this;
    }

    /**
     * Set word spacing.
     *
     * @param float $spacing Word spacing value
     */
    public function setWordSpacing(float $spacing): self
    {
        $this->text->setAttribute('word-spacing', (string) $spacing);

        return $this;
    }

    /**
     * Set text decoration.
     *
     * @param string $decoration none, underline, overline, line-through
     */
    public function setTextDecoration(string $decoration): self
    {
        $this->text->setAttribute('text-decoration', $decoration);

        return $this;
    }

    /**
     * Make text follow a path.
     *
     * @param string $pathId  ID of the path to follow
     * @param string $content Optional text content
     */
    public function followPath(string $pathId, ?string $content = null): TextPathElement
    {
        $textPath = new TextPathElement();
        $textPath->setHref("#$pathId");

        if (null !== $content) {
            $textPath->setTextContent($content);
        } else {
            // Move existing text content to textPath
            $existingContent = $this->text->getTextContent();
            if (null !== $existingContent) {
                $textPath->setTextContent($existingContent);
                $this->text->setTextContent('');
            }
        }

        $this->text->appendChild($textPath);

        return $textPath;
    }

    /**
     * Estimate the bounding box of the text.
     *
     * Note: This is a rough approximation. Accurate measurement requires
     * actual font metrics and rendering.
     *
     * @param float $avgCharWidth Average character width multiplier (default: 0.6)
     */
    public function estimateBoundingBox(float $avgCharWidth = 0.6): BoundingBox
    {
        $content = $this->text->getTextContent() ?? '';
        $fontSize = (float) ($this->text->getAttribute('font-size') ?? 16);
        $x = (float) ($this->text->getAttribute('x') ?? 0);
        $y = (float) ($this->text->getAttribute('y') ?? 0);

        // Rough estimation
        $width = mb_strlen($content) * $fontSize * $avgCharWidth;
        $height = $fontSize;

        // Adjust for text-anchor
        $anchor = $this->text->getAttribute('text-anchor') ?? 'start';
        if ('middle' === $anchor) {
            $x -= $width / 2;
        } elseif ('end' === $anchor) {
            $x -= $width;
        }

        // Adjust for baseline (rough approximation)
        $y -= $fontSize * 0.8; // Approximate baseline offset

        return new BoundingBox($x, $y, $width, $height);
    }

    /**
     * Wrap text to fit within a specified width.
     *
     * Creates multiple tspan elements for each line.
     *
     * Note: This uses a simple character-based estimation and may not be
     * accurate for all fonts.
     *
     * @param float $maxWidth     Maximum width in pixels
     * @param float $lineHeight   Line height multiplier (default: 1.2)
     * @param float $avgCharWidth Average character width multiplier (default: 0.6)
     */
    public static function wrapText(
        TextElement $text,
        float $maxWidth,
        float $lineHeight = 1.2,
        float $avgCharWidth = 0.6,
    ): void {
        $content = $text->getTextContent() ?? '';
        $fontSize = (float) ($text->getAttribute('font-size') ?? 16);
        $charWidth = $fontSize * $avgCharWidth;
        $charsPerLine = (int) floor($maxWidth / $charWidth);

        if ($charsPerLine <= 0) {
            return;
        }

        $words = preg_split('/\s+/', $content);
        assert(false !== $words);

        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = '' === $currentLine ? $word : $currentLine.' '.$word;
            if (mb_strlen($testLine) <= $charsPerLine) {
                $currentLine = $testLine;
            } else {
                if ('' !== $currentLine) {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }

        if ('' !== $currentLine) {
            $lines[] = $currentLine;
        }

        // Clear existing text content
        $text->setTextContent('');

        // Create tspan for each line
        $x = $text->getAttribute('x') ?? '0';
        $y = (float) ($text->getAttribute('y') ?? 0);
        $dy = $fontSize * $lineHeight;

        foreach ($lines as $i => $line) {
            $tspan = new TspanElement();
            $tspan->setX($x);
            $tspan->setY($y + ($i * $dy));
            $tspan->setTextContent($line);
            $text->appendChild($tspan);
        }
    }

    /**
     * Wrap text by words to fit within dimensions.
     *
     * @param float      $maxWidth     Maximum width in pixels
     * @param float|null $maxHeight    Maximum height in pixels (null for unlimited)
     * @param float      $lineHeight   Line height multiplier (default: 1.2)
     * @param float      $avgCharWidth Average character width multiplier (default: 0.6)
     */
    public static function wrapWords(
        TextElement $text,
        float $maxWidth,
        ?float $maxHeight = null,
        float $lineHeight = 1.2,
        float $avgCharWidth = 0.6,
    ): void {
        self::wrapText($text, $maxWidth, $lineHeight, $avgCharWidth);

        // If max height is specified, truncate lines that exceed it
        if (null !== $maxHeight) {
            $fontSize = (float) ($text->getAttribute('font-size') ?? 16);
            $dy = $fontSize * $lineHeight;
            $maxLines = (int) floor($maxHeight / $dy);

            $tspans = [];
            foreach ($text->getChildren() as $child) {
                if ($child instanceof TspanElement) {
                    $tspans[] = $child;
                }
            }

            // Remove excess tspans
            for ($i = $maxLines; $i < count($tspans); ++$i) {
                $text->removeChild($tspans[$i]);
            }
        }
    }

    /**
     * Set fill color for text.
     *
     * @param string $color Color value
     */
    public function setFill(string $color): self
    {
        $this->text->setAttribute('fill', $color);

        return $this;
    }

    /**
     * Set stroke for text.
     *
     * @param string     $color Stroke color
     * @param float|null $width Stroke width
     */
    public function setStroke(string $color, ?float $width = null): self
    {
        $this->text->setAttribute('stroke', $color);

        if (null !== $width) {
            $this->text->setAttribute('stroke-width', (string) $width);
        }

        return $this;
    }

    /**
     * Make text bold.
     */
    public function bold(): self
    {
        $this->text->setAttribute('font-weight', 'bold');

        return $this;
    }

    /**
     * Make text italic.
     */
    public function italic(): self
    {
        $this->text->setAttribute('font-style', 'italic');

        return $this;
    }

    /**
     * Underline text.
     */
    public function underline(): self
    {
        return $this->setTextDecoration('underline');
    }

    /**
     * Add a drop shadow effect to the text using SVG filters.
     *
     * This creates a filter element in the document's defs section and applies
     * it to the text element.
     *
     * @param float       $dx       Horizontal shadow offset (default: 2)
     * @param float       $dy       Vertical shadow offset (default: 2)
     * @param float       $blur     Blur amount for the shadow (default: 4)
     * @param string      $color    Shadow color (default: '#000000')
     * @param float       $opacity  Shadow opacity 0-1 (default: 0.3)
     * @param string|null $filterId Custom filter ID (auto-generated if null)
     *
     * @throws RuntimeException If no document is associated
     */
    public function addShadow(
        float $dx = 2,
        float $dy = 2,
        float $blur = 4,
        string $color = '#000000',
        float $opacity = 0.3,
        ?string $filterId = null,
    ): self {
        if (null === $this->document) {
            throw new RuntimeException('Document is required for adding shadow effects. Pass document to TextHelper constructor or create method.');
        }

        $filterId ??= 'text-shadow-'.uniqid();
        $filter = FilterBuilder::createDropShadow(
            $this->document,
            $filterId,
            $dx,
            $dy,
            $blur,
            $color,
            $opacity
        );

        $this->text->setAttribute('filter', "url(#{$filterId})");

        return $this;
    }

    /**
     * Add a glow effect to the text using SVG filters.
     *
     * This creates a filter element in the document's defs section and applies
     * it to the text element.
     *
     * @param string      $color    Glow color (default: '#3b82f6')
     * @param float       $strength Glow strength/blur amount (default: 2)
     * @param float       $opacity  Glow opacity 0-1 (default: 0.8)
     * @param string|null $filterId Custom filter ID (auto-generated if null)
     *
     * @throws RuntimeException If no document is associated
     */
    public function addGlow(
        string $color = '#3b82f6',
        float $strength = 2,
        float $opacity = 0.8,
        ?string $filterId = null,
    ): self {
        if (null === $this->document) {
            throw new RuntimeException('Document is required for adding glow effects. Pass document to TextHelper constructor or create method.');
        }

        $filterId ??= 'text-glow-'.uniqid();
        $filter = FilterBuilder::createGlow(
            $this->document,
            $filterId,
            $color,
            $strength,
            $opacity
        );

        $this->text->setAttribute('filter', "url(#{$filterId})");

        return $this;
    }

    /**
     * Add an outline/stroke effect to the text.
     *
     * This is a simpler alternative to filters that just uses stroke properties.
     *
     * @param string      $color     Outline color
     * @param float       $width     Outline width (default: 1)
     * @param string|null $fillColor Optional fill color (preserves current fill if null)
     */
    public function addOutline(
        string $color,
        float $width = 1,
        ?string $fillColor = null,
    ): self {
        $this->text->setAttribute('stroke', $color);
        $this->text->setAttribute('stroke-width', (string) $width);
        $this->text->setAttribute('stroke-linejoin', 'round');
        $this->text->setAttribute('stroke-linecap', 'round');
        $this->text->setAttribute('paint-order', 'stroke fill');

        if (null !== $fillColor) {
            $this->text->setAttribute('fill', $fillColor);
        }

        return $this;
    }

    /**
     * Apply a linear gradient fill to the text.
     *
     * Creates a linear gradient definition in the document's defs and applies it
     * to the text element.
     *
     * @param array<array{color: string, offset: float|int|string, opacity?: float}> $stops      Gradient stops with color and offset
     * @param float                                                                  $x1         Gradient start x coordinate (0-100 for objectBoundingBox)
     * @param float                                                                  $y1         Gradient start y coordinate (0-100 for objectBoundingBox)
     * @param float                                                                  $x2         Gradient end x coordinate (0-100 for objectBoundingBox)
     * @param float                                                                  $y2         Gradient end y coordinate (0-100 for objectBoundingBox)
     * @param string|null                                                            $gradientId Custom gradient ID (auto-generated if null)
     *
     * @throws RuntimeException If no document is associated
     */
    public function setGradientFill(
        array $stops,
        float $x1 = 0,
        float $y1 = 0,
        float $x2 = 100,
        float $y2 = 0,
        ?string $gradientId = null,
    ): self {
        if (null === $this->document) {
            throw new RuntimeException('Document is required for gradient fills. Pass document to TextHelper constructor or create method.');
        }

        $gradientId ??= 'text-gradient-'.uniqid();
        $helper = GradientBuilder::createLinear($this->document, $gradientId)
            ->from($x1, $y1)
            ->to($x2, $y2)
            ->units('objectBoundingBox');

        foreach ($stops as $stop) {
            $helper->addStop(
                $stop['offset'],
                $stop['color'],
                $stop['opacity'] ?? null
            );
        }

        $helper->addToDefs();

        $this->text->setAttribute('fill', "url(#{$gradientId})");

        return $this;
    }

    /**
     * Apply a radial gradient fill to the text.
     *
     * Creates a radial gradient definition in the document's defs and applies it
     * to the text element.
     *
     * @param array<array{color: string, offset: float|int|string, opacity?: float}> $stops      Gradient stops with color and offset
     * @param float                                                                  $cx         Center x coordinate (0-100 for objectBoundingBox)
     * @param float                                                                  $cy         Center y coordinate (0-100 for objectBoundingBox)
     * @param float                                                                  $r          Radius (0-100 for objectBoundingBox)
     * @param string|null                                                            $gradientId Custom gradient ID (auto-generated if null)
     *
     * @throws RuntimeException If no document is associated
     */
    public function setRadialGradientFill(
        array $stops,
        float $cx = 50,
        float $cy = 50,
        float $r = 50,
        ?string $gradientId = null,
    ): self {
        if (null === $this->document) {
            throw new RuntimeException('Document is required for gradient fills. Pass document to TextHelper constructor or create method.');
        }

        $gradientId ??= 'text-radial-gradient-'.uniqid();
        $helper = GradientBuilder::createRadial($this->document, $gradientId)
            ->center($cx, $cy)
            ->radius($r)
            ->units('objectBoundingBox');

        foreach ($stops as $stop) {
            $helper->addStop(
                $stop['offset'],
                $stop['color'],
                $stop['opacity'] ?? null
            );
        }

        $helper->addToDefs();

        $this->text->setAttribute('fill', "url(#{$gradientId})");

        return $this;
    }

    /**
     * Apply a pattern fill to the text.
     *
     * Applies an existing pattern (by ID) to the text element.
     *
     * @param string $patternId ID of an existing pattern in the document
     */
    public function setPatternFill(string $patternId): self
    {
        $this->text->setAttribute('fill', "url(#{$patternId})");

        return $this;
    }

    /**
     * Set the document context for this helper.
     *
     * This is needed for features that create definitions (gradients, filters, etc.).
     *
     * @param Document $document The document
     */
    public function setDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }
}
