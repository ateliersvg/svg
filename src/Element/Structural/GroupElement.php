<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Structural;

use Atelier\Svg\Element\AbstractContainerElement;

/**
 * Represents an SVG <g> (group) element.
 *
 * The <g> element is a container used to group other SVG elements.
 * Transformations applied to the <g> element are performed on all of its child elements.
 * Attributes applied to the <g> element are inherited by its child elements.
 */
final class GroupElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('g');
    }
}
