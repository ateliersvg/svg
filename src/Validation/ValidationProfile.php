<?php

declare(strict_types=1);

namespace Atelier\Svg\Validation;

/**
 * Defines validation profiles with different levels of strictness.
 *
 * Profiles control which validation rules are applied and how strictly.
 * Use strict for spec compliance, lenient for real-world SVGs, and
 * accessible for WCAG compliance.
 *
 * Example usage:
 * ```php
 * $validator = new Validator(ValidationProfile::strict());
 * $result = $validator->validate($document);
 * ```
 */
final class ValidationProfile
{
    /**
     * @param array<string, mixed> $options Profile options
     */
    private function __construct(private array $options = [])
    {
    }

    /**
     * Creates a strict validation profile (SVG 1.1 specification compliance).
     *
     * This profile enforces:
     * - All required attributes must be present
     * - Attribute values must match spec formats
     * - Element nesting must follow spec rules
     * - No deprecated or non-standard attributes
     * - Strict ID format validation
     * - All references must be valid
     */
    public static function strict(): self
    {
        return new self([
            'check_required_attributes' => true,
            'check_attribute_types' => true,
            'check_attribute_values' => true,
            'check_element_nesting' => true,
            'check_references' => true,
            'check_duplicate_ids' => true,
            'check_id_format' => true,
            'check_circular_refs' => true,
            'allow_deprecated' => false,
            'allow_unknown_elements' => false,
            'allow_unknown_attributes' => false,
            'require_viewbox' => false,
            'require_title' => false,
            'require_desc' => false,
            'require_alt_text' => false,
            'severity_missing_required' => ValidationSeverity::ERROR,
            'severity_invalid_attribute' => ValidationSeverity::ERROR,
            'severity_invalid_nesting' => ValidationSeverity::ERROR,
            'severity_broken_reference' => ValidationSeverity::ERROR,
            'severity_duplicate_id' => ValidationSeverity::ERROR,
            'severity_circular_ref' => ValidationSeverity::ERROR,
        ]);
    }

    /**
     * Creates a lenient validation profile (real-world SVG tolerance).
     *
     * This profile is more forgiving:
     * - Allows missing optional attributes
     * - Accepts common real-world variations
     * - Allows deprecated but commonly used attributes
     * - Warns instead of errors for minor issues
     * - Flexible ID format
     */
    public static function lenient(): self
    {
        return new self([
            'check_required_attributes' => true,
            'check_attribute_types' => false,
            'check_attribute_values' => false,
            'check_element_nesting' => true,
            'check_references' => true,
            'check_duplicate_ids' => true,
            'check_id_format' => false,
            'check_circular_refs' => true,
            'allow_deprecated' => true,
            'allow_unknown_elements' => true,
            'allow_unknown_attributes' => true,
            'require_viewbox' => false,
            'require_title' => false,
            'require_desc' => false,
            'require_alt_text' => false,
            'severity_missing_required' => ValidationSeverity::WARNING,
            'severity_invalid_attribute' => ValidationSeverity::WARNING,
            'severity_invalid_nesting' => ValidationSeverity::WARNING,
            'severity_broken_reference' => ValidationSeverity::ERROR,
            'severity_duplicate_id' => ValidationSeverity::WARNING,
            'severity_circular_ref' => ValidationSeverity::WARNING,
        ]);
    }

    /**
     * Creates an accessible validation profile (WCAG compliance).
     *
     * This profile focuses on accessibility:
     * - Requires title and desc elements
     * - Requires alt text for images
     * - Checks for proper ARIA attributes
     * - Validates color contrast (if applicable)
     * - Ensures proper semantic structure
     */
    public static function accessible(): self
    {
        return new self([
            'check_required_attributes' => true,
            'check_attribute_types' => false,
            'check_attribute_values' => false,
            'check_element_nesting' => true,
            'check_references' => true,
            'check_duplicate_ids' => true,
            'check_id_format' => false,
            'check_circular_refs' => true,
            'allow_deprecated' => true,
            'allow_unknown_elements' => true,
            'allow_unknown_attributes' => true,
            'require_viewbox' => true,
            'require_title' => true,
            'require_desc' => false,
            'require_alt_text' => true,
            'check_accessibility' => true,
            'check_aria_attributes' => true,
            'check_image_alt_text' => true,
            'check_role_attribute' => true,
            'severity_missing_required' => ValidationSeverity::WARNING,
            'severity_invalid_attribute' => ValidationSeverity::WARNING,
            'severity_invalid_nesting' => ValidationSeverity::WARNING,
            'severity_broken_reference' => ValidationSeverity::ERROR,
            'severity_duplicate_id' => ValidationSeverity::WARNING,
            'severity_circular_ref' => ValidationSeverity::WARNING,
            'severity_accessibility' => ValidationSeverity::WARNING,
        ]);
    }

    /**
     * Creates a custom validation profile.
     *
     * @param array<string, mixed> $options Custom options
     */
    public static function custom(array $options): self
    {
        return new self($options);
    }

    /**
     * Gets the value of an option.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Checks if an option is enabled.
     */
    public function isEnabled(string $key): bool
    {
        return (bool) ($this->options[$key] ?? false);
    }

    /**
     * Gets all options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Creates a new profile with modified options.
     *
     * @param array<string, mixed> $overrides Options to override
     */
    public function with(array $overrides): self
    {
        return new self(array_merge($this->options, $overrides));
    }
}
