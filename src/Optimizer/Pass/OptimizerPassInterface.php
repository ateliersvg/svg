<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;

/**
 * Interface for optimizer passes.
 *
 * Each pass implements a specific optimization technique that can be applied
 * to an SVG document. Passes modify the document in place.
 */
interface OptimizerPassInterface
{
    /**
     * Gets the name of this optimization pass.
     *
     * @return string The pass name
     */
    public function getName(): string;

    /**
     * Optimizes an SVG document.
     *
     * This method modifies the document in place, applying the specific
     * optimization technique implemented by this pass.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void;
}
