<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Structural;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\Viewbox;

/**
 * Represents the <marker> SVG element.
 *
 * Markers are used to draw symbols at the vertices of paths, lines, polylines, and polygons.
 * Common uses include arrowheads, dots, and other decorative symbols.
 *
 * @see https://www.w3.org/TR/SVG11/painting.html#MarkerElement
 */
final class MarkerElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('marker');
    }

    /**
     * Set the reference point X coordinate.
     */
    public function setRefX(string|int|float $refX): static
    {
        $this->setAttribute('refX', (string) $refX);

        return $this;
    }

    /**
     * Get the reference point X coordinate.
     */
    public function getRefX(): ?string
    {
        return $this->getAttribute('refX');
    }

    /**
     * Set the reference point Y coordinate.
     */
    public function setRefY(string|int|float $refY): static
    {
        $this->setAttribute('refY', (string) $refY);

        return $this;
    }

    /**
     * Get the reference point Y coordinate.
     */
    public function getRefY(): ?string
    {
        return $this->getAttribute('refY');
    }

    /**
     * Set the marker width.
     */
    public function setMarkerWidth(string|int|float $width): static
    {
        $this->setAttribute('markerWidth', (string) $width);

        return $this;
    }

    /**
     * Get the marker width.
     */
    public function getMarkerWidth(): ?Length
    {
        $value = $this->getAttribute('markerWidth');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Set the marker height.
     */
    public function setMarkerHeight(string|int|float $height): static
    {
        $this->setAttribute('markerHeight', (string) $height);

        return $this;
    }

    /**
     * Get the marker height.
     */
    public function getMarkerHeight(): ?Length
    {
        $value = $this->getAttribute('markerHeight');

        return null !== $value ? Length::parse($value) : null;
    }

    /**
     * Set the marker orientation.
     *
     * @param string|int|float $orient 'auto', 'auto-start-reverse', or angle
     */
    public function setOrient(string|int|float $orient): static
    {
        $this->setAttribute('orient', (string) $orient);

        return $this;
    }

    /**
     * Get the marker orientation.
     */
    public function getOrient(): ?string
    {
        return $this->getAttribute('orient');
    }

    /**
     * Set the viewBox attribute.
     */
    public function setViewbox(string|Viewbox $viewbox): static
    {
        if ($viewbox instanceof Viewbox) {
            $this->setAttribute('viewBox', $viewbox->toString());
        } else {
            $parsed = Viewbox::parse($viewbox);
            $this->setAttribute('viewBox', $parsed->toString());
        }

        return $this;
    }

    /**
     * Get the viewBox attribute.
     */
    public function getViewbox(): ?Viewbox
    {
        $viewBox = $this->getAttribute('viewBox');

        return null !== $viewBox ? Viewbox::parse($viewBox) : null;
    }

    /**
     * Set marker units.
     *
     * @param string $units 'strokeWidth' or 'userSpaceOnUse'
     */
    public function setMarkerUnits(string $units): static
    {
        $this->setAttribute('markerUnits', $units);

        return $this;
    }

    /**
     * Get marker units.
     */
    public function getMarkerUnits(): ?string
    {
        return $this->getAttribute('markerUnits');
    }

    /**
     * Set the marker size.
     */
    public function setSize(string|int|float $width, string|int|float $height): static
    {
        $this->setMarkerWidth($width);
        $this->setMarkerHeight($height);

        return $this;
    }

    /**
     * Set the reference point.
     */
    public function setRefPoint(string|int|float $x, string|int|float $y): static
    {
        $this->setRefX($x);
        $this->setRefY($y);

        return $this;
    }
}
