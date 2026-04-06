<?php

declare(strict_types=1);

namespace Atelier\Svg\Document;

/**
 * Strategies for merging multiple SVG documents.
 */
enum MergeStrategy
{
    /**
     * Simply append all elements from source documents.
     */
    case APPEND;

    /**
     * Arrange documents side-by-side horizontally.
     */
    case SIDE_BY_SIDE;

    /**
     * Stack documents vertically.
     */
    case STACKED;

    /**
     * Wrap each document's content in a <symbol> element.
     * Useful for creating SVG sprites.
     */
    case SYMBOLS;

    /**
     * Arrange documents in a grid layout.
     */
    case GRID;
}
