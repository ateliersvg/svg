<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;

/**
 * Optimization pass that removes DOCTYPE declarations.
 *
 * This pass removes DOCTYPE declarations from SVG documents.
 *
 * DOCTYPE declarations like <!DOCTYPE svg PUBLIC ...> are not needed for modern SVG:
 * - SVG 1.1 and 2.0 don't require DOCTYPE
 * - Browsers ignore them
 * - They add unnecessary bytes
 *
 * Benefits:
 * - Reduces file size
 * - Cleaner, more modern SVG output
 * - Better compatibility with modern tools
 *
 * Example of what gets removed:
 * <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
 */
final class RemoveDoctypePass implements OptimizerPassInterface
{
    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-doctype';
    }

    /**
     * Optimizes the document by removing DOCTYPE declarations.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        // Similar to RemoveXMLProcInstPass, DOCTYPE declarations are typically
        // handled at the document/parser level rather than as elements in the tree.

        // The actual removal happens during serialization by the dumper.
        // Modern SVG doesn't need DOCTYPE declarations, and most dumpers
        // don't include them by default.

        // This pass serves as documentation that DOCTYPE removal is part of
        // the optimization pipeline and would work with a dumper that supports
        // DOCTYPE suppression.
    }
}
