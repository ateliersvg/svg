<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feImage> SVG filter primitive.
 *
 * Refers to an external image or SVG element as input for another filter primitive.
 *
 * Example:
 * <feImage href="image.png"/>
 * <feImage href="#myGradient"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feImageElement
 */
final class FeImageElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feImage');
    }

    public function setHref(string $href): static
    {
        $this->setAttribute('href', $href);

        return $this;
    }

    public function getHref(): ?string
    {
        return $this->getAttribute('href');
    }
}
