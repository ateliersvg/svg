<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Removes the enable-background attribute from SVG elements.
 *
 * The enable-background attribute was primarily used in legacy Adobe Illustrator
 * exports and is rarely needed in modern SVGs. Removing it reduces file size.
 *
 * Example:
 * Before: <g enable-background="new 0 0 100 100">...</g>
 * After:  <g>...</g>
 */
final class CleanupEnableBackgroundPass extends AbstractOptimizerPass
{
    public function getName(): string
    {
        return 'cleanup-enable-background';
    }

    protected function processElement(ElementInterface $element): void
    {
        if ($element->hasAttribute('enable-background')) {
            $element->removeAttribute('enable-background');
        }
    }
}
