<?php

declare(strict_types=1);

namespace Atelier\Svg\Validation;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Visitor\CallbackVisitor;
use Atelier\Svg\Visitor\Traverser;

/**
 * Utility class for validating and linting SVG documents.
 *
 * Provides methods to check for common issues, validate references,
 * and suggest improvements.
 *
 * Example usage:
 * ```php
 * // Validate a document
 * $errors = DocumentValidator::validate($doc);
 * if (empty($errors)) {
 *     echo "Document is valid!";
 * }
 *
 * // Lint with specific checks
 * $warnings = DocumentValidator::lint($doc, [
 *     'check_ids' => true,
 *     'check_references' => true,
 * ]);
 *
 * // Find broken references
 * $broken = DocumentValidator::findBrokenReferences($doc);
 * ```
 */
final class DocumentValidator
{
    /**
     * Validate an SVG document.
     *
     * Performs basic validation checks and returns an array of errors.
     *
     * @return array<string> Array of error messages
     */
    public static function validate(Document $document): array
    {
        $errors = [];

        $root = $document->getRootElement();
        if (null === $root) {
            $errors[] = 'Document has no root element';

            return $errors;
        }

        // Check for duplicate IDs
        $duplicates = self::findDuplicateIds($document);
        foreach ($duplicates as $id => $count) {
            $errors[] = "Duplicate ID '{$id}' appears {$count} times";
        }

        // Check for broken references
        $brokenRefs = self::findBrokenReferences($document);
        foreach ($brokenRefs as $ref) {
            $errors[] = "Broken reference to '#{$ref['id']}' in element <{$ref['element']}>";
        }

        return $errors;
    }

    /**
     * Check if a document is valid.
     *
     * @return bool True if valid, false otherwise
     */
    public static function isValid(Document $document): bool
    {
        return empty(self::validate($document));
    }

    /**
     * Lint an SVG document with configurable checks.
     *
     * @param array<string, bool> $options Linting options
     *
     * @return array<array{type: string, message: string, severity: string}> Array of warnings
     */
    public static function lint(Document $document, array $options = []): array
    {
        $warnings = [];

        $defaults = [
            'check_ids' => true,
            'check_references' => true,
            'check_colors' => true,
            'check_transforms' => true,
            'check_accessibility' => true,
        ];

        $options = array_merge($defaults, $options);

        if ($options['check_ids']) {
            $warnings = array_merge($warnings, self::lintIds($document));
        }

        if ($options['check_references']) {
            $warnings = array_merge($warnings, self::lintReferences($document));
        }

        if ($options['check_colors']) {
            $warnings = array_merge($warnings, self::lintColors($document));
        }

        if ($options['check_transforms']) {
            $warnings = array_merge($warnings, self::lintTransforms($document));
        }

        if ($options['check_accessibility']) {
            $warnings = array_merge($warnings, self::lintAccessibility($document));
        }

        return $warnings;
    }

    /**
     * Find all broken references in a document.
     *
     * @return array<array{id: string, element: string, attribute: string}> Broken references
     */
    public static function findBrokenReferences(Document $document): array
    {
        $broken = [];
        $allIds = self::getAllIds($document);

        $visitor = new CallbackVisitor(function (ElementInterface $element) use (&$broken, $allIds): bool {
            // Check common reference attributes
            $refAttributes = [
                'href',
                'xlink:href',
                'fill',
                'stroke',
                'filter',
                'clip-path',
                'mask',
                'marker-start',
                'marker-mid',
                'marker-end',
            ];

            foreach ($refAttributes as $attr) {
                $value = $element->getAttribute($attr);
                if (null === $value) {
                    continue;
                }

                // Extract ID from url(#id) or #id format
                $id = null;
                if (preg_match('/url\(#(.+?)\)/', $value, $matches)) {
                    $id = $matches[1];
                } elseif (str_starts_with($value, '#')) {
                    $id = substr($value, 1);
                }

                if (null !== $id && !in_array($id, $allIds, true)) {
                    $broken[] = [
                        'id' => $id,
                        'element' => $element->getTagName(),
                        'attribute' => $attr,
                    ];
                }
            }

            return true; // Continue traversal
        });

        $traverser = new Traverser($visitor);
        $root = $document->getRootElement();
        if (null !== $root) {
            $traverser->traverse($root);
        }

        return $broken;
    }

    /**
     * Update all references from one ID to another.
     *
     * @param Document $document The document
     * @param string   $oldId    Old ID
     * @param string   $newId    New ID
     *
     * @return int Number of references updated
     */
    public static function updateReferences(Document $document, string $oldId, string $newId): int
    {
        $count = 0;

        $visitor = new CallbackVisitor(function (ElementInterface $element) use ($oldId, $newId, &$count): bool {
            $refAttributes = [
                'href',
                'xlink:href',
                'fill',
                'stroke',
                'filter',
                'clip-path',
                'mask',
                'marker-start',
                'marker-mid',
                'marker-end',
            ];

            foreach ($refAttributes as $attr) {
                $value = $element->getAttribute($attr);
                if (null === $value) {
                    continue;
                }

                // Replace in url(#id) format
                $newValue = preg_replace(
                    '/url\(#'.preg_quote($oldId, '/').'\)/',
                    "url(#{$newId})",
                    $value
                );

                // Replace in #id format
                if ($value === "#{$oldId}") {
                    $newValue = "#{$newId}";
                }

                if ($newValue !== $value) {
                    $element->setAttribute($attr, (string) $newValue);
                    ++$count;
                }
            }

            return true;
        });

        $traverser = new Traverser($visitor);
        $root = $document->getRootElement();
        if (null !== $root) {
            $traverser->traverse($root);
        }

        return $count;
    }

    /**
     * Automatically fixes broken references by removing them.
     *
     * @param Document $document The document to fix
     *
     * @return int Number of broken references fixed
     */
    public static function fixBrokenReferences(Document $document): int
    {
        $count = 0;
        $broken = self::findBrokenReferences($document);

        $visitor = new CallbackVisitor(function (ElementInterface $element) use ($broken, &$count): bool {
            foreach ($broken as $ref) {
                $value = $element->getAttribute($ref['attribute']);
                if (null === $value) {
                    continue;
                }

                // Remove broken url(#id) references
                if (preg_match('/url\(#'.preg_quote($ref['id'], '/').'\)/', $value)) {
                    $element->removeAttribute($ref['attribute']);
                    ++$count;
                }

                // Remove broken #id references
                if ($value === "#{$ref['id']}") {
                    $element->removeAttribute($ref['attribute']);
                    ++$count;
                }
            }

            return true;
        });

        $traverser = new Traverser($visitor);
        $root = $document->getRootElement();
        if (null !== $root) {
            $traverser->traverse($root);
        }

        return $count;
    }

    /**
     * Automatically fixes duplicate IDs by renaming duplicates.
     *
     * @param Document $document The document to fix
     *
     * @return int Number of duplicate IDs fixed
     */
    public static function fixDuplicateIds(Document $document): int
    {
        $duplicates = self::findDuplicateIds($document);
        if (empty($duplicates)) {
            return 0;
        }

        $count = 0;

        foreach (array_keys($duplicates) as $duplicateId) {
            // Find all elements with this ID
            $elements = [];
            $visitor = new CallbackVisitor(function (ElementInterface $element) use ($duplicateId, &$elements): bool {
                if ($element->getId() === $duplicateId) {
                    $elements[] = $element;
                }

                return true;
            });

            $traverser = new Traverser($visitor);
            $root = $document->getRootElement();
            if (null !== $root) {
                $traverser->traverse($root);
            }

            // Keep first occurrence, rename others
            for ($i = 1; $i < count($elements); ++$i) {
                $newId = $document->generateUniqueId($duplicateId);
                $elements[$i]->setAttribute('id', $newId);
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Automatically fixes common validation issues.
     *
     * @param Document            $document The document to fix
     * @param array<string, bool> $options  Fix options
     *
     * @return array{broken_references: int, duplicate_ids: int} Number of fixes applied
     */
    public static function autoFix(Document $document, array $options = []): array
    {
        $defaults = [
            'fix_broken_references' => true,
            'fix_duplicate_ids' => true,
        ];

        $options = array_merge($defaults, $options);
        $fixes = [
            'broken_references' => 0,
            'duplicate_ids' => 0,
        ];

        if ($options['fix_broken_references']) {
            $fixes['broken_references'] = self::fixBrokenReferences($document);
        }

        if ($options['fix_duplicate_ids']) {
            $fixes['duplicate_ids'] = self::fixDuplicateIds($document);
        }

        return $fixes;
    }

    /**
     * Suggest improvements for the document.
     *
     * @return array<array{suggestion: string, reason: string, impact: string}> Suggestions
     */
    public static function suggestImprovements(Document $document): array
    {
        $suggestions = [];

        $root = $document->getRootElement();
        if (null === $root) {
            return $suggestions;
        }

        // Check if document has viewBox
        if (null === $root->getAttribute('viewBox')) {
            $suggestions[] = [
                'suggestion' => 'Add a viewBox attribute to the root <svg> element',
                'reason' => 'Enables responsive scaling and better control over coordinate system',
                'impact' => 'high',
            ];
        }

        // Check for title and desc
        $hasTitle = false;
        $hasDesc = false;
        foreach ($root->getChildren() as $child) {
            if ('title' === $child->getTagName()) {
                $hasTitle = true;
            }
            if ('desc' === $child->getTagName()) {
                $hasDesc = true;
            }
        }

        if (!$hasTitle) {
            $suggestions[] = [
                'suggestion' => 'Add a <title> element for accessibility',
                'reason' => 'Improves accessibility for screen readers',
                'impact' => 'medium',
            ];
        }

        // Check for unused defs
        $unusedDefs = self::findUnusedDefs($document);
        if (!empty($unusedDefs)) {
            $suggestions[] = [
                'suggestion' => 'Remove '.count($unusedDefs).' unused definition(s)',
                'reason' => 'Reduces file size',
                'impact' => 'low',
            ];
        }

        // Check for inefficient colors
        $visitor = new CallbackVisitor(function (ElementInterface $element) use (&$suggestions): bool {
            $fill = $element->getAttribute('fill');
            $stroke = $element->getAttribute('stroke');

            // Check for #RRGGBB that could be shortened to #RGB
            foreach (['fill' => $fill, 'stroke' => $stroke] as $attr => $color) {
                if ($color && preg_match('/^#([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3$/i', $color)) {
                    $suggestions[] = [
                        'suggestion' => "Shorten color value in {$attr}: {$color}",
                        'reason' => 'Can be shortened to 3-character hex notation',
                        'impact' => 'low',
                    ];
                }
            }

            return true;
        });

        $traverser = new Traverser($visitor);
        $traverser->traverse($root);

        return $suggestions;
    }

    /**
     * Lint IDs in the document.
     *
     * @return array<array{type: string, message: string, severity: string}>
     */
    private static function lintIds(Document $document): array
    {
        $warnings = [];

        // Check for duplicate IDs
        $duplicates = self::findDuplicateIds($document);
        foreach ($duplicates as $id => $count) {
            $warnings[] = [
                'type' => 'duplicate_id',
                'message' => "ID '{$id}' is duplicated {$count} times",
                'severity' => 'error',
            ];
        }

        // Check for non-alphanumeric IDs
        $allIds = self::getAllIds($document);
        foreach ($allIds as $id) {
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $id)) {
                $warnings[] = [
                    'type' => 'invalid_id',
                    'message' => "ID '{$id}' should start with a letter and contain only letters, numbers, hyphens, and underscores",
                    'severity' => 'warning',
                ];
            }
        }

        return $warnings;
    }

    /**
     * Lint references in the document.
     *
     * @return array<array{type: string, message: string, severity: string}>
     */
    private static function lintReferences(Document $document): array
    {
        $warnings = [];

        $brokenRefs = self::findBrokenReferences($document);
        foreach ($brokenRefs as $ref) {
            $warnings[] = [
                'type' => 'broken_reference',
                'message' => "Reference to '#{$ref['id']}' in <{$ref['element']}> {$ref['attribute']} not found",
                'severity' => 'error',
            ];
        }

        return $warnings;
    }

    /**
     * Lint colors in the document.
     *
     * @return array<array{type: string, message: string, severity: string}>
     */
    private static function lintColors(Document $document): array
    {
        $warnings = [];

        $visitor = new CallbackVisitor(function (ElementInterface $element) use (&$warnings): bool {
            foreach (['fill', 'stroke'] as $attr) {
                $color = $element->getAttribute($attr);
                if (null === $color || str_starts_with($color, 'url(')) {
                    continue;
                }

                // Check for invalid hex colors
                if (str_starts_with($color, '#')) {
                    if (!preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color)) {
                        $warnings[] = [
                            'type' => 'invalid_color',
                            'message' => "Invalid hex color '{$color}' in {$attr}",
                            'severity' => 'warning',
                        ];
                    }
                }
            }

            return true;
        });

        $traverser = new Traverser($visitor);
        $root = $document->getRootElement();
        if (null !== $root) {
            $traverser->traverse($root);
        }

        return $warnings;
    }

    /**
     * Lint transforms in the document.
     *
     * @return array<array{type: string, message: string, severity: string}>
     */
    private static function lintTransforms(Document $document): array
    {
        $warnings = [];

        $visitor = new CallbackVisitor(function (ElementInterface $element) use (&$warnings): bool {
            $transform = $element->getAttribute('transform');
            if (null === $transform || '' === trim($transform)) {
                return true;
            }

            // Check for identity transforms
            if (preg_match('/translate\(0,?\s*0?\)/', $transform)
                || preg_match('/scale\(1,?\s*1?\)/', $transform)
                || preg_match('/rotate\(0/', $transform)) {
                $warnings[] = [
                    'type' => 'identity_transform',
                    'message' => 'Element has identity transform that can be removed',
                    'severity' => 'info',
                ];
            }

            return true;
        });

        $traverser = new Traverser($visitor);
        $root = $document->getRootElement();
        if (null !== $root) {
            $traverser->traverse($root);
        }

        return $warnings;
    }

    /**
     * Lint accessibility in the document.
     *
     * @return array<array{type: string, message: string, severity: string}>
     */
    private static function lintAccessibility(Document $document): array
    {
        $warnings = [];

        $root = $document->getRootElement();
        if (null === $root) {
            return $warnings;
        }

        // Check for missing title
        $hasTitle = false;
        foreach ($root->getChildren() as $child) {
            if ('title' === $child->getTagName()) {
                $hasTitle = true;
                break;
            }
        }

        if (!$hasTitle) {
            $warnings[] = [
                'type' => 'missing_title',
                'message' => 'Document is missing <title> element for accessibility',
                'severity' => 'warning',
            ];
        }

        return $warnings;
    }

    /**
     * Find duplicate IDs in the document.
     *
     * @return array<string, int> Map of ID to occurrence count
     */
    private static function findDuplicateIds(Document $document): array
    {
        $idCounts = [];

        $visitor = new CallbackVisitor(function (ElementInterface $element) use (&$idCounts): bool {
            $id = $element->getId();
            if (null !== $id) {
                $idCounts[$id] = ($idCounts[$id] ?? 0) + 1;
            }

            return true;
        });

        $traverser = new Traverser($visitor);
        $root = $document->getRootElement();
        if (null !== $root) {
            $traverser->traverse($root);
        }

        // Filter to only duplicates
        return array_filter($idCounts, fn ($count) => $count > 1);
    }

    /**
     * Get all IDs in the document.
     *
     * @return array<string>
     */
    private static function getAllIds(Document $document): array
    {
        $ids = [];

        $visitor = new CallbackVisitor(function (ElementInterface $element) use (&$ids): bool {
            $id = $element->getId();
            if (null !== $id) {
                $ids[] = $id;
            }

            return true;
        });

        $traverser = new Traverser($visitor);
        $root = $document->getRootElement();
        if (null !== $root) {
            $traverser->traverse($root);
        }

        return $ids;
    }

    /**
     * Find unused definitions in the document.
     *
     * @return array<string> Array of unused IDs
     */
    private static function findUnusedDefs(Document $document): array
    {
        $defIds = [];
        $usedIds = [];

        // Find all IDs in defs
        $visitor = new CallbackVisitor(function (ElementInterface $element) use (&$defIds): bool {
            if ('defs' === $element->getTagName()) {
                if ($element instanceof ContainerElementInterface) {
                    foreach ($element->getChildren() as $child) {
                        $id = $child->getId();
                        if (null !== $id) {
                            $defIds[] = $id;
                        }
                    }
                }
            }

            return true;
        });

        $traverser = new Traverser($visitor);
        $root = $document->getRootElement();
        if (null !== $root) {
            $traverser->traverse($root);
        }

        // Find all referenced IDs
        $refVisitor = new CallbackVisitor(function (ElementInterface $element) use (&$usedIds): bool {
            $refAttributes = ['href', 'xlink:href', 'fill', 'stroke', 'filter', 'clip-path', 'mask'];

            foreach ($refAttributes as $attr) {
                $value = $element->getAttribute($attr);
                if (null === $value) {
                    continue;
                }

                if (preg_match('/url\(#(.+?)\)/', $value, $matches)) {
                    $usedIds[] = $matches[1];
                } elseif (str_starts_with($value, '#')) {
                    $usedIds[] = substr($value, 1);
                }
            }

            return true;
        });

        if (null !== $root) {
            $refTraverser = new Traverser($refVisitor);
            $refTraverser->traverse($root);
        }

        return array_values(array_diff($defIds, $usedIds));
    }
}
