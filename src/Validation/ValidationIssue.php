<?php

declare(strict_types=1);

namespace Atelier\Svg\Validation;

use Atelier\Svg\Element\ElementInterface;

/**
 * Represents a single validation issue.
 */
final readonly class ValidationIssue
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public ValidationSeverity $severity,
        public string $code,
        public string $message,
        public ?ElementInterface $element = null,
        public ?string $attribute = null,
        public ?string $value = null,
        public array $metadata = [],
    ) {
    }

    /**
     * Creates an error issue.
     *
     * @param array<string, mixed> $metadata
     */
    public static function error(
        string $code,
        string $message,
        ?ElementInterface $element = null,
        ?string $attribute = null,
        ?string $value = null,
        array $metadata = [],
    ): self {
        return new self(
            ValidationSeverity::ERROR,
            $code,
            $message,
            $element,
            $attribute,
            $value,
            $metadata
        );
    }

    /**
     * Creates a warning issue.
     *
     * @param array<string, mixed> $metadata
     */
    public static function warning(
        string $code,
        string $message,
        ?ElementInterface $element = null,
        ?string $attribute = null,
        ?string $value = null,
        array $metadata = [],
    ): self {
        return new self(
            ValidationSeverity::WARNING,
            $code,
            $message,
            $element,
            $attribute,
            $value,
            $metadata
        );
    }

    /**
     * Creates an info issue.
     *
     * @param array<string, mixed> $metadata
     */
    public static function info(
        string $code,
        string $message,
        ?ElementInterface $element = null,
        ?string $attribute = null,
        ?string $value = null,
        array $metadata = [],
    ): self {
        return new self(
            ValidationSeverity::INFO,
            $code,
            $message,
            $element,
            $attribute,
            $value,
            $metadata
        );
    }

    /**
     * Gets the issue as an array for serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'severity' => $this->severity->value,
            'code' => $this->code,
            'message' => $this->message,
        ];

        if (null !== $this->element) {
            $data['element'] = [
                'tag' => $this->element->getTagName(),
                'id' => $this->element->getId(),
            ];
        }

        if (null !== $this->attribute) {
            $data['attribute'] = $this->attribute;
        }

        if (null !== $this->value) {
            $data['value'] = $this->value;
        }

        if (!empty($this->metadata)) {
            $data['metadata'] = $this->metadata;
        }

        return $data;
    }

    /**
     * Gets a formatted string representation of the issue.
     */
    public function format(): string
    {
        $prefix = match ($this->severity) {
            ValidationSeverity::ERROR => '[ERROR]',
            ValidationSeverity::WARNING => '[WARN]',
            ValidationSeverity::INFO => '[INFO]',
        };

        $parts = [$prefix, $this->message];

        if (null !== $this->element) {
            $tag = $this->element->getTagName();
            $id = $this->element->getId();
            $elementStr = $id ? "<{$tag} id=\"{$id}\">" : "<{$tag}>";
            $parts[] = "in {$elementStr}";
        }

        if (null !== $this->attribute) {
            $parts[] = "attribute '{$this->attribute}'";
        }

        return implode(' ', $parts);
    }
}
