<?php

declare(strict_types=1);

namespace Atelier\Svg\Validation;

use Atelier\Svg\Element\ElementInterface;

/**
 * Represents information about a reference from one element to another.
 *
 * @internal
 */
final readonly class ReferenceInfo
{
    public function __construct(
        public ElementInterface $element,
        public string $attribute,
        public string $referencedId,
        public string $value,
    ) {
    }
}
