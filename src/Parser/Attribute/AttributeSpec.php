<?php

declare(strict_types=1);

namespace Atelier\Svg\Parser\Attribute;

/**
 * Describes the specification for an SVG attribute.
 *
 * This class holds metadata about an SVG attribute including its type,
 * whether it's required, allowed values, and other parsing information.
 */
final readonly class AttributeSpec
{
    /**
     * Creates a new attribute specification.
     *
     * @param string             $name              The attribute name
     * @param AttributeType      $type              The attribute type for parsing
     * @param bool               $required          Whether the attribute is required
     * @param mixed              $defaultValue      Default value if not specified
     * @param array<string>|null $enumValues        For ENUM types, the allowed values
     * @param bool               $deprecated        Whether the attribute is deprecated
     * @param string|null        $deprecatedMessage Deprecation message
     * @param string|null        $namespace         The namespace URI for namespaced attributes
     */
    public function __construct(
        private string $name,
        private AttributeType $type,
        private bool $required = false,
        private mixed $defaultValue = null,
        private ?array $enumValues = null,
        private bool $deprecated = false,
        private ?string $deprecatedMessage = null,
        private ?string $namespace = null,
    ) {
    }

    /**
     * Creates a string attribute specification.
     */
    public static function string(string $name, bool $required = false, ?string $default = null): self
    {
        return new self($name, AttributeType::STRING, $required, $default);
    }

    /**
     * Creates a length attribute specification.
     */
    public static function length(string $name, bool $required = false, mixed $default = null): self
    {
        return new self($name, AttributeType::LENGTH, $required, $default);
    }

    /**
     * Creates a number attribute specification.
     */
    public static function number(string $name, bool $required = false, ?float $default = null): self
    {
        return new self($name, AttributeType::NUMBER, $required, $default);
    }

    /**
     * Creates a color attribute specification.
     */
    public static function color(string $name, bool $required = false, ?string $default = null): self
    {
        return new self($name, AttributeType::COLOR, $required, $default);
    }

    /**
     * Creates a paint attribute specification (color, url, none).
     */
    public static function paint(string $name, bool $required = false, ?string $default = null): self
    {
        return new self($name, AttributeType::PAINT, $required, $default);
    }

    /**
     * Creates a transform attribute specification.
     */
    public static function transform(string $name): self
    {
        return new self($name, AttributeType::TRANSFORM);
    }

    /**
     * Creates an IRI reference attribute specification.
     */
    public static function iri(string $name): self
    {
        return new self($name, AttributeType::IRI);
    }

    /**
     * Creates an enum attribute specification.
     *
     * @param array<string> $values The allowed values
     */
    public static function enum(string $name, array $values, bool $required = false, ?string $default = null): self
    {
        return new self($name, AttributeType::ENUM, $required, $default, $values);
    }

    /**
     * Creates a deprecated attribute specification.
     */
    public static function deprecated(string $name, AttributeType $type, string $message): self
    {
        return new self($name, $type, deprecated: true, deprecatedMessage: $message);
    }

    /**
     * Creates a namespaced attribute specification.
     */
    public static function namespaced(string $name, string $namespace, AttributeType $type): self
    {
        return new self($name, $type, namespace: $namespace);
    }

    /**
     * Gets the attribute name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the attribute type.
     */
    public function getType(): AttributeType
    {
        return $this->type;
    }

    /**
     * Checks if the attribute is required.
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Gets the default value.
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Gets the allowed enum values (for ENUM type).
     *
     * @return array<string>|null
     */
    public function getEnumValues(): ?array
    {
        return $this->enumValues;
    }

    /**
     * Checks if the attribute is deprecated.
     */
    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * Gets the deprecation message.
     */
    public function getDeprecatedMessage(): ?string
    {
        return $this->deprecatedMessage;
    }

    /**
     * Gets the namespace URI (for namespaced attributes).
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Checks if this is a namespaced attribute.
     */
    public function isNamespaced(): bool
    {
        return null !== $this->namespace;
    }

    /**
     * Validates a value against this specification.
     *
     * @return bool True if the value is valid
     */
    public function validate(mixed $value): bool
    {
        if (null === $value || '' === $value) {
            return !$this->required;
        }

        if (AttributeType::ENUM === $this->type && null !== $this->enumValues) {
            if (!is_scalar($value) && !$value instanceof \Stringable) {
                return false;
            }

            return in_array((string) $value, $this->enumValues, true);
        }

        // Basic type validation can be expanded
        return true;
    }
}
