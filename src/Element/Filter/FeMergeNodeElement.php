<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents the <feMergeNode> SVG element.
 *
 * This element specifies one input layer for the feMerge filter primitive.
 * Multiple feMergeNode elements are stacked in order (last on top).
 *
 * Example:
 * <feMergeNode in="blur"/>
 * <feMergeNode in="SourceGraphic"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feMergeNodeElement
 */
final class FeMergeNodeElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('feMergeNode');
    }

    /**
     * Set the input source for this merge node.
     */
    public function setIn(string $in): static
    {
        $this->setAttribute('in', $in);

        return $this;
    }

    /**
     * Get the input source.
     */
    public function getIn(): ?string
    {
        return $this->getAttribute('in');
    }
}
