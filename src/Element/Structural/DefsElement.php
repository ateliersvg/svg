<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Structural;

use Atelier\Svg\Element\AbstractContainerElement;

/**
 * Represents the <defs> SVG element.
 *
 * The <defs> element is used to store graphical objects that will be used at a later time.
 * Objects created inside a <defs> element are not rendered directly. To display them, you
 * have to reference them (with a <use> element for example).
 */
final class DefsElement extends AbstractContainerElement
{
    private const string TAG_NAME = 'defs';
    private const array PROTECTED_ATTRIBUTES = [];

    public function __construct(array $initialAttributes = [])
    {
        parent::__construct(self::TAG_NAME, self::PROTECTED_ATTRIBUTES, $initialAttributes);
    }
}
