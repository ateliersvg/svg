<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feTile> SVG filter primitive.
 *
 * Fills a rectangle with a tiled pattern of an input image.
 *
 * Example:
 * <feTile in="SourceGraphic"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feTileElement
 */
final class FeTileElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feTile');
    }
}
