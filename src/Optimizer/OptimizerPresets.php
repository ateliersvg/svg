<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Optimizer\Pass\AddClassesToSVGPass;
use Atelier\Svg\Optimizer\Pass\CleanupAttributesPass;
use Atelier\Svg\Optimizer\Pass\CleanupEnableBackgroundPass;
use Atelier\Svg\Optimizer\Pass\CleanupIdsPass;
use Atelier\Svg\Optimizer\Pass\CleanupNumericValuesPass;
use Atelier\Svg\Optimizer\Pass\CollapseGroupsPass;
use Atelier\Svg\Optimizer\Pass\ConvertColorsPass;
use Atelier\Svg\Optimizer\Pass\ConvertEllipseToCirclePass;
use Atelier\Svg\Optimizer\Pass\ConvertPathDataPass;
use Atelier\Svg\Optimizer\Pass\ConvertShapeToPathPass;
use Atelier\Svg\Optimizer\Pass\ConvertStyleToAttrsPass;
use Atelier\Svg\Optimizer\Pass\ConvertTransformPass;
use Atelier\Svg\Optimizer\Pass\InlineStylesPass;
use Atelier\Svg\Optimizer\Pass\MergePathsPass;
use Atelier\Svg\Optimizer\Pass\MergeStylesPass;
use Atelier\Svg\Optimizer\Pass\MoveAttributesToGroupPass;
use Atelier\Svg\Optimizer\Pass\MoveGroupAttrsToElemsPass;
use Atelier\Svg\Optimizer\Pass\OptimizerPassInterface;
use Atelier\Svg\Optimizer\Pass\RemoveCommentsPass;
use Atelier\Svg\Optimizer\Pass\RemoveDefaultAttributesPass;
use Atelier\Svg\Optimizer\Pass\RemoveDescPass;
use Atelier\Svg\Optimizer\Pass\RemoveDimensionsPass;
use Atelier\Svg\Optimizer\Pass\RemoveDoctypePass;
use Atelier\Svg\Optimizer\Pass\RemoveDuplicateDefsPass;
use Atelier\Svg\Optimizer\Pass\RemoveEditorsNSDataPass;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyAttrsPass;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyElementsPass;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyGroupsPass;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyTextPass;
use Atelier\Svg\Optimizer\Pass\RemoveHiddenElementsPass;
use Atelier\Svg\Optimizer\Pass\RemoveMetadataPass;
use Atelier\Svg\Optimizer\Pass\RemoveNonInheritableGroupAttrsPass;
use Atelier\Svg\Optimizer\Pass\RemoveRedundantSvgAttributesPass;
use Atelier\Svg\Optimizer\Pass\RemoveTitlePass;
use Atelier\Svg\Optimizer\Pass\RemoveUnknownsAndDefaultsPass;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedClassesPass;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedDefsPass;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedNSPass;
use Atelier\Svg\Optimizer\Pass\RemoveUselessStrokeAndFillPass;
use Atelier\Svg\Optimizer\Pass\RemoveXMLProcInstPass;
use Atelier\Svg\Optimizer\Pass\RoundValuesPass;
use Atelier\Svg\Optimizer\Pass\SimplifyPathPass;
use Atelier\Svg\Optimizer\Pass\SimplifyTransformsPass;
use Atelier\Svg\Optimizer\Pass\SortAttributesPass;
use Atelier\Svg\Optimizer\Pass\SortDefsChildrenPass;
use Atelier\Svg\Path\Simplifier\Simplifier;

/**
 * Predefined optimizer configurations inspired by SVGO.
 *
 * Four presets forming a clear gradient from conservative to maximum compression:
 *
 * - **safe**: Conservative, preserves metadata/IDs/structure. For VCS, design tools, scripted SVGs.
 * - **default**: Balanced optimization for general use. The recommended starting point.
 * - **web**: Aggressive for production delivery. Strips title/desc/dimensions, merges paths.
 * - **aggressive**: Maximum file size reduction. Integer-only coordinates, lossy simplification.
 *
 * ```
 * safe  <  default  <  web  <  aggressive
 * preserve                     compress
 * ```
 *
 * ## Precision Strategy
 *
 * Each preset uses carefully chosen precision values for different contexts:
 * - **Coordinates**: 1-3 decimals (balance visual quality vs file size)
 * - **Transforms**: 2-4 decimals (higher precision prevents error accumulation)
 * - **Path data**: 2-4 decimals (preserve curve smoothness)
 *
 * Multiple passes handle precision:
 * 1. RoundValuesPass: Primary rounding of coordinates
 * 2. CleanupNumericValuesPass: Format numbers (trailing zeros, etc.)
 * 3. SimplifyTransformsPass: Round transform matrices with higher precision
 * 4. ConvertPathDataPass: Optimize path data with curve-appropriate precision
 *
 * @see PrecisionConfig
 */
final class OptimizerPresets
{
    /**
     * Default preset - balanced optimization.
     *
     * The recommended starting point. Applies most optimizations while preserving
     * `<title>` elements and essential structure. Removes metadata, descriptions,
     * editor data, and unused elements.
     *
     * Pipeline phases:
     * 1. Cleanup   - remove junk (doctype, XML PI, comments, metadata, editor data)
     * 2. Normalize - inline styles, cleanup attributes, remove defaults
     * 3. Optimize  - round numbers, convert colors
     * 4. Structure - merge styles, move attrs, collapse groups
     * 5. Convert   - transforms, shapes, paths
     * 6. Finalize  - sort attrs, cleanup IDs, remove unused NS
     *
     * @return array<OptimizerPassInterface>
     */
    public static function default(): array
    {
        return [
            // -- Phase 1: Cleanup --
            new RemoveDoctypePass(),
            new RemoveXMLProcInstPass(),
            new RemoveCommentsPass(),
            new RemoveMetadataPass(),
            new RemoveEditorsNSDataPass(),
            new RemoveDescPass(),
            new RemoveHiddenElementsPass(
                removeDisplayNone: true,
                removeVisibilityHidden: true,
                removeOpacityZero: false, // Might be animated
            ),
            new RemoveEmptyElementsPass(),
            new RemoveEmptyTextPass(),
            new RemoveEmptyGroupsPass(),
            new RemoveEmptyAttrsPass(),
            new RemoveUnusedDefsPass(),

            // -- Phase 2: Normalize --
            new InlineStylesPass(),
            new CleanupAttributesPass(),
            new CleanupEnableBackgroundPass(),
            new RemoveDefaultAttributesPass(),
            new RemoveRedundantSvgAttributesPass(),
            new RemoveUnknownsAndDefaultsPass(),
            new RemoveNonInheritableGroupAttrsPass(),
            new RemoveUselessStrokeAndFillPass(),

            // -- Phase 3: Optimize values --
            new RoundValuesPass(
                precision: PrecisionConfig::COORDINATE_DEFAULT,
                transformPrecision: PrecisionConfig::TRANSFORM_DEFAULT,
                pathPrecision: PrecisionConfig::PATH_DEFAULT,
            ),
            new CleanupNumericValuesPass(precision: PrecisionConfig::CLEANUP_DEFAULT),
            new ConvertColorsPass(
                convertToShortHex: true,
                convertToNames: true,
                convertRgb: true,
            ),

            // -- Phase 4: Structure --
            new RemoveDuplicateDefsPass(),
            new MergeStylesPass(minify: true),
            new ConvertStyleToAttrsPass(onlyMatchShorthand: true),
            new MoveAttributesToGroupPass(minChildrenCount: 2),
            new MoveGroupAttrsToElemsPass(),
            new CollapseGroupsPass(),
            new RemoveEmptyGroupsPass(),

            // -- Phase 5: Convert --
            new ConvertTransformPass(
                convertOnPaths: true,
                convertOnShapes: true,
            ),
            new SimplifyTransformsPass(precision: PrecisionConfig::TRANSFORM_DEFAULT),
            new ConvertEllipseToCirclePass(),
            new SimplifyPathPass(new Simplifier(), 0.5),
            new ConvertPathDataPass(precision: PrecisionConfig::PATH_DEFAULT),

            // -- Phase 6: Finalize --
            new AddClassesToSVGPass(minOccurrences: 3),
            new RemoveUnusedClassesPass(),
            new SortAttributesPass(),
            new SortDefsChildrenPass(),
            new RemoveUnusedNSPass(),
            new CleanupIdsPass(
                remove: true,
                minify: false,
            ),
            new RemoveEmptyGroupsPass(),
        ];
    }

    /**
     * Aggressive preset - maximum file size reduction.
     *
     * Applies all optimizations including lossy ones. Removes all metadata,
     * titles, descriptions, and dimensions. Converts shapes to paths for merging.
     * Uses precision 0 for integer-only coordinates.
     *
     * Use when file size is the absolute priority and visual fidelity can be
     * slightly degraded.
     *
     * @return array<OptimizerPassInterface>
     */
    public static function aggressive(): array
    {
        return [
            // -- Phase 1: Cleanup --
            new RemoveDoctypePass(),
            new RemoveXMLProcInstPass(),
            new RemoveCommentsPass(),
            new RemoveMetadataPass(),
            new RemoveEditorsNSDataPass(),
            new RemoveDescPass(),
            new RemoveTitlePass(),
            new RemoveHiddenElementsPass(
                removeDisplayNone: true,
                removeVisibilityHidden: true,
                removeOpacityZero: true,
            ),
            new RemoveEmptyElementsPass(),
            new RemoveEmptyTextPass(),
            new RemoveEmptyGroupsPass(),
            new RemoveEmptyAttrsPass(),
            new RemoveUnusedDefsPass(),
            new RemoveDimensionsPass(),

            // -- Phase 2: Normalize --
            new InlineStylesPass(),
            new CleanupAttributesPass(),
            new CleanupEnableBackgroundPass(),
            new RemoveDefaultAttributesPass(),
            new RemoveRedundantSvgAttributesPass(),
            new RemoveUnknownsAndDefaultsPass(),
            new RemoveNonInheritableGroupAttrsPass(),
            new RemoveUselessStrokeAndFillPass(),

            // -- Phase 3: Optimize values --
            // Precision 0 = integer-only coordinates for maximum compression
            new RoundValuesPass(
                precision: 0,
                transformPrecision: PrecisionConfig::TRANSFORM_AGGRESSIVE,
                pathPrecision: 0,
            ),
            new CleanupNumericValuesPass(precision: 0, removeLeadingZero: true),
            new ConvertColorsPass(
                convertToShortHex: true,
                convertToNames: true,
                convertRgb: true,
            ),

            // -- Phase 4: Structure --
            new RemoveDuplicateDefsPass(),
            new MergeStylesPass(minify: true),
            new ConvertStyleToAttrsPass(onlyMatchShorthand: false),
            new MoveAttributesToGroupPass(minChildrenCount: 2),
            new MoveGroupAttrsToElemsPass(),
            new CollapseGroupsPass(),
            new RemoveEmptyGroupsPass(),

            // -- Phase 5: Convert --
            new ConvertTransformPass(
                convertOnPaths: true,
                convertOnShapes: true,
            ),
            new SimplifyTransformsPass(precision: PrecisionConfig::TRANSFORM_AGGRESSIVE),
            new ConvertEllipseToCirclePass(),
            new ConvertShapeToPathPass(
                convertRects: true,
                convertCircles: true,
                convertEllipses: true,
                convertLines: true,
                convertPolygons: true,
                convertPolylines: true,
            ),
            new SimplifyPathPass(new Simplifier(), 2.0),
            new ConvertPathDataPass(precision: 0, removeRedundantCommands: true),
            new MergePathsPass(),

            // -- Phase 6: Finalize --
            new AddClassesToSVGPass(minOccurrences: 2),
            new RemoveUnusedClassesPass(),
            new SortAttributesPass(),
            new SortDefsChildrenPass(),
            new RemoveUnusedNSPass(),
            new CleanupIdsPass(
                remove: true,
                minify: true,
            ),
            new RemoveEmptyGroupsPass(),
        ];
    }

    /**
     * Safe preset - conservative optimizations.
     *
     * Only applies optimizations unlikely to cause visual or behavioral differences.
     * Preserves all metadata, IDs, class names, titles, descriptions, and uses
     * conservative precision. No style inlining, no shape conversion, no path rewriting.
     *
     * Suitable for version-controlled SVGs, design tool interchange (Figma, Sketch,
     * Illustrator), and SVGs that may be scripted or externally referenced.
     *
     * @return array<OptimizerPassInterface>
     */
    public static function safe(): array
    {
        return [
            // -- Phase 1: Cleanup (safe removals only) --
            new RemoveDoctypePass(),
            new RemoveXMLProcInstPass(),
            new RemoveCommentsPass(),
            new RemoveEditorsNSDataPass(),
            new RemoveHiddenElementsPass(
                removeDisplayNone: false, // Might be toggled via JS
                removeVisibilityHidden: false,
                removeOpacityZero: false,
            ),
            new RemoveEmptyElementsPass(),
            new RemoveEmptyGroupsPass(),
            new RemoveEmptyAttrsPass(),
            new RemoveUnusedDefsPass(),
            new RemoveDuplicateDefsPass(),

            // -- Phase 2: Normalize (lossless attribute cleanup) --
            new CleanupAttributesPass(),
            new CleanupEnableBackgroundPass(),
            new RemoveDefaultAttributesPass(),
            new RemoveRedundantSvgAttributesPass(),
            new RemoveUnknownsAndDefaultsPass(),
            new RemoveNonInheritableGroupAttrsPass(),
            new RemoveUselessStrokeAndFillPass(),

            // -- Phase 3: Optimize values (conservative precision) --
            new RoundValuesPass(
                precision: PrecisionConfig::COORDINATE_SAFE,
                transformPrecision: PrecisionConfig::TRANSFORM_SAFE,
                pathPrecision: PrecisionConfig::PATH_SAFE,
            ),
            new CleanupNumericValuesPass(precision: PrecisionConfig::CLEANUP_SAFE),
            new ConvertColorsPass(
                convertToShortHex: true,
                convertToNames: false,
                convertRgb: true,
            ),

            // -- Phase 4: Structure (minimal changes) --
            new MergeStylesPass(minify: false),
            new CollapseGroupsPass(),
            new RemoveEmptyGroupsPass(),

            // -- Phase 5: Convert (very conservative) --
            new ConvertEllipseToCirclePass(),
            new SimplifyPathPass(new Simplifier(), 0.1),
            new ConvertPathDataPass(precision: PrecisionConfig::PATH_SAFE),

            // -- Phase 6: Finalize --
            new SortAttributesPass(),
            new RemoveUnusedNSPass(),
            new RemoveEmptyGroupsPass(),
            // Don't touch IDs at all
        ];
    }

    /**
     * Web preset - optimized for production web delivery.
     *
     * Targets SVGs served via `<img>`, inline SVG, CSS backgrounds, or icon systems.
     * Strips titles, descriptions, dimensions, and metadata. Converts shapes to paths
     * for merging, uses aggressive precision, and minifies IDs.
     *
     * Accessibility is assumed to be handled by the surrounding HTML context.
     *
     * @return array<OptimizerPassInterface>
     */
    public static function web(): array
    {
        return [
            // -- Phase 1: Cleanup --
            new RemoveDoctypePass(),
            new RemoveXMLProcInstPass(),
            new RemoveCommentsPass(),
            new RemoveMetadataPass(),
            new RemoveEditorsNSDataPass(),
            new RemoveDescPass(),
            new RemoveTitlePass(),
            new RemoveHiddenElementsPass(
                removeDisplayNone: true,
                removeVisibilityHidden: true,
                removeOpacityZero: true,
            ),
            new RemoveEmptyElementsPass(),
            new RemoveEmptyTextPass(),
            new RemoveEmptyGroupsPass(),
            new RemoveEmptyAttrsPass(),
            new RemoveUnusedDefsPass(),
            new RemoveDimensionsPass(),

            // -- Phase 2: Normalize --
            new InlineStylesPass(),
            new CleanupAttributesPass(),
            new CleanupEnableBackgroundPass(),
            new RemoveDefaultAttributesPass(),
            new RemoveRedundantSvgAttributesPass(),
            new RemoveUnknownsAndDefaultsPass(),
            new RemoveNonInheritableGroupAttrsPass(),
            new RemoveUselessStrokeAndFillPass(),

            // -- Phase 3: Optimize values (aggressive precision) --
            new RoundValuesPass(
                precision: PrecisionConfig::COORDINATE_AGGRESSIVE,
                transformPrecision: PrecisionConfig::TRANSFORM_AGGRESSIVE,
                pathPrecision: PrecisionConfig::PATH_AGGRESSIVE,
            ),
            new CleanupNumericValuesPass(precision: PrecisionConfig::CLEANUP_AGGRESSIVE, removeLeadingZero: true),
            new ConvertColorsPass(
                convertToShortHex: true,
                convertToNames: true,
                convertRgb: true,
            ),

            // -- Phase 4: Structure --
            new RemoveDuplicateDefsPass(),
            new MergeStylesPass(minify: true),
            new ConvertStyleToAttrsPass(onlyMatchShorthand: false),
            new MoveAttributesToGroupPass(minChildrenCount: 2),
            new MoveGroupAttrsToElemsPass(),
            new CollapseGroupsPass(),
            new RemoveEmptyGroupsPass(),

            // -- Phase 5: Convert (shapes to paths for merging) --
            new ConvertTransformPass(
                convertOnPaths: true,
                convertOnShapes: true,
            ),
            new SimplifyTransformsPass(precision: PrecisionConfig::TRANSFORM_AGGRESSIVE),
            new ConvertEllipseToCirclePass(),
            new ConvertShapeToPathPass(
                convertRects: true,
                convertCircles: true,
                convertEllipses: true,
                convertLines: true,
                convertPolygons: true,
                convertPolylines: true,
            ),
            new SimplifyPathPass(new Simplifier(), 1.0),
            new ConvertPathDataPass(precision: PrecisionConfig::PATH_AGGRESSIVE, removeRedundantCommands: true),
            new MergePathsPass(),

            // -- Phase 6: Finalize --
            new AddClassesToSVGPass(minOccurrences: 2),
            new RemoveUnusedClassesPass(),
            new SortAttributesPass(),
            new SortDefsChildrenPass(),
            new RemoveUnusedNSPass(),
            new CleanupIdsPass(
                remove: true,
                minify: true,
            ),
            new RemoveEmptyGroupsPass(),
        ];
    }

    /**
     * Get a preset by name.
     *
     * @param string $name One of: default, aggressive, safe, web
     *
     * @return array<OptimizerPassInterface>
     *
     * @throws InvalidArgumentException if preset name is unknown
     */
    public static function get(string $name): array
    {
        return match ($name) {
            'default' => self::default(),
            'aggressive' => self::aggressive(),
            'safe' => self::safe(),
            'web' => self::web(),
            default => throw new InvalidArgumentException(sprintf("Unknown preset '%s'. Available: default, aggressive, safe, web", $name)),
        };
    }
}
