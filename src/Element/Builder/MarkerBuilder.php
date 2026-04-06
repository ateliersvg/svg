<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\MarkerElement;
use Atelier\Svg\Exception\RuntimeException;
use Atelier\Svg\Path\PathBuilder;

/**
 * Utility class for creating and managing SVG markers.
 *
 * Provides convenient methods for creating common marker shapes like
 * arrows, circles, and dots.
 *
 * Example usage:
 * ```php
 * // Create an arrow marker
 * $arrow = MarkerHelper::arrow($doc, 'arrow-end', '#000');
 *
 * // Apply to a line
 * $line->setAttribute('marker-end', 'url(#arrow-end)');
 * ```
 *
 * @see https://www.w3.org/TR/SVG11/painting.html#Markers
 */
final class MarkerBuilder
{
    private ?DefsElement $defs = null;

    public function __construct(private readonly Document $document, private readonly MarkerElement $marker)
    {
    }

    /**
     * Create a new marker with fluent API.
     *
     * @param Document $document The document
     * @param string   $id       Marker ID
     */
    public static function create(Document $document, string $id): self
    {
        $marker = new MarkerElement();
        $marker->setId($id);

        return new self($document, $marker);
    }

    /**
     * Add the marker to the document's defs section.
     */
    public function addToDefs(): self
    {
        $defs = $this->getOrCreateDefs();
        $defs->appendChild($this->marker);

        return $this;
    }

    /**
     * Get the underlying marker element.
     */
    public function getMarker(): MarkerElement
    {
        return $this->marker;
    }

    /**
     * Set the marker size.
     */
    public function size(float $width, float $height): self
    {
        $this->marker->setSize($width, $height);

        return $this;
    }

    /**
     * Set the reference point.
     */
    public function refPoint(float $x, float $y): self
    {
        $this->marker->setRefPoint($x, $y);

        return $this;
    }

    /**
     * Set the marker viewBox.
     */
    public function viewBox(string $viewBox): self
    {
        $this->marker->setViewbox($viewBox);

        return $this;
    }

    /**
     * Set marker to auto-orient.
     */
    public function autoOrient(): self
    {
        $this->marker->setOrient('auto');

        return $this;
    }

    /**
     * Set marker color.
     */
    public function color(string $color): self
    {
        $this->marker->setAttribute('fill', $color);

        return $this;
    }

    /**
     * Create an arrow marker.
     *
     * @param Document $document The document
     * @param string   $id       Marker ID
     * @param string   $color    Arrow color
     * @param float    $size     Arrow size
     */
    public static function arrow(
        Document $document,
        string $id,
        string $color = '#000000',
        float $size = 10,
    ): MarkerElement {
        $helper = self::create($document, $id);

        $helper->marker->setSize($size, $size);
        $helper->marker->setRefPoint($size, $size / 2);
        $helper->marker->setViewbox("0 0 {$size} {$size}");
        $helper->marker->setOrient('auto');

        // Create arrow path
        $path = new PathElement();
        $pathData = PathBuilder::startAt(0, 0)
            ->lineTo($size, $size / 2)
            ->lineTo(0, $size)
            ->closePath()
            ->toPathData();
        $path->setData($pathData);
        $path->setAttribute('fill', $color);

        $helper->marker->appendChild($path);
        $helper->addToDefs();

        return $helper->getMarker();
    }

    /**
     * Create a circle marker.
     *
     * @param Document $document The document
     * @param string   $id       Marker ID
     * @param string   $color    Circle color
     * @param float    $radius   Circle radius
     */
    public static function circle(
        Document $document,
        string $id,
        string $color = '#000000',
        float $radius = 3,
    ): MarkerElement {
        $helper = self::create($document, $id);

        $size = $radius * 2;
        $helper->marker->setSize($size, $size);
        $helper->marker->setRefPoint($radius, $radius);
        $helper->marker->setViewbox("0 0 {$size} {$size}");

        // Create circle
        $circle = new CircleElement();
        $circle->setCx($radius);
        $circle->setCy($radius);
        $circle->setR($radius);
        $circle->setAttribute('fill', $color);

        $helper->marker->appendChild($circle);
        $helper->addToDefs();

        return $helper->getMarker();
    }

    /**
     * Create a dot marker (small filled circle).
     *
     * @param Document $document The document
     * @param string   $id       Marker ID
     * @param string   $color    Dot color
     * @param float    $radius   Dot radius
     */
    public static function dot(
        Document $document,
        string $id,
        string $color = '#000000',
        float $radius = 2,
    ): MarkerElement {
        return self::circle($document, $id, $color, $radius);
    }

    /**
     * Create a square marker.
     *
     * @param Document $document The document
     * @param string   $id       Marker ID
     * @param string   $color    Square color
     * @param float    $size     Square size
     */
    public static function square(
        Document $document,
        string $id,
        string $color = '#000000',
        float $size = 6,
    ): MarkerElement {
        $helper = self::create($document, $id);

        $helper->marker->setSize($size, $size);
        $helper->marker->setRefPoint($size / 2, $size / 2);
        $helper->marker->setViewbox("0 0 {$size} {$size}");

        // Create square as polygon
        $polygon = new PolygonElement();
        $polygon->setPoints("0,0 {$size},0 {$size},{$size} 0,{$size}");
        $polygon->setAttribute('fill', $color);

        $helper->marker->appendChild($polygon);
        $helper->addToDefs();

        return $helper->getMarker();
    }

    /**
     * Create a diamond marker.
     *
     * @param Document $document The document
     * @param string   $id       Marker ID
     * @param string   $color    Diamond color
     * @param float    $size     Diamond size
     */
    public static function diamond(
        Document $document,
        string $id,
        string $color = '#000000',
        float $size = 8,
    ): MarkerElement {
        $helper = self::create($document, $id);

        $helper->marker->setSize($size, $size);
        $helper->marker->setRefPoint($size / 2, $size / 2);
        $helper->marker->setViewbox("0 0 {$size} {$size}");

        // Create diamond as polygon
        $half = $size / 2;
        $polygon = new PolygonElement();
        $polygon->setPoints("{$half},0 {$size},{$half} {$half},{$size} 0,{$half}");
        $polygon->setAttribute('fill', $color);

        $helper->marker->appendChild($polygon);
        $helper->addToDefs();

        return $helper->getMarker();
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
