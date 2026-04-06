<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;

/**
 * Removes <title> elements from SVG documents.
 *
 * Title elements provide human-readable titles but are not needed for rendering.
 * They can be safely removed to reduce file size.
 *
 * Note: This may impact accessibility and SEO. Title elements are important for:
 * - Screen readers
 * - Tooltips on hover
 * - Search engine optimization
 *
 * This pass is typically disabled by default in production optimizers.
 *
 * Example:
 * Before: <svg><title>My Icon</title><circle r="50"/></svg>
 * After:  <svg><circle r="50"/></svg>
 *
 * This class delegates to RemoveElementsByTagNamePass for the actual implementation.
 *
 * @see RemoveElementsByTagNamePass For a more flexible implementation
 */
final readonly class RemoveTitlePass implements OptimizerPassInterface
{
    private RemoveElementsByTagNamePass $delegate;

    public function __construct()
    {
        $this->delegate = RemoveElementsByTagNamePass::removeTitle();
    }

    public function getName(): string
    {
        return 'remove-title';
    }

    public function optimize(Document $document): void
    {
        $this->delegate->optimize($document);
    }
}
