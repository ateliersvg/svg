<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Analyzes SVG documents and generates reports.
 *
 * Provides insights into document structure, size, complexity,
 * and potential optimization opportunities.
 */
final class Analyzer
{
    /**
     * Generates a comprehensive analysis report for a document.
     *
     * @return array{
     *   size: array{bytes: int, formatted: string, compressed: string, compression_ratio: float},
     *   structure: array{total_elements: int, elements_by_type: array<string, int>, max_depth: int, total_attributes: int},
     *   styles: array{inline_styles: int, presentation_attributes: int, style_elements: int, classes: int, unique_color_count: int},
     *   optimization: array{opportunities: array<string>, potential_savings: string}
     * }
     */
    public static function analyze(Document $document): array
    {
        $report = [
            'size' => self::analyzeSize($document),
            'structure' => self::analyzeStructure($document),
            'styles' => self::analyzeStyles($document),
            'optimization' => self::analyzeOptimization($document),
        ];

        return $report;
    }

    /**
     * Analyzes document size.
     *
     * @return array{bytes: int, formatted: string, compressed: string, compression_ratio: float}
     */
    public static function analyzeSize(Document $document): array
    {
        $svg = $document->toString();
        $bytes = strlen($svg);
        $encoded = gzencode($svg, 9);
        $compressed = false !== $encoded ? strlen($encoded) : 0;

        return [
            'bytes' => $bytes,
            'formatted' => self::formatBytes($bytes),
            'compressed' => self::formatBytes($compressed),
            'compression_ratio' => $bytes > 0 ? round(($compressed / $bytes) * 100, 2) : 0,
        ];
    }

    /**
     * Analyzes document structure.
     *
     * @return array{total_elements: int, elements_by_type: array<string, int>, max_depth: int, total_attributes: int}
     */
    public static function analyzeStructure(Document $document): array
    {
        $stats = [
            'total_elements' => 0,
            'elements_by_type' => [],
            'max_depth' => 0,
            'total_attributes' => 0,
        ];

        $root = $document->getRoot();
        if (null !== $root) {
            self::analyzeElement($root, $stats, 1);
        }

        return $stats;
    }

    /**
     * Recursively analyzes an element and its children.
     *
     * @param array{total_elements: int, elements_by_type: array<string, int>, max_depth: int, total_attributes: int} $stats
     */
    private static function analyzeElement(ElementInterface $element, array &$stats, int $depth): void
    {
        ++$stats['total_elements'];
        $stats['max_depth'] = max($stats['max_depth'], $depth);

        // Count by type
        $type = $element->getTagName();
        if (!isset($stats['elements_by_type'][$type])) {
            $stats['elements_by_type'][$type] = 0;
        }
        ++$stats['elements_by_type'][$type];

        // Count attributes
        if ($element instanceof AbstractElement) {
            $stats['total_attributes'] += count($element->getAttributes());
        }

        // Recurse into children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                self::analyzeElement($child, $stats, $depth + 1);
            }
        }
    }

    /**
     * Analyzes styles in the document.
     *
     * @return array{inline_styles: int, presentation_attributes: int, style_elements: int, classes: int, unique_color_count: int}
     */
    public static function analyzeStyles(Document $document): array
    {
        $stats = [
            'inline_styles' => 0,
            'presentation_attributes' => 0,
            'style_elements' => 0,
            'classes' => 0,
            'unique_colors' => [],
        ];

        $root = $document->getRoot();
        if (null !== $root) {
            self::analyzeElementStyles($root, $stats);
        }

        $stats['unique_color_count'] = count($stats['unique_colors']);
        unset($stats['unique_colors']); // Remove the array, keep only count

        /* @var array{inline_styles: int, presentation_attributes: int, style_elements: int, classes: int, unique_color_count: int} */
        return $stats;
    }

    /**
     * Recursively analyzes styles in an element and its children.
     *
     * @param array{inline_styles: int, presentation_attributes: int, style_elements: int, classes: int, unique_colors: array<string, bool>} $stats
     */
    private static function analyzeElementStyles(ElementInterface $element, array &$stats): void
    {
        assert($element instanceof AbstractElement);

        // Count inline styles
        if (null !== $element->getAttribute('style')) {
            ++$stats['inline_styles'];
        }

        // Count classes
        $stats['classes'] += count($element->getClasses());

        // Count presentation attributes
        $presentationAttrs = ['fill', 'stroke', 'opacity', 'transform'];
        foreach ($presentationAttrs as $attr) {
            if (null !== $element->getAttribute($attr)) {
                ++$stats['presentation_attributes'];
            }
        }

        // Extract colors
        foreach (['fill', 'stroke'] as $attr) {
            $value = $element->getAttribute($attr);
            if (null !== $value && 'none' !== $value) {
                $stats['unique_colors'][$value] = true;
            }
        }

        // Count style elements
        if ('style' === $element->getTagName()) {
            ++$stats['style_elements'];
        }

        // Recurse into children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                self::analyzeElementStyles($child, $stats);
            }
        }
    }

    /**
     * Analyzes potential optimizations.
     *
     * @return array{opportunities: array<string>, potential_savings: string}
     */
    public static function analyzeOptimization(Document $document): array
    {
        $opportunities = [];

        // Count elements that could be optimized
        $emptyElements = 0;
        $defaultAttributes = 0;
        $redundantGroups = 0;

        $root = $document->getRoot();
        if (null !== $root) {
            self::countOptimizationOpportunities(
                $root,
                $emptyElements,
                $defaultAttributes,
                $redundantGroups
            );
        }

        if ($emptyElements > 0) {
            $opportunities[] = "Remove {$emptyElements} empty elements";
        }

        if ($defaultAttributes > 0) {
            $opportunities[] = "Remove {$defaultAttributes} default attributes";
        }

        if ($redundantGroups > 0) {
            $opportunities[] = "Collapse {$redundantGroups} redundant groups";
        }

        return [
            'opportunities' => $opportunities,
            'potential_savings' => count($opportunities) > 0 ? 'medium' : 'low',
        ];
    }

    /**
     * Counts optimization opportunities.
     */
    private static function countOptimizationOpportunities(
        ElementInterface $element,
        int &$emptyElements,
        int &$defaultAttributes,
        int &$redundantGroups,
    ): void {
        assert($element instanceof AbstractElement);

        // Check for empty elements
        if ($element instanceof ContainerElementInterface && 0 === count($element->getChildren())) {
            ++$emptyElements;
        }

        // Check for default attributes (simplified check)
        $defaults = ['fill' => 'black', 'stroke' => 'none'];
        foreach ($defaults as $attr => $defaultValue) {
            if ($element->getAttribute($attr) === $defaultValue) {
                ++$defaultAttributes;
            }
        }

        // Check for redundant groups (groups with only one child)
        if ('g' === $element->getTagName() && $element instanceof ContainerElementInterface) {
            if (1 === count($element->getChildren())) {
                ++$redundantGroups;
            }
        }

        // Recurse into children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                self::countOptimizationOpportunities(
                    $child,
                    $emptyElements,
                    $defaultAttributes,
                    $redundantGroups
                );
            }
        }
    }

    /**
     * Formats bytes into a human-readable string.
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = $bytes;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            ++$i;
        }

        return round($size, 2).' '.$units[$i];
    }

    /**
     * Prints a formatted analysis report.
     */
    public static function printReport(Document $document): string
    {
        $report = self::analyze($document);

        $output = "SVG Document Analysis Report\n";
        $output .= "============================\n\n";

        $output .= "Size:\n";
        $output .= "  Original: {$report['size']['formatted']}\n";
        $output .= "  Compressed: {$report['size']['compressed']}\n";
        $output .= "  Compression Ratio: {$report['size']['compression_ratio']}%\n\n";

        $output .= "Structure:\n";
        $output .= "  Total Elements: {$report['structure']['total_elements']}\n";
        $output .= "  Max Depth: {$report['structure']['max_depth']}\n";
        $output .= "  Total Attributes: {$report['structure']['total_attributes']}\n\n";

        $output .= "Elements by Type:\n";
        foreach ($report['structure']['elements_by_type'] as $type => $count) {
            $output .= "  {$type}: {$count}\n";
        }
        $output .= "\n";

        $output .= "Styles:\n";
        $output .= "  Inline Styles: {$report['styles']['inline_styles']}\n";
        $output .= "  Presentation Attributes: {$report['styles']['presentation_attributes']}\n";
        $output .= "  Style Elements: {$report['styles']['style_elements']}\n";
        $output .= "  Classes: {$report['styles']['classes']}\n";
        $output .= "  Unique Colors: {$report['styles']['unique_color_count']}\n\n";

        $output .= "Optimization Opportunities:\n";
        if (empty($report['optimization']['opportunities'])) {
            $output .= "  No major opportunities found.\n";
        } else {
            foreach ($report['optimization']['opportunities'] as $opportunity) {
                $output .= "  - {$opportunity}\n";
            }
        }

        return $output;
    }
}
