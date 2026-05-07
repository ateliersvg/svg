<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer;

use Atelier\Svg\Document;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Optimizer\Pass\OptimizerPassInterface;

/**
 * Applies multiple optimization passes to SVG documents.
 *
 * The Optimizer orchestrates a pipeline of optimization passes, applying them
 * sequentially to transform and optimize SVG documents. Each pass implements
 * a specific optimization strategy such as removing empty elements, merging
 * paths, or converting shapes to paths.
 *
 * Passes are executed in the order they are added, allowing for sophisticated
 * multi-stage optimizations where the output of one pass becomes the input
 * for the next. This design enables:
 *
 * - Composable optimization strategies
 * - Fine-grained control over which optimizations to apply
 * - Extensibility through custom pass implementations
 * - Predictable, deterministic optimization results
 *
 * Example usage:
 * ```php
 * $optimizer = new Optimizer([
 *     new RemoveEmptyElementsPass(),
 *     new MergePathsPass(),
 *     new RoundValuesPass(2),
 * ]);
 *
 * $optimized = $optimizer->optimize($document);
 * ```
 *
 * For common optimization configurations, see OptimizerPresets.
 *
 * @see OptimizerPresets For pre-configured optimization pipelines
 * @see OptimizerPassInterface For implementing custom optimization passes
 */
final class Optimizer implements OptimizerInterface
{
    /**
     * Creates a new Optimizer with the given optimization passes.
     *
     * @param array<OptimizerPassInterface> $passes The optimization passes to apply
     */
    public function __construct(private array $passes = [])
    {
    }

    /**
     * Optimizes a document with the given preset and options.
     *
     * @param Document             $document The document to optimize
     * @param string               $preset   One of: default, aggressive, safe, web
     * @param array<string, mixed> $options  Additional optimization options
     *
     * @return Document The optimized document
     *
     * @throws InvalidArgumentException If the preset name is unknown
     */
    public static function forDocument(
        Document $document,
        string $preset = 'default',
        array $options = [],
    ): Document {
        $passes = OptimizerPresets::get($preset);
        $optimizer = new self($passes);

        return $optimizer->optimize($document);
    }

    /**
     * Removes unnecessary metadata from a document.
     */
    public static function removeMetadata(Document $document): Document
    {
        $optimizer = new self([
            new Pass\RemoveCommentsPass(),
            new Pass\RemoveMetadataPass(),
            new Pass\RemoveDescPass(),
            new Pass\RemoveTitlePass(),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Removes unused definitions and references.
     */
    public static function cleanupDefs(Document $document): Document
    {
        $optimizer = new self([
            new Pass\RemoveUnusedDefsPass(),
            new Pass\RemoveDuplicateDefsPass(),
            new Pass\RemoveUnusedClassesPass(),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Rounds numeric values to reduce file size.
     *
     * @param int $precision Number of decimal places to keep
     */
    public static function roundValues(Document $document, int $precision = 2): Document
    {
        $optimizer = new self([
            new Pass\RoundValuesPass($precision),
            new Pass\CleanupNumericValuesPass($precision),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Optimizes colors (convert to shortest form).
     */
    public static function optimizeColors(Document $document): Document
    {
        $optimizer = new self([
            new Pass\ConvertColorsPass(
                convertToShortHex: true,
                convertToNames: true,
                convertRgb: true,
            ),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Converts inline styles to attributes.
     */
    public static function inlineStyles(Document $document): Document
    {
        $optimizer = new self([
            new Pass\ConvertStyleToAttrsPass(
                onlyMatchShorthand: false
            ),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Converts attributes to inline styles.
     */
    public static function extractStyles(Document $document): Document
    {
        $optimizer = new self([
            new Pass\MergeStylesPass(minify: true),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Simplifies paths by removing redundant points.
     *
     * @param float $tolerance Simplification tolerance (higher = more aggressive)
     */
    public static function simplifyPaths(Document $document, float $tolerance = 0.5): Document
    {
        $optimizer = new self([
            new Pass\SimplifyPathPass(
                new \Atelier\Svg\Path\Simplifier\Simplifier(),
                $tolerance
            ),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Removes hidden and empty elements.
     */
    public static function removeHidden(Document $document): Document
    {
        $optimizer = new self([
            new Pass\RemoveHiddenElementsPass(
                removeDisplayNone: true,
                removeVisibilityHidden: true,
                removeOpacityZero: true,
            ),
            new Pass\RemoveEmptyElementsPass(),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Merges paths where possible.
     */
    public static function mergePaths(Document $document): Document
    {
        $optimizer = new self([
            new Pass\MergePathsPass(),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Collapses unnecessary groups.
     */
    public static function collapseGroups(Document $document): Document
    {
        $optimizer = new self([
            new Pass\CollapseGroupsPass(),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Cleans up and minifies IDs.
     *
     * @param bool $minify Whether to minify IDs to short names (a, b, c, etc.)
     */
    public static function cleanupIds(Document $document, bool $minify = false): Document
    {
        $optimizer = new self([
            new Pass\CleanupIdsPass(
                remove: true,
                minify: $minify,
            ),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Removes default attributes that match SVG defaults.
     */
    public static function removeDefaults(Document $document): Document
    {
        $optimizer = new self([
            new Pass\RemoveDefaultAttributesPass(),
            new Pass\RemoveUnknownsAndDefaultsPass(),
        ]);

        return $optimizer->optimize($document);
    }

    /**
     * Adds an optimization pass to the end of the pass pipeline.
     *
     * Passes are executed in the order they are added. Consider the
     * dependencies and interactions between passes when ordering them.
     * For example, path simplification should typically run after
     * converting shapes to paths.
     *
     * @param OptimizerPassInterface $pass The optimization pass to add
     *
     * @return self Returns this optimizer for method chaining
     */
    public function addPass(OptimizerPassInterface $pass): self
    {
        $this->passes[] = $pass;

        return $this;
    }

    /**
     * Gets all optimization passes.
     *
     * @return array<OptimizerPassInterface>
     */
    public function getPasses(): array
    {
        return $this->passes;
    }

    /**
     * Optimizes an SVG document by applying all configured passes.
     *
     * Executes each optimization pass in sequence. The passes modify the
     * document in place, so the original document is transformed. If you
     * need to preserve the original, clone it before optimizing.
     *
     * The method returns the document for convenience, but note that it's
     * the same instance that was passed in (not a copy).
     *
     * @param Document $document The document to optimize (modified in place)
     *
     * @return Document The same document instance, now optimized
     */
    public function optimize(Document $document): Document
    {
        foreach ($this->passes as $pass) {
            $pass->optimize($document);
        }

        return $document;
    }
}
