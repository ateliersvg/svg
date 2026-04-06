<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;

/**
 * Optimization pass that removes XML comments from the SVG document.
 *
 * Note: This pass is currently a no-op since the Element tree does not
 * support comment nodes. Comments are stripped at the loader level instead.
 */
final class RemoveCommentsPass implements OptimizerPassInterface
{
    public function getName(): string
    {
        return 'remove-comments';
    }

    public function optimize(Document $document): void
    {
        // No-op: comment nodes are stripped at the loader level.
    }
}
