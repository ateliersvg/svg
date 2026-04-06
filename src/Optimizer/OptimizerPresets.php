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
use Atelier\Svg\Optimizer\Pass\MergePathsPass;
use Atelier\Svg\Optimizer\Pass\MergeStylesPass;
use Atelier\Svg\Optimizer\Pass\MoveAttributesToGroupPass;
use Atelier\Svg\Optimizer\Pass\OptimizerPassInterface;
use Atelier\Svg\Optimizer\Pass\RemoveCommentsPass;
use Atelier\Svg\Optimizer\Pass\RemoveDefaultAttributesPass;
use Atelier\Svg\Optimizer\Pass\RemoveDimensionsPass;
use Atelier\Svg\Optimizer\Pass\RemoveDuplicateDefsPass;
use Atelier\Svg\Optimizer\Pass\RemoveEditorsNSDataPass;
use Atelier\Svg\Optimizer\Pass\RemoveElementsByTagNamePass;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyAttrsPass;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyElementsPass;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyGroupsPass;
use Atelier\Svg\Optimizer\Pass\RemoveHiddenElementsPass;
use Atelier\Svg\Optimizer\Pass\RemoveMetadataPass;
use Atelier\Svg\Optimizer\Pass\RemoveNonInheritableGroupAttrsPass;
use Atelier\Svg\Optimizer\Pass\RemoveRedundantSvgAttributesPass;
use Atelier\Svg\Optimizer\Pass\RemoveUnknownsAndDefaultsPass;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedClassesPass;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedDefsPass;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedNSPass;
use Atelier\Svg\Optimizer\Pass\RemoveUselessStrokeAndFillPass;
use Atelier\Svg\Optimizer\Pass\RoundValuesPass;
use Atelier\Svg\Optimizer\Pass\SimplifyPathPass;
use Atelier\Svg\Optimizer\Pass\SimplifyTransformsPass;
use Atelier\Svg\Optimizer\Pass\SortAttributesPass;
use Atelier\Svg\Path\Simplifier\Simplifier;

/**
 * Predefined optimizer configurations inspired by SVGO.
 *
 * Provides common optimization presets for different use cases:
 * - default: Balanced optimization for general use
 * - aggressive: Maximum file size reduction
 * - safe: Conservative optimizations that preserve more metadata
 * - accessible: Optimization with accessibility preservation
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
 * See PrecisionConfig for centralized precision constants and
 * docs/optimizer/optimization-strategy.md for detailed rationale.
 *
 * @see PrecisionConfig
 */
final class OptimizerPresets
{
    /**
     * Default preset - balanced optimization.
     *
     * Applies most optimizations while preserving accessibility and essential metadata.
     * Good for production use in most cases.
     *
     * @return array<OptimizerPassInterface>
     */
    public static function default(): array
    {
        return [
            // Remove unnecessary content
            new RemoveCommentsPass(),
            new RemoveMetadataPass(),  // Default: keeps desc and title
            new RemoveEditorsNSDataPass(), // Remove editor-specific metadata
            new RemoveHiddenElementsPass(
                removeDisplayNone: true,
                removeVisibilityHidden: true,
                removeOpacityZero: false, // Might be animated
            ),
            new RemoveEmptyElementsPass(),
            new RemoveEmptyGroupsPass(),
            new RemoveEmptyAttrsPass(),
            new RemoveUnusedDefsPass(),

            // Cleanup and normalize
            new CleanupAttributesPass(),
            new CleanupEnableBackgroundPass(),
            new RemoveDefaultAttributesPass(),
            new RemoveRedundantSvgAttributesPass(),
            new RemoveUnknownsAndDefaultsPass(),
            new RemoveNonInheritableGroupAttrsPass(),
            new RemoveUselessStrokeAndFillPass(),

            // Optimize values
            // Round coordinates early; use per-context precision for transforms and paths
            new RoundValuesPass(
                precision: PrecisionConfig::COORDINATE_DEFAULT,
                transformPrecision: PrecisionConfig::TRANSFORM_DEFAULT,
                pathPrecision: PrecisionConfig::PATH_DEFAULT,
            ),
            // Cleanup formatting with safety margin above rounding precision
            new CleanupNumericValuesPass(precision: PrecisionConfig::CLEANUP_DEFAULT),
            new ConvertColorsPass(
                convertToShortHex: true,
                convertToNames: true,
                convertRgb: true,
            ),

            // Optimize defs
            new RemoveDuplicateDefsPass(),

            // Structure optimization
            new MergeStylesPass(minify: true),
            new ConvertStyleToAttrsPass(onlyMatchShorthand: true),
            new MoveAttributesToGroupPass(minChildrenCount: 2),
            new CollapseGroupsPass(),
            new RemoveEmptyGroupsPass(),

            // Convert transforms (before path operations)
            new ConvertTransformPass(
                convertOnPaths: true,
                convertOnShapes: true,
            ),

            // Simplify remaining transforms with higher precision to prevent error accumulation
            new SimplifyTransformsPass(precision: PrecisionConfig::TRANSFORM_DEFAULT),

            // Simplify paths (conservative tolerance)
            new SimplifyPathPass(new Simplifier(), 0.5),

            // Convert path data late so syntax stays compact
            new ConvertPathDataPass(precision: PrecisionConfig::PATH_DEFAULT),

            // Extract common styles to classes
            new AddClassesToSVGPass(minOccurrences: 2),

            // Remove unused classes (after AddClassesToSVGPass)
            new RemoveUnusedClassesPass(),

            // Sort attributes for better compression
            new SortAttributesPass(),

            // Remove unused namespaces (after all passes that might use them)
            new RemoveUnusedNSPass(),

            // Cleanup IDs last (after all references are resolved)
            new CleanupIdsPass(
                remove: true,
                minify: false, // Don't minify by default (might break external references)
            ),

            // Final sweep for empty groups possibly created by previous passes
            new RemoveEmptyGroupsPass(),
        ];
    }

    /**
     * Aggressive preset - maximum file size reduction.
     *
     * Applies all optimizations including those that might remove useful metadata.
     * Use when file size is the absolute priority.
     *
     * @return array<OptimizerPassInterface>
     */
    public static function aggressive(): array
    {
        return [
            // Remove everything possible
            new RemoveCommentsPass(),
            new RemoveMetadataPass(),  // Remove all metadata including desc/title
            new RemoveEditorsNSDataPass(), // Remove all editor-specific metadata
            new RemoveHiddenElementsPass(
                removeDisplayNone: true,
                removeVisibilityHidden: true,
                removeOpacityZero: true,  // Remove even if might be animated
            ),
            new RemoveEmptyElementsPass(),
            new RemoveEmptyGroupsPass(),
            new RemoveEmptyAttrsPass(),
            new RemoveUnusedDefsPass(),

            // Aggressive cleanup
            new CleanupAttributesPass(),
            new CleanupEnableBackgroundPass(),
            new RemoveDefaultAttributesPass(),
            new RemoveRedundantSvgAttributesPass(),
            new RemoveUnknownsAndDefaultsPass(),
            new RemoveNonInheritableGroupAttrsPass(),
            RemoveElementsByTagNamePass::removeDesc(),
            RemoveElementsByTagNamePass::removeTitle(),
            new RemoveDimensionsPass(), // Make responsive
            new RemoveUselessStrokeAndFillPass(),

            // Maximum value optimization
            // Accept minor quality loss for maximum size reduction
            new RoundValuesPass(
                precision: PrecisionConfig::COORDINATE_AGGRESSIVE,
                transformPrecision: PrecisionConfig::TRANSFORM_AGGRESSIVE,
                pathPrecision: PrecisionConfig::PATH_AGGRESSIVE,
            ),
            // Format numbers aggressively (.5 instead of 0.5, removes leading zero)
            new CleanupNumericValuesPass(precision: PrecisionConfig::CLEANUP_AGGRESSIVE, removeLeadingZero: true),
            new ConvertColorsPass(
                convertToShortHex: true,
                convertToNames: true,
                convertRgb: true,
            ),

            // Optimize defs
            new RemoveDuplicateDefsPass(),

            // Aggressive structure optimization
            new MergeStylesPass(minify: true),
            new ConvertStyleToAttrsPass(onlyMatchShorthand: false), // Convert even if same length
            new MoveAttributesToGroupPass(minChildrenCount: 2),
            new CollapseGroupsPass(),
            new RemoveEmptyGroupsPass(),

            // Convert transforms (apply to coordinates)
            new ConvertTransformPass(
                convertOnPaths: true,
                convertOnShapes: true,
            ),

            // Simplify remaining transforms (accept small errors for size savings)
            new SimplifyTransformsPass(precision: PrecisionConfig::TRANSFORM_AGGRESSIVE),

            // Convert non-eccentric ellipses to circles (before converting to paths)
            new ConvertEllipseToCirclePass(),

            // Convert shapes to paths (enables merging)
            new ConvertShapeToPathPass(
                convertRects: true,
                convertCircles: true,
                convertEllipses: true,
                convertLines: true,
                convertPolygons: true,
                convertPolylines: true,
            ),

            // Aggressive path simplification
            new SimplifyPathPass(new Simplifier(), 1.0),

            // Optimize path data late for compact syntax
            new ConvertPathDataPass(precision: PrecisionConfig::PATH_AGGRESSIVE, removeRedundantCommands: true),

            // Merge paths (after conversion)
            new MergePathsPass(),

            // Clean up any groups that became empty after conversions
            new RemoveEmptyGroupsPass(),

            // Extract common styles to classes (aggressive: require only 2 occurrences)
            new AddClassesToSVGPass(minOccurrences: 2),

            // Remove unused classes (after AddClassesToSVGPass)
            new RemoveUnusedClassesPass(),

            // Sort attributes for better compression
            new SortAttributesPass(),

            // Remove unused namespaces (after all passes that might use them)
            new RemoveUnusedNSPass(),

            // Minify IDs
            new CleanupIdsPass(
                remove: true,
                minify: true, // Minify IDs to a, b, c, etc.
            ),

            new RemoveEmptyGroupsPass(),
        ];
    }

    /**
     * Safe preset - conservative optimizations.
     *
     * Only applies optimizations that are unlikely to cause issues.
     * Preserves all metadata, IDs, and uses conservative simplification.
     *
     * @return array<OptimizerPassInterface>
     */
    public static function safe(): array
    {
        return [
            // Only remove clearly unnecessary content
            new RemoveCommentsPass(),
            new RemoveHiddenElementsPass(
                removeDisplayNone: false, // Might be toggled via JS
                removeVisibilityHidden: false,
                removeOpacityZero: false,
            ),
            new RemoveEmptyElementsPass(),
            new RemoveEmptyGroupsPass(),
            new RemoveEmptyAttrsPass(),

            // Light cleanup
            new CleanupAttributesPass(),
            new RemoveRedundantSvgAttributesPass(),
            new RemoveUselessStrokeAndFillPass(),

            // Conservative value optimization (virtually no quality loss)
            new RoundValuesPass(
                precision: PrecisionConfig::COORDINATE_SAFE,
                transformPrecision: PrecisionConfig::TRANSFORM_SAFE,
                pathPrecision: PrecisionConfig::PATH_SAFE,
            ),
            new ConvertColorsPass(
                convertToShortHex: true,
                convertToNames: false, // Don't convert to names (might not be universally supported)
                convertRgb: true,
            ),

            // Minimal structure changes
            new MergeStylesPass(minify: false), // Don't minify CSS
            new CollapseGroupsPass(),
            new RemoveEmptyGroupsPass(),

            // Very conservative path simplification (tolerance: 0.1)
            // Extremely low tolerance preserves near-exact path shape
            new SimplifyPathPass(new Simplifier(), 0.1),

            new RemoveEmptyGroupsPass(),
            // Don't touch IDs at all
        ];
    }

    /**
     * Accessibility-focused preset.
     *
     * Optimizes while prioritizing accessibility features.
     * Preserves titles, descriptions, ARIA attributes, and readable IDs.
     *
     * @return array<OptimizerPassInterface>
     */
    public static function accessible(): array
    {
        return [
            // Remove only non-accessibility content
            new RemoveCommentsPass(),
            // Note: Don't include RemoveMetadataPass to preserve all metadata
            new RemoveHiddenElementsPass(
                removeDisplayNone: true,
                removeVisibilityHidden: true,
                removeOpacityZero: false,
            ),
            new RemoveEmptyElementsPass(),
            new RemoveEmptyGroupsPass(),
            new RemoveUnusedDefsPass(),

            // Standard cleanup
            new CleanupAttributesPass(),
            new RemoveDefaultAttributesPass(),
            new RemoveRedundantSvgAttributesPass(),
            new RemoveUselessStrokeAndFillPass(),

            // Standard value optimization
            new RoundValuesPass(
                precision: PrecisionConfig::COORDINATE_DEFAULT,
                transformPrecision: PrecisionConfig::TRANSFORM_DEFAULT,
                pathPrecision: PrecisionConfig::PATH_DEFAULT,
            ),
            new ConvertColorsPass(
                convertToShortHex: true,
                convertToNames: true,
                convertRgb: true,
            ),

            // Structure optimization
            new MergeStylesPass(minify: true),
            new ConvertStyleToAttrsPass(onlyMatchShorthand: true),
            new MoveAttributesToGroupPass(minChildrenCount: 2),
            new CollapseGroupsPass(),
            new RemoveEmptyGroupsPass(),

            // Moderate path simplification
            new SimplifyPathPass(new Simplifier(), 0.5),

            // Remove unused IDs but don't minify (keep readable)
            new CleanupIdsPass(
                remove: true,
                minify: false,
            ),

            new RemoveEmptyGroupsPass(),
        ];
    }

    /**
     * Get a preset by name.
     *
     * @param string $name One of: default, aggressive, safe, accessible
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
            'accessible' => self::accessible(),
            default => throw new InvalidArgumentException(sprintf("Unknown preset '%s'. Available: default, aggressive, safe, accessible", $name)),
        };
    }
}
