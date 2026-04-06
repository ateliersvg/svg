<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Clipping;

use Atelier\Svg\Element\AbstractContainerElement;

/**
 * Represents an SVG <clipPath> element.
 *
 * The clipPath element defines a clipping path which can be applied to
 * other elements via the clip-path property. The clipping path restricts
 * the region to which paint can be applied.
 *
 * @see https://www.w3.org/TR/SVG11/masking.html#ClipPathElement
 */
final class ClipPathElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('clipPath');
    }

    /**
     * Sets the coordinate system for the contents of the clipPath.
     *
     * @param string $clipPathUnits The units ('userSpaceOnUse' or 'objectBoundingBox')
     */
    public function setClipPathUnits(string $clipPathUnits): static
    {
        $this->setAttribute('clipPathUnits', $clipPathUnits);

        return $this;
    }

    /**
     * Gets the coordinate system for the contents of the clipPath.
     *
     * @return string|null The clipPath units, or null if not set
     */
    public function getClipPathUnits(): ?string
    {
        return $this->getAttribute('clipPathUnits');
    }
}
