<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

/**
 * Interface for SVG builders that produce an SVG root element.
 */
interface BuilderInterface
{
    /**
     * Gets the SVG root element.
     */
    public function getSvg(): SvgElement;
}
