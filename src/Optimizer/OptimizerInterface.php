<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer;

use Atelier\Svg\Document;

/**
 * Interface for SVG optimizers.
 *
 * Optimizers apply a series of optimization passes to an SVG document
 * to reduce file size and improve performance.
 */
interface OptimizerInterface
{
    /**
     * Optimizes an SVG document.
     *
     * @param Document $document The document to optimize
     *
     * @return Document The optimized document
     */
    public function optimize(Document $document): Document;
}
