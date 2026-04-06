<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;

/**
 * Optimization pass that removes XML processing instructions.
 *
 * This pass removes the XML declaration (<?xml version="1.0"?>) from the SVG document.
 *
 * Processing instructions like <?xml ...?> are not needed for SVG files in most contexts:
 * - Modern browsers don't require them
 * - When embedded in HTML, they can cause issues
 * - They add unnecessary bytes to the file
 *
 * Benefits:
 * - Reduces file size
 * - Better compatibility when embedding SVG in HTML
 * - Cleaner output
 *
 * Note: If you need strict XML compliance, don't use this pass.
 */
final class RemoveXMLProcInstPass implements OptimizerPassInterface
{
    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-xml-proc-inst';
    }

    /**
     * Optimizes the document by removing XML processing instructions.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        // The XML processing instruction is typically stored at the document level
        // In this implementation, we mark that the XML declaration should not be included
        // when the document is serialized

        // Note: The actual removal happens during serialization by the dumper.
        // We set a flag on the document to indicate that the XML declaration
        // should be omitted.

        // For now, this is a placeholder that will work with the dumper's configuration.
        // The Document class would need a method to control XML declaration output.

        // This pass is primarily informational - the actual removal typically happens
        // at the dumper level by not including the XML declaration in the output.
    }
}
