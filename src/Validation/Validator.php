<?php

declare(strict_types=1);

namespace Atelier\Svg\Validation;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Comprehensive SVG document validator.
 *
 * Validates SVG documents according to configurable profiles and rules.
 * Checks references, attributes, element nesting, accessibility, and more.
 *
 * Example usage:
 * ```php
 * $validator = new Validator(ValidationProfile::strict());
 * $result = $validator->validate($document);
 *
 * if ($result->isValid()) {
 *     echo "Document is valid!";
 * } else {
 *     echo $result->format();
 * }
 * ```
 */
final class Validator
{
    private readonly ValidationProfile $profile;
    private ?ReferenceTracker $tracker = null;

    /**
     * SVG 1.1 element nesting rules.
     * Maps parent elements to allowed child elements (* means any).
     */
    private const array NESTING_RULES = [
        'svg' => ['*'],
        'g' => ['*'],
        'defs' => ['*'],
        'symbol' => ['*'],
        'marker' => ['*'],
        'pattern' => ['*'],
        'mask' => ['*'],
        'clipPath' => ['rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'path', 'text', 'use'],
        'a' => ['*'],
        'switch' => ['*'],
        'text' => ['tspan', 'tref', 'textPath', 'a'],
        'tspan' => ['tspan', 'tref', 'a'],
        'textPath' => ['tspan', 'tref', 'a'],
        'linearGradient' => ['stop', 'animate', 'animateTransform', 'set'],
        'radialGradient' => ['stop', 'animate', 'animateTransform', 'set'],
        'filter' => [
            'feBlend', 'feColorMatrix', 'feComponentTransfer', 'feComposite',
            'feConvolveMatrix', 'feDiffuseLighting', 'feDisplacementMap', 'feFlood',
            'feGaussianBlur', 'feImage', 'feMerge', 'feMorphology', 'feOffset',
            'feSpecularLighting', 'feTile', 'feTurbulence', 'animate', 'set',
        ],
    ];

    /**
     * Required attributes for specific elements.
     */
    private const array REQUIRED_ATTRIBUTES = [
        'circle' => ['cx', 'cy', 'r'],
        'ellipse' => ['cx', 'cy', 'rx', 'ry'],
        'line' => ['x1', 'y1', 'x2', 'y2'],
        'rect' => ['width', 'height'],
        'image' => ['width', 'height'],
        'use' => ['href', 'xlink:href'], // At least one required
        'linearGradient' => [],
        'radialGradient' => [],
        'stop' => ['offset'],
    ];

    public function __construct(?ValidationProfile $profile = null)
    {
        $this->profile = $profile ?? ValidationProfile::lenient();
    }

    /**
     * Validates an SVG document.
     */
    public function validate(Document $document): ValidationResult
    {
        $result = new ValidationResult();

        // Initialize reference tracker
        $this->tracker = new ReferenceTracker($document);

        // Check basic document structure
        $root = $document->getRootElement();
        if (null === $root) {
            $result->addIssue(ValidationIssue::error(
                'no_root',
                'Document has no root element'
            ));

            return $result;
        }

        // Validate references
        if ($this->profile->isEnabled('check_references')) {
            $this->validateReferences($result);
        }

        // Check duplicate IDs
        if ($this->profile->isEnabled('check_duplicate_ids')) {
            $this->validateDuplicateIds($result);
        }

        // Check circular references
        if ($this->profile->isEnabled('check_circular_refs')) {
            $this->validateCircularReferences($result);
        }

        // Validate document structure
        $this->validateElement($root, $result, null);

        // Check accessibility
        if ($this->profile->isEnabled('check_accessibility')) {
            $this->validateAccessibility($document, $result);
        }

        return $result;
    }

    /**
     * Validates a single element recursively.
     */
    private function validateElement(
        ElementInterface $element,
        ValidationResult $result,
        ?ElementInterface $parent,
    ): void {
        $tagName = $element->getTagName();

        // Validate required attributes
        if ($this->profile->isEnabled('check_required_attributes')) {
            $this->validateRequiredAttributes($element, $result);
        }

        // Validate attribute values
        if ($this->profile->isEnabled('check_attribute_values')) {
            $this->validateAttributeValues($element, $result);
        }

        // Validate element nesting
        if ($this->profile->isEnabled('check_element_nesting') && null !== $parent) {
            $this->validateNesting($element, $parent, $result);
        }

        // Validate ID format
        if ($this->profile->isEnabled('check_id_format')) {
            $this->validateIdFormat($element, $result);
        }

        // Recursively validate children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->validateElement($child, $result, $element);
            }
        }
    }

    /**
     * Validates references in the document.
     */
    private function validateReferences(ValidationResult $result): void
    {
        assert(null !== $this->tracker);

        $brokenRefs = $this->tracker->findBrokenReferences();
        /** @var ValidationSeverity $severity */
        $severity = $this->profile->get('severity_broken_reference', ValidationSeverity::ERROR);

        foreach ($brokenRefs as $ref) {
            $result->addIssue(new ValidationIssue(
                $severity,
                'broken_reference',
                $ref->getDescription(),
                $ref->referencingElement,
                $ref->attribute,
                $ref->value,
                ['referenced_id' => $ref->referencedId]
            ));
        }
    }

    /**
     * Validates for duplicate IDs.
     */
    private function validateDuplicateIds(ValidationResult $result): void
    {
        assert(null !== $this->tracker);

        $duplicates = $this->tracker->getDuplicateIds();
        /** @var ValidationSeverity $severity */
        $severity = $this->profile->get('severity_duplicate_id', ValidationSeverity::ERROR);

        foreach ($duplicates as $id => $count) {
            $result->addIssue(new ValidationIssue(
                $severity,
                'duplicate_id',
                "ID '{$id}' appears {$count} times in the document",
                null,
                'id',
                $id,
                ['count' => $count]
            ));
        }
    }

    /**
     * Validates for circular references.
     */
    private function validateCircularReferences(ValidationResult $result): void
    {
        assert(null !== $this->tracker);

        $cycles = $this->tracker->findCircularReferences();
        /** @var ValidationSeverity $severity */
        $severity = $this->profile->get('severity_circular_ref', ValidationSeverity::ERROR);

        foreach ($cycles as $cycle) {
            $cycleStr = implode(' → ', $cycle);
            $result->addIssue(new ValidationIssue(
                $severity,
                'circular_reference',
                "Circular reference detected: {$cycleStr}",
                null,
                null,
                null,
                ['cycle' => $cycle]
            ));
        }
    }

    /**
     * Validates required attributes for an element.
     */
    private function validateRequiredAttributes(
        ElementInterface $element,
        ValidationResult $result,
    ): void {
        $tagName = $element->getTagName();

        if (!isset(self::REQUIRED_ATTRIBUTES[$tagName])) {
            return;
        }

        $required = self::REQUIRED_ATTRIBUTES[$tagName];
        /** @var ValidationSeverity $severity */
        $severity = $this->profile->get('severity_missing_required', ValidationSeverity::ERROR);

        // Handle special case where at least one of multiple attributes is required
        if ('use' === $tagName) {
            $hasHref = $element->hasAttribute('href');
            $hasXlinkHref = $element->hasAttribute('xlink:href');

            if (!$hasHref && !$hasXlinkHref) {
                $result->addIssue(new ValidationIssue(
                    $severity,
                    'missing_required_attribute',
                    "Element <{$tagName}> requires either 'href' or 'xlink:href' attribute",
                    $element
                ));
            }

            return;
        }

        // Standard required attributes check
        foreach ($required as $attr) {
            if (!$element->hasAttribute($attr)) {
                $result->addIssue(new ValidationIssue(
                    $severity,
                    'missing_required_attribute',
                    "Element <{$tagName}> is missing required attribute '{$attr}'",
                    $element,
                    $attr
                ));
            }
        }
    }

    /**
     * Validates attribute values.
     */
    private function validateAttributeValues(
        ElementInterface $element,
        ValidationResult $result,
    ): void {
        /** @var ValidationSeverity $severity */
        $severity = $this->profile->get('severity_invalid_attribute', ValidationSeverity::WARNING);

        // Validate numeric attributes
        foreach (['x', 'y', 'width', 'height', 'cx', 'cy', 'r', 'rx', 'ry'] as $attr) {
            $value = $element->getAttribute($attr);
            if (null !== $value && !is_numeric($value) && !$this->isLengthValue($value)) {
                $result->addIssue(new ValidationIssue(
                    $severity,
                    'invalid_attribute_value',
                    "Attribute '{$attr}' should be a numeric value, got '{$value}'",
                    $element,
                    $attr,
                    $value
                ));
            }
        }

        // Validate color attributes
        foreach (['fill', 'stroke'] as $attr) {
            $value = $element->getAttribute($attr);
            if (null !== $value && !$this->isValidColorValue($value)) {
                $result->addIssue(new ValidationIssue(
                    $severity,
                    'invalid_color',
                    "Attribute '{$attr}' has invalid color value '{$value}'",
                    $element,
                    $attr,
                    $value
                ));
            }
        }
    }

    /**
     * Validates element nesting rules.
     */
    private function validateNesting(
        ElementInterface $element,
        ElementInterface $parent,
        ValidationResult $result,
    ): void {
        $childTag = $element->getTagName();
        $parentTag = $parent->getTagName();

        if (!isset(self::NESTING_RULES[$parentTag])) {
            return; // No rules defined for this parent
        }

        $allowed = self::NESTING_RULES[$parentTag];

        if (!in_array('*', $allowed, true) && !in_array($childTag, $allowed, true)) {
            /** @var ValidationSeverity $severity */
            $severity = $this->profile->get('severity_invalid_nesting', ValidationSeverity::WARNING);
            $result->addIssue(new ValidationIssue(
                $severity,
                'invalid_nesting',
                "Element <{$childTag}> is not allowed as child of <{$parentTag}>",
                $element
            ));
        }
    }

    /**
     * Validates ID format.
     */
    private function validateIdFormat(ElementInterface $element, ValidationResult $result): void
    {
        $id = $element->getId();
        if (null === $id) {
            return;
        }

        // SVG IDs should start with a letter and contain only letters, numbers, hyphens, and underscores
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $id)) {
            $result->addIssue(ValidationIssue::warning(
                'invalid_id_format',
                "ID '{$id}' should start with a letter and contain only letters, numbers, hyphens, and underscores",
                $element,
                'id',
                $id
            ));
        }
    }

    /**
     * Validates accessibility.
     */
    private function validateAccessibility(Document $document, ValidationResult $result): void
    {
        $root = $document->getRootElement();
        assert(null !== $root);

        /** @var ValidationSeverity $severity */
        $severity = $this->profile->get('severity_accessibility', ValidationSeverity::WARNING);

        // Check for viewBox
        if ($this->profile->isEnabled('require_viewbox') && !$root->hasAttribute('viewBox')) {
            $result->addIssue(new ValidationIssue(
                $severity,
                'missing_viewbox',
                'Root <svg> element should have a viewBox attribute for responsive scaling',
                $root,
                'viewBox'
            ));
        }

        // Check for title
        if ($this->profile->isEnabled('require_title')) {
            $hasTitle = false;
            foreach ($root->getChildren() as $child) {
                if ('title' === $child->getTagName()) {
                    $hasTitle = true;
                    break;
                }
            }

            if (!$hasTitle) {
                $result->addIssue(new ValidationIssue(
                    $severity,
                    'missing_title',
                    'Document should have a <title> element for accessibility',
                    $root
                ));
            }
        }

        // Check for desc
        if ($this->profile->isEnabled('require_desc')) {
            $hasDesc = false;
            foreach ($root->getChildren() as $child) {
                if ('desc' === $child->getTagName()) {
                    $hasDesc = true;
                    break;
                }
            }

            if (!$hasDesc) {
                $result->addIssue(new ValidationIssue(
                    ValidationSeverity::INFO,
                    'missing_desc',
                    'Document could benefit from a <desc> element for accessibility',
                    $root
                ));
            }
        }

        // Check images for alt text
        if ($this->profile->isEnabled('check_image_alt_text')) {
            $this->validateImageAltText($root, $result, $severity);
        }
    }

    /**
     * Validates that images have alt text (title or aria-label).
     */
    private function validateImageAltText(
        ElementInterface $element,
        ValidationResult $result,
        ValidationSeverity $severity,
    ): void {
        if ('image' === $element->getTagName()) {
            $hasAlt = $element->hasAttribute('aria-label')
                      || $element->hasAttribute('title')
                      || $this->hasChildTitle($element);

            if (!$hasAlt) {
                $result->addIssue(new ValidationIssue(
                    $severity,
                    'missing_image_alt',
                    'Image element should have alt text (aria-label, title attribute, or <title> child)',
                    $element
                ));
            }
        }

        // Recursively check children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->validateImageAltText($child, $result, $severity);
            }
        }
    }

    /**
     * Checks if an element has a title child.
     */
    private function hasChildTitle(ElementInterface $element): bool
    {
        if (!$element instanceof ContainerElementInterface) {
            return false;
        }

        foreach ($element->getChildren() as $child) {
            if ('title' === $child->getTagName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a value is a valid SVG length value.
     */
    private function isLengthValue(string $value): bool
    {
        // Match: number, number%, number + unit (px, em, rem, etc.)
        return 1 === preg_match('/^-?\d+(\.\d+)?(px|em|rem|pt|pc|cm|mm|in|%)?$/', $value);
    }

    /**
     * Checks if a value is a valid color value.
     */
    private function isValidColorValue(string $value): bool
    {
        // Allow url() references
        if (str_starts_with($value, 'url(')) {
            return true;
        }

        // Allow named colors and keywords
        if (in_array($value, ['none', 'currentColor', 'inherit', 'transparent'], true)) {
            return true;
        }

        // Allow hex colors (#RGB or #RRGGBB)
        if (preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $value)) {
            return true;
        }

        // Allow rgb() and rgba()
        if (preg_match('/^rgba?\([^)]+\)$/', $value)) {
            return true;
        }

        // Allow hsl() and hsla()
        if (preg_match('/^hsla?\([^)]+\)$/', $value)) {
            return true;
        }

        // Check common named colors (simplified check)
        $namedColors = ['black', 'white', 'red', 'green', 'blue', 'yellow', 'gray', 'grey'];

        return in_array(strtolower($value), $namedColors, true);
    }

    /**
     * Gets the validation profile.
     */
    public function getProfile(): ValidationProfile
    {
        return $this->profile;
    }

    /**
     * Gets the reference tracker (if validation has been run).
     */
    public function getTracker(): ?ReferenceTracker
    {
        return $this->tracker;
    }
}
