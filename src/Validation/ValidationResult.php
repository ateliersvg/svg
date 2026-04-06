<?php

declare(strict_types=1);

namespace Atelier\Svg\Validation;

/**
 * Represents the result of a validation operation.
 */
final class ValidationResult
{
    /** @var array<ValidationIssue> */
    private array $issues = [];

    /**
     * @param array<ValidationIssue> $issues
     */
    public function __construct(array $issues = [])
    {
        foreach ($issues as $issue) {
            $this->addIssue($issue);
        }
    }

    /**
     * Adds a validation issue.
     */
    public function addIssue(ValidationIssue $issue): self
    {
        $this->issues[] = $issue;

        return $this;
    }

    /**
     * Gets all validation issues.
     *
     * @return array<ValidationIssue>
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /**
     * Gets issues filtered by severity.
     *
     * @return array<ValidationIssue>
     */
    public function getIssuesBySeverity(ValidationSeverity $severity): array
    {
        return array_filter(
            $this->issues,
            fn (ValidationIssue $issue): bool => $issue->severity === $severity
        );
    }

    /**
     * Gets all error issues.
     *
     * @return array<ValidationIssue>
     */
    public function getErrors(): array
    {
        return $this->getIssuesBySeverity(ValidationSeverity::ERROR);
    }

    /**
     * Gets all warning issues.
     *
     * @return array<ValidationIssue>
     */
    public function getWarnings(): array
    {
        return $this->getIssuesBySeverity(ValidationSeverity::WARNING);
    }

    /**
     * Gets all info issues.
     *
     * @return array<ValidationIssue>
     */
    public function getInfo(): array
    {
        return $this->getIssuesBySeverity(ValidationSeverity::INFO);
    }

    /**
     * Checks if the validation passed (no errors).
     */
    public function isValid(): bool
    {
        return empty($this->getErrors());
    }

    /**
     * Checks if there are any issues of any severity.
     */
    public function hasIssues(): bool
    {
        return !empty($this->issues);
    }

    /**
     * Gets the total count of issues.
     */
    public function count(): int
    {
        return count($this->issues);
    }

    /**
     * Gets counts by severity.
     *
     * @return array{errors: int, warnings: int, info: int}
     */
    public function getCounts(): array
    {
        return [
            'errors' => count($this->getErrors()),
            'warnings' => count($this->getWarnings()),
            'info' => count($this->getInfo()),
        ];
    }

    /**
     * Merges another validation result into this one.
     */
    public function merge(ValidationResult $other): self
    {
        foreach ($other->getIssues() as $issue) {
            $this->addIssue($issue);
        }

        return $this;
    }

    /**
     * Converts the result to an array for serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->isValid(),
            'counts' => $this->getCounts(),
            'issues' => array_map(
                fn (ValidationIssue $issue): array => $issue->toArray(),
                $this->issues
            ),
        ];
    }

    /**
     * Formats the result as a human-readable string.
     */
    public function format(): string
    {
        if (empty($this->issues)) {
            return "✓ Validation passed - no issues found\n";
        }

        $counts = $this->getCounts();
        $lines = [];

        $lines[] = sprintf(
            'Validation completed: %d error(s), %d warning(s), %d info',
            $counts['errors'],
            $counts['warnings'],
            $counts['info']
        );
        $lines[] = str_repeat('-', 60);

        foreach ($this->issues as $issue) {
            $lines[] = $issue->format();
        }

        return implode("\n", $lines)."\n";
    }
}
