<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Path\Simplifier\SimplifierInterface;

/**
 * Optimization pass that simplifies path data using a simplification algorithm.
 *
 * This pass reduces the number of points in path elements while maintaining
 * visual appearance within a specified tolerance. It only simplifies <path>
 * elements and preserves curve segments (only simplifying line segments).
 *
 * ## Tolerance and document scale
 *
 * The tolerance is a distance in user units, so its effect depends on how large the
 * drawing is in its own coordinate system. A tolerance of 1.0 is a hairline in a
 * 1000-unit banner and a whole module in a 33-unit barcode, where it deletes corners
 * and turns rectangles into triangles.
 *
 * Pass $relativeTolerance to bound the tolerance by the drawing's own span: the pass
 * then applies the smaller of $tolerance and $relativeTolerance * span, where the span
 * is the diagonal of the root viewBox. A document large enough is simplified exactly as
 * it is with $tolerance alone; a small-grid drawing gets a proportionally smaller
 * tolerance instead of being flattened. Documents with no usable scale keep $tolerance.
 */
final class SimplifyPathPass extends AbstractOptimizerPass
{
    /**
     * Relative tolerances used by the presets, as a fraction of the drawing span.
     *
     * Each value reproduces its preset's absolute tolerance at a span of 500 user units,
     * the scale the absolute values were tuned for (0.001 * 500 = 0.5, the default
     * preset tolerance). Below that span the tolerance shrinks with the drawing.
     */
    public const float RELATIVE_TOLERANCE_DEFAULT = 0.001;
    public const float RELATIVE_TOLERANCE_AGGRESSIVE = 0.004;
    public const float RELATIVE_TOLERANCE_SAFE = 0.0002;
    public const float RELATIVE_TOLERANCE_WEB = 0.002;

    /**
     * Tolerance applied during the current optimize() run, in user units.
     */
    private float $runTolerance;

    /**
     * Creates a new SimplifyPathPass.
     *
     * @param SimplifierInterface $simplifier        The simplification algorithm to use
     * @param float               $tolerance         The tolerance in user units (higher = more aggressive simplification)
     * @param float|null          $relativeTolerance Upper bound on the tolerance as a fraction of the
     *                                               drawing span, or null to always use $tolerance
     *
     * @throws InvalidArgumentException if $relativeTolerance is not greater than zero
     */
    public function __construct(
        private readonly SimplifierInterface $simplifier,
        private float $tolerance = 1.0,
        private readonly ?float $relativeTolerance = null,
    ) {
        if (null !== $relativeTolerance && $relativeTolerance <= 0.0) {
            throw new InvalidArgumentException(sprintf('Relative tolerance must be greater than zero, got %s.', $relativeTolerance));
        }

        $this->runTolerance = $tolerance;
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'simplify-path';
    }

    /**
     * Simplifies every path in the document, at the tolerance the document resolves to.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $this->runTolerance = $this->resolveTolerance($document);

        parent::optimize($document);
    }

    /**
     * Resolves the tolerance this pass applies to a document, in user units.
     *
     * Returns the configured tolerance when no relative tolerance is set or when the
     * document exposes no usable scale, and the smaller of the two otherwise.
     *
     * @param Document $document The document to measure
     */
    public function resolveTolerance(Document $document): float
    {
        if (null === $this->relativeTolerance) {
            return $this->tolerance;
        }

        $span = self::drawingSpan($document->getRootElement());

        if (null === $span) {
            return $this->tolerance;
        }

        return min($this->tolerance, $this->relativeTolerance * $span);
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
        $simplifiedData = $this->simplifier->simplify($pathData, $this->runTolerance);

        // Update the path element with simplified data
        $pathElement->setData($simplifiedData);
    }

    /**
     * Measures the drawing span, the diagonal of the coordinate space paths are drawn in.
     *
     * The viewBox defines that space. Without one, the width and height attributes do,
     * since user units are then the units those attributes are written in. Returns null
     * when neither is usable, which leaves the caller with the absolute tolerance.
     *
     * @param SvgElement|null $root The root element to measure
     */
    private static function drawingSpan(?SvgElement $root): ?float
    {
        if (null === $root) {
            return null;
        }

        return self::spanFromViewbox($root) ?? self::spanFromDimensions($root);
    }

    /**
     * Measures the span from the viewBox attribute.
     */
    private static function spanFromViewbox(SvgElement $root): ?float
    {
        try {
            $viewbox = $root->getViewbox();
        } catch (InvalidArgumentException) {
            // A viewBox that does not parse tells us nothing about the scale.
            return null;
        }

        if (null === $viewbox) {
            return null;
        }

        return self::positiveDiagonal($viewbox->getWidth(), $viewbox->getHeight());
    }

    /**
     * Measures the span from the width and height attributes.
     */
    private static function spanFromDimensions(SvgElement $root): ?float
    {
        try {
            $width = $root->getWidth();
            $height = $root->getHeight();
        } catch (InvalidArgumentException) {
            return null;
        }

        if (null === $width || null === $height) {
            return null;
        }

        // A percentage sizes the element against its container, not the drawing.
        if ($width->isPercentage() || $height->isPercentage()) {
            return null;
        }

        return self::positiveDiagonal($width->getValue(), $height->getValue());
    }

    /**
     * Returns the diagonal of a box, or null when the box has no area to measure.
     */
    private static function positiveDiagonal(float $width, float $height): ?float
    {
        $diagonal = hypot($width, $height);

        return $diagonal > 0.0 ? $diagonal : null;
    }

    /**
     * Gets the tolerance value, in user units.
     *
     * @return float The tolerance value
     */
    public function getTolerance(): float
    {
        return $this->tolerance;
    }

    /**
     * Gets the relative tolerance, as a fraction of the drawing span.
     *
     * @return float|null The relative tolerance, or null when the tolerance is absolute
     */
    public function getRelativeTolerance(): ?float
    {
        return $this->relativeTolerance;
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
