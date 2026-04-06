<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an SVG IRI reference (Internationalized Resource Identifier).
 *
 * SVG IRI references are used to reference elements within the same document
 * or external resources. They can appear in various forms:
 * - #elementId (fragment reference)
 * - url(#elementId) (URL function notation with fragment)
 * - url(path/to/file.svg#elementId) (URL with path and fragment)
 * - url(http://example.com/file.svg#elementId) (URL with absolute URI)
 *
 * @see https://www.w3.org/TR/SVG11/intro.html#TermIRI
 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Content_type#iri
 */
final readonly class IriReference implements \Stringable
{
    private const string URL_PATTERN = '/^url\(\s*([^)]+)\s*\)$/';

    /**
     * Private constructor, use static factory methods.
     */
    private function __construct(
        private string $iri,
        private ?string $fragment,
        private bool $hasUrlFunction,
    ) {
    }

    /**
     * Parses a string representation of an IRI reference.
     *
     * @param string|null $value The IRI reference string to parse
     *
     * @throws InvalidArgumentException if the value is invalid
     */
    public static function parse(?string $value): self
    {
        if (null === $value || '' === trim($value)) {
            throw new InvalidArgumentException('IRI reference cannot be empty');
        }

        $value = trim($value);
        $hasUrlFunction = false;

        // Check if the IRI is wrapped in url() function
        if (preg_match(self::URL_PATTERN, $value, $matches)) {
            $hasUrlFunction = true;
            $value = trim($matches[1]);
        }

        // Extract fragment if present
        $fragment = null;
        $fragmentPos = strpos($value, '#');

        if (false !== $fragmentPos) {
            $fragment = substr($value, $fragmentPos + 1);
            // If it's just a fragment identifier (#id), value should only contain the fragment part
            if (0 === $fragmentPos) {
                $value = '#'.$fragment;
            }
        }

        return new self($value, $fragment, $hasUrlFunction);
    }

    /**
     * Creates an IRI reference from a fragment identifier (element ID).
     *
     * @param string $elementId      The element ID without the # symbol
     * @param bool   $useUrlFunction Whether to wrap in url() notation
     */
    public static function fromElementId(string $elementId, bool $useUrlFunction = false): self
    {
        if ('' === trim($elementId)) {
            throw new InvalidArgumentException('Element ID cannot be empty');
        }

        $iri = '#'.$elementId;

        return new self($iri, $elementId, $useUrlFunction);
    }

    /**
     * Creates an IRI reference from a complete URI.
     *
     * @param string $uri            The URI (can include fragment)
     * @param bool   $useUrlFunction Whether to wrap in url() notation
     */
    public static function fromUri(string $uri, bool $useUrlFunction = true): self
    {
        if ('' === trim($uri)) {
            throw new InvalidArgumentException('URI cannot be empty');
        }

        $fragment = null;
        $fragmentPos = strpos($uri, '#');

        if (false !== $fragmentPos) {
            $fragment = substr($uri, $fragmentPos + 1);
        }

        return new self($uri, $fragment, $useUrlFunction);
    }

    /**
     * Returns the raw IRI without url() wrapper.
     */
    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * Returns the fragment part (element ID) if present, null otherwise.
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * Returns whether this IRI has a fragment component.
     */
    public function hasFragment(): bool
    {
        return null !== $this->fragment;
    }

    /**
     * Returns whether this IRI was wrapped in url() function.
     */
    public function hasUrlFunction(): bool
    {
        return $this->hasUrlFunction;
    }

    /**
     * Returns whether this IRI is a simple fragment reference (#id).
     */
    public function isFragmentOnly(): bool
    {
        return $this->hasFragment() && str_starts_with($this->iri, '#');
    }

    /**
     * Returns the IRI with or without url() wrapper based on original format.
     */
    public function toString(): string
    {
        if ($this->hasUrlFunction) {
            return 'url('.$this->iri.')';
        }

        return $this->iri;
    }

    /**
     * Returns the IRI with url() wrapper regardless of original format.
     */
    public function toUrlFunction(): string
    {
        return 'url('.$this->iri.')';
    }

    /**
     * Magic method for string conversion.
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
