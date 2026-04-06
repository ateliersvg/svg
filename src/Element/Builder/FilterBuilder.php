<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Filter\FeBlendElement;
use Atelier\Svg\Element\Filter\FeColorMatrixElement;
use Atelier\Svg\Element\Filter\FeCompositeElement;
use Atelier\Svg\Element\Filter\FeFloodElement;
use Atelier\Svg\Element\Filter\FeGaussianBlurElement;
use Atelier\Svg\Element\Filter\FeOffsetElement;
use Atelier\Svg\Element\Filter\FilterElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Exception\RuntimeException;

/**
 * Utility class for creating and managing SVG filters with a fluent API.
 *
 * Provides convenient methods for creating common filter effects like blur,
 * drop shadows, glows, and color transformations.
 *
 * Example usage:
 * ```php
 * // Create a blur filter
 * $filter = FilterBuilder::create($doc, 'blur-effect')
 *     ->gaussianBlur(5)
 *     ->getFilter();
 *
 * // Create a drop shadow
 * $filter = FilterBuilder::createDropShadow($doc, 'shadow', 2, 2, 4, '#000', 0.3);
 *
 * // Apply filter to element
 * $element->setAttribute('filter', 'url(#blur-effect)');
 * ```
 *
 * @see https://www.w3.org/TR/SVG2/filters.html
 */
final class FilterBuilder
{
    private ?DefsElement $defs = null;

    public function __construct(private readonly Document $document, private readonly FilterElement $filter)
    {
    }

    /**
     * Create a new filter with fluent API.
     *
     * @param Document $document The document to add the filter to
     * @param string   $id       The filter ID
     */
    public static function create(Document $document, string $id): self
    {
        $filter = new FilterElement();
        $filter->setId($id);

        return new self($document, $filter);
    }

    /**
     * Add the filter to the document's defs section.
     */
    public function addToDefs(): self
    {
        $defs = $this->getOrCreateDefs();
        $defs->appendChild($this->filter);

        return $this;
    }

    /**
     * Get the underlying filter element.
     */
    public function getFilter(): FilterElement
    {
        return $this->filter;
    }

    /**
     * Add a Gaussian blur effect.
     *
     * @param float       $stdDeviation Blur amount (typical: 1-10)
     * @param string|null $in           Input for the filter (default: SourceGraphic)
     * @param string|null $result       Result identifier
     */
    public function gaussianBlur(
        float $stdDeviation,
        ?string $in = null,
        ?string $result = null,
    ): self {
        $blur = new FeGaussianBlurElement();
        $blur->setStdDeviation($stdDeviation);

        if (null !== $in) {
            $blur->setIn($in);
        }

        if (null !== $result) {
            $blur->setResult($result);
        }

        $this->filter->appendChild($blur);

        return $this;
    }

    /**
     * Add an offset effect.
     *
     * @param float       $dx     Horizontal offset
     * @param float       $dy     Vertical offset
     * @param string|null $in     Input for the filter
     * @param string|null $result Result identifier
     */
    public function offset(
        float $dx,
        float $dy,
        ?string $in = null,
        ?string $result = null,
    ): self {
        $offset = new FeOffsetElement();
        $offset->setDx($dx);
        $offset->setDy($dy);

        if (null !== $in) {
            $offset->setIn($in);
        }

        if (null !== $result) {
            $offset->setResult($result);
        }

        $this->filter->appendChild($offset);

        return $this;
    }

    /**
     * Add a color matrix transformation.
     *
     * @param string            $type   Type: 'matrix', 'saturate', 'hueRotate', 'luminanceToAlpha'
     * @param string|float|null $values Values for the transformation
     * @param string|null       $in     Input for the filter
     * @param string|null       $result Result identifier
     */
    public function colorMatrix(
        string $type,
        string|float|null $values = null,
        ?string $in = null,
        ?string $result = null,
    ): self {
        $colorMatrix = new FeColorMatrixElement();
        $colorMatrix->setType($type);

        if (null !== $values) {
            $colorMatrix->setValues((string) $values);
        }

        if (null !== $in) {
            $colorMatrix->setIn($in);
        }

        if (null !== $result) {
            $colorMatrix->setResult($result);
        }

        $this->filter->appendChild($colorMatrix);

        return $this;
    }

    /**
     * Add a blend effect.
     *
     * @param string      $mode   Blend mode: 'normal', 'multiply', 'screen', 'darken', 'lighten'
     * @param string|null $in     Input 1
     * @param string|null $in2    Input 2
     * @param string|null $result Result identifier
     */
    public function blend(
        string $mode,
        ?string $in = null,
        ?string $in2 = null,
        ?string $result = null,
    ): self {
        $blend = new FeBlendElement();
        $blend->setMode($mode);

        if (null !== $in) {
            $blend->setIn($in);
        }

        if (null !== $in2) {
            $blend->setIn2($in2);
        }

        if (null !== $result) {
            $blend->setResult($result);
        }

        $this->filter->appendChild($blend);

        return $this;
    }

    /**
     * Add a composite effect.
     *
     * @param string      $operator Composite operator: 'over', 'in', 'out', 'atop', 'xor', 'arithmetic'
     * @param string|null $in       Input 1
     * @param string|null $in2      Input 2
     * @param string|null $result   Result identifier
     */
    public function composite(
        string $operator,
        ?string $in = null,
        ?string $in2 = null,
        ?string $result = null,
    ): self {
        $composite = new FeCompositeElement();
        $composite->setOperator($operator);

        if (null !== $in) {
            $composite->setIn($in);
        }

        if (null !== $in2) {
            $composite->setIn2($in2);
        }

        if (null !== $result) {
            $composite->setResult($result);
        }

        $this->filter->appendChild($composite);

        return $this;
    }

    /**
     * Add a flood effect (solid color fill).
     *
     * @param string      $color   Flood color
     * @param float       $opacity Flood opacity (0-1)
     * @param string|null $result  Result identifier
     */
    public function flood(
        string $color,
        float $opacity = 1.0,
        ?string $result = null,
    ): self {
        $flood = new FeFloodElement();
        $flood->setFloodColor($color);
        $flood->setFloodOpacity($opacity);

        if (null !== $result) {
            $flood->setResult($result);
        }

        $this->filter->appendChild($flood);

        return $this;
    }

    /**
     * Create a drop shadow effect.
     *
     * This is a convenience method that creates a complete drop shadow filter
     * using multiple filter primitives.
     *
     * @param Document $document The document
     * @param string   $id       Filter ID
     * @param float    $dx       Horizontal offset
     * @param float    $dy       Vertical offset
     * @param float    $blur     Blur amount
     * @param string   $color    Shadow color
     * @param float    $opacity  Shadow opacity (0-1)
     */
    public static function createDropShadow(
        Document $document,
        string $id,
        float $dx = 2,
        float $dy = 2,
        float $blur = 4,
        string $color = '#000000',
        float $opacity = 0.3,
    ): FilterElement {
        $helper = self::create($document, $id);

        // Create the shadow
        $helper->gaussianBlur($blur, 'SourceAlpha', 'blur')
            ->offset($dx, $dy, 'blur', 'offsetBlur')
            ->flood($color, $opacity, 'color')
            ->composite('in', 'color', 'offsetBlur', 'shadow')
            ->blend('normal', 'SourceGraphic', 'shadow');

        $helper->addToDefs();

        return $helper->getFilter();
    }

    /**
     * Create a glow effect.
     *
     * @param Document $document The document
     * @param string   $id       Filter ID
     * @param string   $color    Glow color
     * @param float    $strength Glow strength (blur amount)
     * @param float    $opacity  Glow opacity (0-1)
     */
    public static function createGlow(
        Document $document,
        string $id,
        string $color = '#3b82f6',
        float $strength = 2,
        float $opacity = 0.8,
    ): FilterElement {
        $helper = self::create($document, $id);

        // Create the glow
        $helper->gaussianBlur($strength, 'SourceAlpha', 'blur')
            ->flood($color, $opacity, 'color')
            ->composite('in', 'color', 'blur', 'glow')
            ->blend('normal', 'SourceGraphic', 'glow');

        $helper->addToDefs();

        return $helper->getFilter();
    }

    /**
     * Create a simple blur filter.
     *
     * @param Document $document The document
     * @param string   $id       Filter ID
     * @param float    $amount   Blur amount
     */
    public static function createBlur(
        Document $document,
        string $id,
        float $amount = 5,
    ): FilterElement {
        $helper = self::create($document, $id);
        $helper->gaussianBlur($amount);
        $helper->addToDefs();

        return $helper->getFilter();
    }

    /**
     * Create a desaturate (grayscale) filter.
     *
     * @param Document $document The document
     * @param string   $id       Filter ID
     * @param float    $amount   Desaturation amount (0 = no change, 1 = full grayscale)
     */
    public static function createDesaturate(
        Document $document,
        string $id,
        float $amount = 1.0,
    ): FilterElement {
        $helper = self::create($document, $id);
        $helper->colorMatrix('saturate', 1.0 - $amount);
        $helper->addToDefs();

        return $helper->getFilter();
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
