<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Path\Simplifier\SimplifierInterface;

/**
 * Optimization pass that simplifies path data using a simplification algorithm.
 *
 * This pass reduces the number of points in path elements while maintaining
 * visual appearance within a specified tolerance. It only simplifies <path>
 * elements and preserves curve segments (only simplifying line segments).
 */
final class SimplifyPathPass extends AbstractOptimizerPass
{
    /**
     * Creates a new SimplifyPathPass.
     *
     * @param SimplifierInterface $simplifier The simplification algorithm to use
     * @param float               $tolerance  The tolerance value (higher = more aggressive simplification)
     */
    public function __construct(
        private readonly SimplifierInterface $simplifier,
        private float $tolerance = 1.0,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'simplify-path';
    }

    /**
     * Processes an element to simplify path data.
     *
     * @param ElementInterface $element The element to process
     */
    protected function processElement(ElementInterface $element): void
    {
        // If this is a path element, simplify it
        if ($element instanceof PathElement) {
            $this->simplifyPath($element);
        }
    }

    /**
     * Simplifies a path element.
     *
     * @param PathElement $pathElement The path element to simplify
     */
    private function simplifyPath(PathElement $pathElement): void
    {
        $pathData = $pathElement->getData();

        if (null === $pathData) {
            return;
        }

        // Simplify the path data
        $simplifiedData = $this->simplifier->simplify($pathData, $this->tolerance);

        // Update the path element with simplified data
        $pathElement->setData($simplifiedData);
    }

    /**
     * Gets the tolerance value.
     *
     * @return float The tolerance value
     */
    public function getTolerance(): float
    {
        return $this->tolerance;
    }

    /**
     * Sets the tolerance value.
     *
     * @param float $tolerance The tolerance value
     */
    public function setTolerance(float $tolerance): self
    {
        $this->tolerance = $tolerance;

        return $this;
    }
}
