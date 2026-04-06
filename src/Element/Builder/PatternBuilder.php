<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Gradient\PatternElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Exception\RuntimeException;

/**
 * Utility class for creating and managing SVG patterns with a fluent API.
 *
 * Provides convenient methods for creating patterns with common shapes
 * and configurations. Patterns can be used to fill or stroke elements
 * with repeating graphics.
 *
 * Example usage:
 * ```php
 * // Create a dots pattern
 * $pattern = PatternBuilder::create($doc, 'dots-pattern')
 *     ->size(10, 10)
 *     ->addCircle(5, 5, 2, '#3b82f6')
 *     ->addToDefs()
 *     ->getPattern();
 *
 * // Apply to element
 * $element->setAttribute('fill', 'url(#dots-pattern)');
 *
 * // Or use preset patterns
 * PatternBuilder::createDots($doc, 'dots', 10, 2, '#3b82f6');
 * PatternBuilder::createStripes($doc, 'stripes', 10, 2, '#8b5cf6');
 * PatternBuilder::createCheckerboard($doc, 'checker', 20, '#000', '#fff');
 * ```
 *
 * @see https://www.w3.org/TR/SVG11/pservers.html#PatternElement
 */
final class PatternBuilder
{
    private ?DefsElement $defs = null;

    public function __construct(private readonly Document $document, private readonly PatternElement $pattern)
    {
    }

    /**
     * Create a new pattern with fluent API.
     *
     * @param Document $document The document to add the pattern to
     * @param string   $id       The pattern ID
     */
    public static function create(Document $document, string $id): self
    {
        $pattern = new PatternElement();
        $pattern->setId($id);

        return new self($document, $pattern);
    }

    /**
     * Add the pattern to the document's defs section.
     */
    public function addToDefs(): self
    {
        $defs = $this->getOrCreateDefs();
        $defs->appendChild($this->pattern);

        return $this;
    }

    /**
     * Get the underlying pattern element.
     */
    public function getPattern(): PatternElement
    {
        return $this->pattern;
    }

    /**
     * Set the size of the pattern tile.
     *
     * @param string|int|float $width  Pattern width
     * @param string|int|float $height Pattern height
     */
    public function size(string|int|float $width, string|int|float $height): self
    {
        $this->pattern->setWidth($width);
        $this->pattern->setHeight($height);

        return $this;
    }

    /**
     * Set the position of the pattern tile.
     *
     * @param string|int|float $x X coordinate
     * @param string|int|float $y Y coordinate
     */
    public function position(string|int|float $x, string|int|float $y): self
    {
        $this->pattern->setX($x);
        $this->pattern->setY($y);

        return $this;
    }

    /**
     * Set the bounds (position and size) of the pattern tile.
     *
     * @param string|int|float $x      X coordinate
     * @param string|int|float $y      Y coordinate
     * @param string|int|float $width  Pattern width
     * @param string|int|float $height Pattern height
     */
    public function bounds(
        string|int|float $x,
        string|int|float $y,
        string|int|float $width,
        string|int|float $height,
    ): self {
        $this->pattern->setBounds($x, $y, $width, $height);

        return $this;
    }

    /**
     * Set the pattern units (coordinate system for x, y, width, height).
     *
     * @param string $units 'userSpaceOnUse' or 'objectBoundingBox' (default)
     */
    public function units(string $units): self
    {
        $this->pattern->setPatternUnits($units);

        return $this;
    }

    /**
     * Set the pattern content units (coordinate system for pattern contents).
     *
     * @param string $units 'userSpaceOnUse' (default) or 'objectBoundingBox'
     */
    public function contentUnits(string $units): self
    {
        $this->pattern->setPatternContentUnits($units);

        return $this;
    }

    /**
     * Set the pattern transform.
     *
     * @param string $transform Transform string
     */
    public function transform(string $transform): self
    {
        $this->pattern->setPatternTransform($transform);

        return $this;
    }

    /**
     * Set the viewBox for the pattern.
     *
     * @param float $minX   Minimum X
     * @param float $minY   Minimum Y
     * @param float $width  ViewBox width
     * @param float $height ViewBox height
     */
    public function viewBox(float $minX, float $minY, float $width, float $height): self
    {
        $this->pattern->setViewBox("{$minX} {$minY} {$width} {$height}");

        return $this;
    }

    /**
     * Add a child element to the pattern.
     *
     * @param ElementInterface $element The element to add
     */
    public function addElement(ElementInterface $element): self
    {
        $this->pattern->appendChild($element);

        return $this;
    }

    /**
     * Add a rectangle to the pattern.
     *
     * @param float       $x      X coordinate
     * @param float       $y      Y coordinate
     * @param float       $width  Rectangle width
     * @param float       $height Rectangle height
     * @param string|null $fill   Fill color
     * @param string|null $stroke Stroke color
     */
    public function addRect(
        float $x,
        float $y,
        float $width,
        float $height,
        ?string $fill = null,
        ?string $stroke = null,
    ): self {
        $rect = new RectElement();
        $rect->setX($x);
        $rect->setY($y);
        $rect->setWidth($width);
        $rect->setHeight($height);

        if (null !== $fill) {
            $rect->setAttribute('fill', $fill);
        }

        if (null !== $stroke) {
            $rect->setAttribute('stroke', $stroke);
        }

        $this->pattern->appendChild($rect);

        return $this;
    }

    /**
     * Add a circle to the pattern.
     *
     * @param float       $cx     Center X coordinate
     * @param float       $cy     Center Y coordinate
     * @param float       $r      Radius
     * @param string|null $fill   Fill color
     * @param string|null $stroke Stroke color
     */
    public function addCircle(
        float $cx,
        float $cy,
        float $r,
        ?string $fill = null,
        ?string $stroke = null,
    ): self {
        $circle = new CircleElement();
        $circle->setCx($cx);
        $circle->setCy($cy);
        $circle->setR($r);

        if (null !== $fill) {
            $circle->setAttribute('fill', $fill);
        }

        if (null !== $stroke) {
            $circle->setAttribute('stroke', $stroke);
        }

        $this->pattern->appendChild($circle);

        return $this;
    }

    /**
     * Create a dots pattern.
     *
     * @param Document    $document        The document
     * @param string      $id              Pattern ID
     * @param float       $spacing         Spacing between dots
     * @param float       $dotSize         Dot radius
     * @param string      $color           Dot color
     * @param string|null $backgroundColor Optional background color
     */
    public static function createDots(
        Document $document,
        string $id,
        float $spacing = 10,
        float $dotSize = 2,
        string $color = '#000000',
        ?string $backgroundColor = null,
    ): PatternElement {
        $helper = self::create($document, $id)
            ->size($spacing, $spacing);

        if (null !== $backgroundColor) {
            $helper->addRect(0, 0, $spacing, $spacing, $backgroundColor);
        }

        $helper->addCircle($spacing / 2, $spacing / 2, $dotSize, $color)
            ->addToDefs();

        return $helper->getPattern();
    }

    /**
     * Create a horizontal stripes pattern.
     *
     * @param Document    $document        The document
     * @param string      $id              Pattern ID
     * @param float       $height          Total height of one stripe cycle
     * @param float       $stripeWidth     Width of the colored stripe
     * @param string      $color           Stripe color
     * @param string|null $backgroundColor Optional background color
     */
    public static function createStripes(
        Document $document,
        string $id,
        float $height = 10,
        float $stripeWidth = 5,
        string $color = '#000000',
        ?string $backgroundColor = null,
    ): PatternElement {
        $helper = self::create($document, $id)
            ->size($height, $height);

        if (null !== $backgroundColor) {
            $helper->addRect(0, 0, $height, $height, $backgroundColor);
        }

        $helper->addRect(0, 0, $height, $stripeWidth, $color)
            ->addToDefs();

        return $helper->getPattern();
    }

    /**
     * Create a diagonal stripes pattern.
     *
     * @param Document    $document        The document
     * @param string      $id              Pattern ID
     * @param float       $size            Size of one stripe cycle
     * @param float       $stripeWidth     Width of the colored stripe
     * @param string      $color           Stripe color
     * @param string|null $backgroundColor Optional background color
     */
    public static function createDiagonalStripes(
        Document $document,
        string $id,
        float $size = 20,
        float $stripeWidth = 10,
        string $color = '#000000',
        ?string $backgroundColor = null,
    ): PatternElement {
        $helper = self::create($document, $id)
            ->size($size, $size)
            ->units('userSpaceOnUse');

        if (null !== $backgroundColor) {
            $helper->addRect(0, 0, $size, $size, $backgroundColor);
        }

        // Create diagonal stripe using transformed rectangle
        $rect = new RectElement();
        $rect->setX(-$size / 2);
        $rect->setY(0);
        $rect->setWidth($size * 2);
        $rect->setHeight($stripeWidth);
        $rect->setAttribute('fill', $color);
        $rect->setAttribute('transform', "rotate(45 {$size} {$size})");

        $helper->addElement($rect)->addToDefs();

        return $helper->getPattern();
    }

    /**
     * Create a checkerboard pattern.
     *
     * @param Document $document   The document
     * @param string   $id         Pattern ID
     * @param float    $squareSize Size of each square
     * @param string   $color1     First color
     * @param string   $color2     Second color
     */
    public static function createCheckerboard(
        Document $document,
        string $id,
        float $squareSize = 20,
        string $color1 = '#000000',
        string $color2 = '#ffffff',
    ): PatternElement {
        $patternSize = $squareSize * 2;

        $helper = self::create($document, $id)
            ->size($patternSize, $patternSize);

        // Background
        $helper->addRect(0, 0, $patternSize, $patternSize, $color1);

        // Two squares in checkerboard pattern
        $helper->addRect($squareSize, 0, $squareSize, $squareSize, $color2);
        $helper->addRect(0, $squareSize, $squareSize, $squareSize, $color2);

        $helper->addToDefs();

        return $helper->getPattern();
    }

    /**
     * Create a grid pattern.
     *
     * @param Document    $document        The document
     * @param string      $id              Pattern ID
     * @param float       $size            Grid cell size
     * @param float       $lineWidth       Grid line width
     * @param string      $color           Grid line color
     * @param string|null $backgroundColor Optional background color
     */
    public static function createGrid(
        Document $document,
        string $id,
        float $size = 20,
        float $lineWidth = 1,
        string $color = '#cccccc',
        ?string $backgroundColor = null,
    ): PatternElement {
        $helper = self::create($document, $id)
            ->size($size, $size);

        if (null !== $backgroundColor) {
            $helper->addRect(0, 0, $size, $size, $backgroundColor);
        }

        // Horizontal line
        $helper->addRect(0, 0, $size, $lineWidth, $color);

        // Vertical line
        $helper->addRect(0, 0, $lineWidth, $size, $color);

        $helper->addToDefs();

        return $helper->getPattern();
    }

    /**
     * Create a crosshatch pattern.
     *
     * @param Document    $document        The document
     * @param string      $id              Pattern ID
     * @param float       $size            Pattern size
     * @param float       $lineWidth       Line width
     * @param string      $color           Line color
     * @param string|null $backgroundColor Optional background color
     */
    public static function createCrosshatch(
        Document $document,
        string $id,
        float $size = 20,
        float $lineWidth = 1,
        string $color = '#000000',
        ?string $backgroundColor = null,
    ): PatternElement {
        $helper = self::create($document, $id)
            ->size($size, $size)
            ->units('userSpaceOnUse');

        if (null !== $backgroundColor) {
            $helper->addRect(0, 0, $size, $size, $backgroundColor);
        }

        // Diagonal line 1
        $rect1 = new RectElement();
        $rect1->setX(-$size / 2);
        $rect1->setY($size / 2 - $lineWidth / 2);
        $rect1->setWidth($size * 2);
        $rect1->setHeight($lineWidth);
        $rect1->setAttribute('fill', $color);
        $rect1->setAttribute('transform', "rotate(45 {$size} {$size})");

        // Diagonal line 2
        $rect2 = new RectElement();
        $rect2->setX(-$size / 2);
        $rect2->setY($size / 2 - $lineWidth / 2);
        $rect2->setWidth($size * 2);
        $rect2->setHeight($lineWidth);
        $rect2->setAttribute('fill', $color);
        $rect2->setAttribute('transform', "rotate(-45 {$size} {$size})");

        $helper->addElement($rect1)
            ->addElement($rect2)
            ->addToDefs();

        return $helper->getPattern();
    }

    /**
     * Get or create the defs element.
     */
    private function getOrCreateDefs(): DefsElement
    {
        if (null !== $this->defs) {
            return $this->defs;
        }

        $root = $this->document->getRootElement();
        if (null === $root) {
            throw new RuntimeException('Document has no root element');
        }

        // Look for existing defs
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                $this->defs = $child;

                return $this->defs;
            }
        }

        // Create new defs as first child
        $this->defs = new DefsElement();
        $root->prependChild($this->defs);

        return $this->defs;
    }
}
