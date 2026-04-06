<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;

/**
 * Removes <desc> elements from SVG documents.
 *
 * Description elements provide human-readable descriptions but are not needed
 * for rendering. They can be safely removed to reduce file size.
 *
 * Note: This may impact accessibility. Consider keeping descriptions if accessibility is important.
 *
 * Example:
 * Before: <svg><desc>A red circle</desc><circle r="50"/></svg>
 * After:  <svg><circle r="50"/></svg>
 *
 * This class delegates to RemoveElementsByTagNamePass for the actual implementation.
 *
 * @see RemoveElementsByTagNamePass For a more flexible implementation
 */
final readonly class RemoveDescPass implements OptimizerPassInterface
{
    private RemoveElementsByTagNamePass $delegate;

    public function __construct()
    {
        $this->delegate = RemoveElementsByTagNamePass::removeDesc();
    }

    public function getName(): string
    {
        return 'remove-desc';
    }

    public function optimize(Document $document): void
    {
        $this->delegate->optimize($document);
    }
}
