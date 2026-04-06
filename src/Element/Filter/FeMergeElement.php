<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractContainerElement;

/**
 * Represents the <feMerge> SVG filter primitive.
 *
 * This filter primitive composites multiple input images together by stacking them
 * in the order they appear (last on top). Each input is specified by a feMergeNode child.
 *
 * Common uses:
 * - Combining drop shadow with original graphic
 * - Layering multiple effects
 * - Creating complex composite effects
 *
 * Example:
 * <feMerge>
 *   <feMergeNode in="shadowBlur"/>
 *   <feMergeNode in="SourceGraphic"/>
 * </feMerge>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feMergeElement
 */
final class FeMergeElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('feMerge');
    }
}
