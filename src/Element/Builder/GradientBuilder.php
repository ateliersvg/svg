<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\RadialGradientElement;
use Atelier\Svg\Element\Gradient\StopElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Exception\LogicException;
use Atelier\Svg\Exception\RuntimeException;

/**
 * Utility class for creating and managing SVG gradients with a fluent API.
 *
 * Provides convenient methods for creating linear and radial gradients
 * with color stops.
 *
 * Example usage:
 * ```php
 * // Create a linear gradient
 * $gradient = GradientBuilder::createLinear($doc, 'grad1')
 *     ->from(0, 0)
 *     ->to(100, 100)
 *     ->addStop(0, '#3b82f6')
 *     ->addStop(100, '#8b5cf6')
 *     ->getGradient();
 *
 * // Apply to element
 * $element->setAttribute('fill', 'url(#grad1)');
 * ```
 *
 * @see https://www.w3.org/TR/SVG11/pservers.html
 */
final class GradientBuilder
{
    private ?DefsElement $defs = null;

    public function __construct(private readonly Document $document, private readonly LinearGradientElement|RadialGradientElement $gradient)
    {
    }

    /**
     * Create a new linear gradient with fluent API.
     *
     * @param Document $document The document to add the gradient to
     * @param string   $id       The gradient ID
     */
    public static function createLinear(Document $document, string $id): self
    {
        $gradient = new LinearGradientElement();
        $gradient->setId($id);

        return new self($document, $gradient);
    }

    /**
     * Create a new radial gradient with fluent API.
     *
     * @param Document $document The document to add the gradient to
     * @param string   $id       The gradient ID
     */
    public static function createRadial(Document $document, string $id): self
    {
        $gradient = new RadialGradientElement();
        $gradient->setId($id);

        return new self($document, $gradient);
    }

    /**
     * Add the gradient to the document's defs section.
     */
    public function addToDefs(): self
    {
        $defs = $this->getOrCreateDefs();
        $defs->appendChild($this->gradient);

        return $this;
    }

    /**
     * Get the underlying gradient element.
     */
    public function getGradient(): LinearGradientElement|RadialGradientElement
    {
        return $this->gradient;
    }

    /**
     * Set the start point for a linear gradient.
     *
     * @param string|int|float $x1 Start X coordinate
     * @param string|int|float $y1 Start Y coordinate
     */
    public function from(string|int|float $x1, string|int|float $y1): self
    {
        if (!$this->gradient instanceof LinearGradientElement) {
            throw new LogicException('from() can only be used with linear gradients');
        }

        $this->gradient->setX1($x1);
        $this->gradient->setY1($y1);

        return $this;
    }

    /**
     * Set the end point for a linear gradient.
     *
     * @param string|int|float $x2 End X coordinate
     * @param string|int|float $y2 End Y coordinate
     */
    public function to(string|int|float $x2, string|int|float $y2): self
    {
        if (!$this->gradient instanceof LinearGradientElement) {
            throw new LogicException('to() can only be used with linear gradients');
        }

        $this->gradient->setX2($x2);
        $this->gradient->setY2($y2);

        return $this;
    }

    /**
     * Set the center point for a radial gradient.
     *
     * @param string|int|float $cx Center X coordinate
     * @param string|int|float $cy Center Y coordinate
     */
    public function center(string|int|float $cx, string|int|float $cy): self
    {
        if (!$this->gradient instanceof RadialGradientElement) {
            throw new LogicException('center() can only be used with radial gradients');
        }

        $this->gradient->setCx($cx);
        $this->gradient->setCy($cy);

        return $this;
    }

    /**
     * Set the radius for a radial gradient.
     *
     * @param string|int|float $r Radius
     */
    public function radius(string|int|float $r): self
    {
        if (!$this->gradient instanceof RadialGradientElement) {
            throw new LogicException('radius() can only be used with radial gradients');
        }

        $this->gradient->setR($r);

        return $this;
    }

    /**
     * Set the focal point for a radial gradient.
     *
     * @param string|int|float $fx Focal X coordinate
     * @param string|int|float $fy Focal Y coordinate
     */
    public function focal(string|int|float $fx, string|int|float $fy): self
    {
        if (!$this->gradient instanceof RadialGradientElement) {
            throw new LogicException('focal() can only be used with radial gradients');
        }

        $this->gradient->setFx($fx);
        $this->gradient->setFy($fy);

        return $this;
    }

    /**
     * Add a color stop to the gradient.
     *
     * @param string|int|float $offset  Position (0-100 or 0-1)
     * @param string           $color   Color value
     * @param float|null       $opacity Optional opacity (0-1)
     */
    public function addStop(
        string|int|float $offset,
        string $color,
        ?float $opacity = null,
    ): self {
        $stop = new StopElement();
        $stop->setOffset($offset);
        $stop->setStopColor($color);

        if (null !== $opacity) {
            $stop->setStopOpacity($opacity);
        }

        $this->gradient->appendChild($stop);

        return $this;
    }

    /**
     * Set the gradient spread method.
     *
     * @param string $spreadMethod 'pad', 'reflect', or 'repeat'
     */
    public function spreadMethod(string $spreadMethod): self
    {
        $this->gradient->setSpreadMethod($spreadMethod);

        return $this;
    }

    /**
     * Set the gradient units.
     *
     * @param string $units 'userSpaceOnUse' or 'objectBoundingBox'
     */
    public function units(string $units): self
    {
        $this->gradient->setGradientUnits($units);

        return $this;
    }

    /**
     * Set the gradient transform.
     *
     * @param string $transform Transform string
     */
    public function transform(string $transform): self
    {
        $this->gradient->setGradientTransform($transform);

        return $this;
    }

    /**
     * Create a horizontal linear gradient (left to right).
     *
     * @param Document $document  The document
     * @param string   $id        Gradient ID
     * @param string   $fromColor Start color
     * @param string   $toColor   End color
     */
    public static function horizontal(
        Document $document,
        string $id,
        string $fromColor,
        string $toColor,
    ): LinearGradientElement {
        $gradient = self::createLinear($document, $id)
            ->from(0, 0)
            ->to(100, 0)
            ->units('objectBoundingBox')
            ->addStop(0, $fromColor)
            ->addStop(100, $toColor)
            ->addToDefs()
            ->getGradient();

        assert($gradient instanceof LinearGradientElement);

        return $gradient;
    }

    /**
     * Create a vertical linear gradient (top to bottom).
     *
     * @param Document $document  The document
     * @param string   $id        Gradient ID
     * @param string   $fromColor Start color
     * @param string   $toColor   End color
     */
    public static function vertical(
        Document $document,
        string $id,
        string $fromColor,
        string $toColor,
    ): LinearGradientElement {
        $gradient = self::createLinear($document, $id)
            ->from(0, 0)
            ->to(0, 100)
            ->units('objectBoundingBox')
            ->addStop(0, $fromColor)
            ->addStop(100, $toColor)
            ->addToDefs()
            ->getGradient();

        assert($gradient instanceof LinearGradientElement);

        return $gradient;
    }

    /**
     * Create a diagonal linear gradient (top-left to bottom-right).
     *
     * @param Document $document  The document
     * @param string   $id        Gradient ID
     * @param string   $fromColor Start color
     * @param string   $toColor   End color
     */
    public static function diagonal(
        Document $document,
        string $id,
        string $fromColor,
        string $toColor,
    ): LinearGradientElement {
        $gradient = self::createLinear($document, $id)
            ->from(0, 0)
            ->to(100, 100)
            ->units('objectBoundingBox')
            ->addStop(0, $fromColor)
            ->addStop(100, $toColor)
            ->addToDefs()
            ->getGradient();

        assert($gradient instanceof LinearGradientElement);

        return $gradient;
    }

    /**
     * Create a radial gradient from center.
     *
     * @param Document $document    The document
     * @param string   $id          Gradient ID
     * @param string   $centerColor Center color
     * @param string   $edgeColor   Edge color
     */
    public static function radial(
        Document $document,
        string $id,
        string $centerColor,
        string $edgeColor,
    ): RadialGradientElement {
        $gradient = self::createRadial($document, $id)
            ->center(50, 50)
            ->radius(50)
            ->units('objectBoundingBox')
            ->addStop(0, $centerColor)
            ->addStop(100, $edgeColor)
            ->addToDefs()
            ->getGradient();

        assert($gradient instanceof RadialGradientElement);

        return $gradient;
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
