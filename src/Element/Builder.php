<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

use Atelier\Svg\Document;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\RadialGradientElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Structural\UseElement;
use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Exception\LogicException;

/**
 * Fluent API builder for creating SVG documents programmatically.
 *
 * This class provides a chainable interface for building complex SVG documents.
 * It maintains a stack of container elements to support proper nesting.
 *
 * Example usage:
 * ```php
 * $builder = new Builder();
 * $doc = $builder
 *     ->svg(800, 600)
 *         ->rect(10, 10, 100, 100)->fill('#ff0000')->end()
 *         ->circle(200, 200, 50)->fill('#00ff00')->stroke('#000')->strokeWidth(2)->end()
 *         ->g()
 *             ->rect(300, 100, 50, 50)->fill('#0000ff')->end()
 *             ->rect(360, 100, 50, 50)->fill('#ffff00')->end()
 *         ->end()
 *     ->getDocument();
 * ```
 */
final class Builder implements BuilderInterface
{
    private ?Document $document = null;
    private ?SvgElement $rootElement = null;

    /** @var array<ContainerElementInterface> Stack of container elements for nesting */
    private array $containerStack = [];

    /** @var ElementInterface|null The current element being built */
    private ?ElementInterface $currentElement = null;

    /**
     * Creates a new SVG root element and starts building.
     *
     * @param int|float $width  The width of the SVG
     * @param int|float $height The height of the SVG
     */
    public function svg(int|float $width, int|float $height): self
    {
        $this->rootElement = new SvgElement();
        $this->rootElement->setWidth($width);
        $this->rootElement->setHeight($height);

        $this->document = new Document($this->rootElement);
        $this->containerStack = [$this->rootElement];
        $this->currentElement = $this->rootElement;

        return $this;
    }

    /**
     * Returns the SVG root element (required by BuilderInterface).
     */
    public function getSvg(): SvgElement
    {
        if (null === $this->rootElement) {
            $this->svg(300, 150); // Create default SVG
        }

        assert(null !== $this->rootElement);

        return $this->rootElement;
    }

    /**
     * Creates a group element.
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function g(): self
    {
        $element = new GroupElement();

        return $this->addElementToCurrentContainer($element, true);
    }

    /**
     * Creates a rectangle element.
     *
     * @param int|float      $x      X coordinate
     * @param int|float      $y      Y coordinate
     * @param int|float      $width  Width of the rectangle
     * @param int|float      $height Height of the rectangle
     * @param int|float|null $rx     Optional x-axis radius for rounded corners
     * @param int|float|null $ry     Optional y-axis radius for rounded corners
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function rect(
        int|float $x,
        int|float $y,
        int|float $width,
        int|float $height,
        int|float|null $rx = null,
        int|float|null $ry = null,
    ): self {
        $element = new RectElement();
        $element->setX($x);
        $element->setY($y);
        $element->setWidth($width);
        $element->setHeight($height);

        if (null !== $rx) {
            $element->setRx($rx);
        }
        if (null !== $ry) {
            $element->setRy($ry);
        }

        return $this->addElementToCurrentContainer($element);
    }

    /**
     * Creates a circle element.
     *
     * @param int|float $cx Center x coordinate
     * @param int|float $cy Center y coordinate
     * @param int|float $r  Radius
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function circle(int|float $cx, int|float $cy, int|float $r): self
    {
        $element = new CircleElement();
        $element->setCx($cx);
        $element->setCy($cy);
        $element->setR($r);

        return $this->addElementToCurrentContainer($element);
    }

    /**
     * Creates an ellipse element.
     *
     * @param int|float $cx Center x coordinate
     * @param int|float $cy Center y coordinate
     * @param int|float $rx X-axis radius
     * @param int|float $ry Y-axis radius
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function ellipse(int|float $cx, int|float $cy, int|float $rx, int|float $ry): self
    {
        $element = new EllipseElement();
        $element->setCx($cx);
        $element->setCy($cy);
        $element->setRx($rx);
        $element->setRy($ry);

        return $this->addElementToCurrentContainer($element);
    }

    /**
     * Creates a line element.
     *
     * @param int|float $x1 Start x coordinate
     * @param int|float $y1 Start y coordinate
     * @param int|float $x2 End x coordinate
     * @param int|float $y2 End y coordinate
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function line(int|float $x1, int|float $y1, int|float $x2, int|float $y2): self
    {
        $element = new LineElement();
        $element->setX1($x1);
        $element->setY1($y1);
        $element->setX2($x2);
        $element->setY2($y2);

        return $this->addElementToCurrentContainer($element);
    }

    /**
     * Creates a polygon element.
     *
     * @param string $points Points string (e.g., "0,0 100,0 100,100")
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function polygon(string $points): self
    {
        $element = new PolygonElement();
        $element->setPoints($points);

        return $this->addElementToCurrentContainer($element);
    }

    /**
     * Creates a polyline element.
     *
     * @param string $points Points string (e.g., "0,0 100,0 100,100")
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function polyline(string $points): self
    {
        $element = new PolylineElement();
        $element->setPoints($points);

        return $this->addElementToCurrentContainer($element);
    }

    /**
     * Creates a path element and returns a PathBuilder for fluent path construction.
     *
     * @return FluentPathBuilder A proxy that allows fluent path building
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function path(): FluentPathBuilder
    {
        $element = new PathElement();
        $this->addElementToCurrentContainer($element);

        return new FluentPathBuilder($this, $element);
    }

    /**
     * Creates a text element.
     *
     * @param int|float   $x       X coordinate
     * @param int|float   $y       Y coordinate
     * @param string|null $content Optional text content
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function text(int|float $x, int|float $y, ?string $content = null): self
    {
        $element = new TextElement();
        $element->setX($x);
        $element->setY($y);

        if (null !== $content) {
            $element->setTextContent($content);
        }

        return $this->addElementToCurrentContainer($element, true);
    }

    /**
     * Creates a linear gradient element.
     *
     * @param string         $id The gradient ID
     * @param int|float|null $x1 Optional start x coordinate
     * @param int|float|null $y1 Optional start y coordinate
     * @param int|float|null $x2 Optional end x coordinate
     * @param int|float|null $y2 Optional end y coordinate
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function linearGradient(
        string $id,
        int|float|null $x1 = null,
        int|float|null $y1 = null,
        int|float|null $x2 = null,
        int|float|null $y2 = null,
    ): self {
        $element = new LinearGradientElement();
        $element->setAttribute('id', $id);

        if (null !== $x1) {
            $element->setX1($x1);
        }
        if (null !== $y1) {
            $element->setY1($y1);
        }
        if (null !== $x2) {
            $element->setX2($x2);
        }
        if (null !== $y2) {
            $element->setY2($y2);
        }

        return $this->addElementToCurrentContainer($element, true);
    }

    /**
     * Creates a radial gradient element.
     *
     * @param string         $id The gradient ID
     * @param int|float|null $cx Optional center x coordinate
     * @param int|float|null $cy Optional center y coordinate
     * @param int|float|null $r  Optional radius
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function radialGradient(
        string $id,
        int|float|null $cx = null,
        int|float|null $cy = null,
        int|float|null $r = null,
    ): self {
        $element = new RadialGradientElement();
        $element->setAttribute('id', $id);

        if (null !== $cx) {
            $element->setCx($cx);
        }
        if (null !== $cy) {
            $element->setCy($cy);
        }
        if (null !== $r) {
            $element->setR($r);
        }

        return $this->addElementToCurrentContainer($element, true);
    }

    /**
     * Creates a defs section.
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function defs(): self
    {
        $element = new DefsElement();

        return $this->addElementToCurrentContainer($element, true);
    }

    /**
     * Creates a use element.
     *
     * @param string         $href The reference to the element to use
     * @param int|float|null $x    Optional x coordinate
     * @param int|float|null $y    Optional y coordinate
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function use(string $href, int|float|null $x = null, int|float|null $y = null): self
    {
        $element = new UseElement();
        $element->setAttribute('href', $href);

        if (null !== $x) {
            $element->setAttribute('x', (string) $x);
        }
        if (null !== $y) {
            $element->setAttribute('y', (string) $y);
        }

        return $this->addElementToCurrentContainer($element);
    }

    /**
     * Creates an image element.
     *
     * @param string         $href   The image URL
     * @param int|float|null $x      Optional x coordinate
     * @param int|float|null $y      Optional y coordinate
     * @param int|float|null $width  Optional width
     * @param int|float|null $height Optional height
     *
     * @throws LogicException If no container exists (call svg() first)
     */
    public function image(
        string $href,
        int|float|null $x = null,
        int|float|null $y = null,
        int|float|null $width = null,
        int|float|null $height = null,
    ): self {
        $element = new ImageElement();
        $element->setAttribute('href', $href);

        if (null !== $x) {
            $element->setAttribute('x', (string) $x);
        }
        if (null !== $y) {
            $element->setAttribute('y', (string) $y);
        }
        if (null !== $width) {
            $element->setAttribute('width', (string) $width);
        }
        if (null !== $height) {
            $element->setAttribute('height', (string) $height);
        }

        return $this->addElementToCurrentContainer($element);
    }

    /**
     * Sets the fill color attribute.
     *
     * @param string $color The fill color
     *
     * @throws LogicException If there is no current element
     */
    public function fill(string $color): self
    {
        return $this->attr('fill', $color);
    }

    /**
     * Sets the stroke color attribute.
     *
     * @param string $color The stroke color
     *
     * @throws LogicException If there is no current element
     */
    public function stroke(string $color): self
    {
        return $this->attr('stroke', $color);
    }

    /**
     * Sets the stroke-width attribute.
     *
     * @param int|float $width The stroke width
     *
     * @throws LogicException If there is no current element
     */
    public function strokeWidth(int|float $width): self
    {
        return $this->attr('stroke-width', (string) $width);
    }

    /**
     * Sets the opacity attribute.
     *
     * @param float $value The opacity value (0.0 to 1.0)
     *
     * @throws LogicException If there is no current element
     */
    public function opacity(float $value): self
    {
        return $this->attr('opacity', (string) $value);
    }

    /**
     * Sets the transform attribute.
     *
     * @param string $transform The transform string
     *
     * @throws LogicException If there is no current element
     */
    public function transform(string $transform): self
    {
        return $this->attr('transform', $transform);
    }

    /**
     * Sets any attribute on the current element.
     *
     * @param string $name  The attribute name
     * @param string $value The attribute value
     *
     * @throws LogicException If there is no current element
     */
    public function attr(string $name, string $value): self
    {
        if (null === $this->currentElement) {
            throw new LogicException('No current element to set attributes on');
        }

        $this->currentElement->setAttribute($name, $value);

        return $this;
    }

    /**
     * Sets the id attribute.
     *
     * @param string $id The element ID
     *
     * @throws LogicException If there is no current element
     */
    public function id(string $id): self
    {
        return $this->attr('id', $id);
    }

    /**
     * Sets the class attribute.
     *
     * @param string $className The CSS class name
     *
     * @throws LogicException If there is no current element
     */
    public function class(string $className): self
    {
        return $this->attr('class', $className);
    }

    /**
     * Closes the current element and returns to the parent container.
     *
     * @throws LogicException If there is no container to close
     */
    public function end(): self
    {
        if (empty($this->containerStack)) {
            throw new LogicException('Cannot end: no container to close');
        }

        // Pop the current container if it's a container element
        if ($this->currentElement instanceof ContainerElementInterface
            && end($this->containerStack) === $this->currentElement) {
            array_pop($this->containerStack);
        }

        // Set current element to the top of the stack
        $this->currentElement = end($this->containerStack) ?: null;

        return $this;
    }

    /**
     * Gets the built Document.
     */
    public function getDocument(): Document
    {
        if (null === $this->document) {
            // Create a default document if none exists
            $this->svg(300, 150);
        }

        assert(null !== $this->document);

        return $this->document;
    }

    /**
     * Serializes the document to an SVG string.
     */
    public function toString(): string
    {
        $dumper = new CompactXmlDumper();

        return $dumper->dump($this->getDocument());
    }

    /**
     * Adds an element to the current container.
     *
     * @param ElementInterface $element     The element to add
     * @param bool             $isContainer Whether this element is a container that should be pushed to the stack
     */
    private function addElementToCurrentContainer(ElementInterface $element, bool $isContainer = false): self
    {
        if (empty($this->containerStack)) {
            throw new LogicException('No container to add element to. Call svg() first.');
        }

        $currentContainer = end($this->containerStack);
        assert($currentContainer instanceof ContainerElementInterface);

        $currentContainer->appendChild($element);
        $this->currentElement = $element;

        if ($isContainer && $element instanceof ContainerElementInterface) {
            $this->containerStack[] = $element;
        }

        return $this;
    }
}
