<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;

/**
 * Removes width and height attributes from the root <svg> element.
 *
 * Removing dimensions makes the SVG scalable and responsive. The SVG will
 * scale to fill its container while maintaining its aspect ratio (if viewBox is present).
 *
 * Note: This is often desirable for inline SVGs in web pages, but may break
 * SVGs that rely on explicit dimensions. Use with caution.
 *
 * Example:
 * Before: <svg width="100" height="100" viewBox="0 0 100 100">...</svg>
 * After:  <svg viewBox="0 0 100 100">...</svg>
 */
final class RemoveDimensionsPass implements OptimizerPassInterface
{
    public function getName(): string
    {
        return 'remove-dimensions';
    }

    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Only remove if viewBox is present (so aspect ratio is preserved)
        if ($rootElement->hasAttribute('viewBox')) {
            $rootElement->removeAttribute('width');
            $rootElement->removeAttribute('height');
        }
    }
}
