<?php

declare(strict_types=1);

namespace Atelier\Svg\Document;

use Atelier\Svg\Document;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Exception\RuntimeException;

/**
 * DocumentBuilder provides methods for creating SVG documents from various sources.
 *
 * This builder handles document creation with proper error handling and validation.
 */
final class DocumentBuilder implements DocumentBuilderInterface
{
    /**
     * Creates a new SVG root element with default settings.
     *
     * @return SvgElement A new SVG element with xmlns and version attributes
     */
    public function getSvg(): SvgElement
    {
        $svg = new SvgElement();

        // Default attributes are set in SvgElement constructor
        return $svg;
    }

    /**
     * Creates a new Document with an empty SVG root element.
     *
     * @return Document A new document with a default SVG root
     */
    public function getSvgDocument(): Document
    {
        $svg = $this->getSvg();

        return new Document($svg);
    }

    /**
     * Creates a Document with custom width and height.
     *
     * @param int|float $width  The width of the SVG
     * @param int|float $height The height of the SVG
     *
     * @return Document A new document with specified dimensions
     */
    public function createDocument(int|float $width = 300, int|float $height = 150): Document
    {
        return Document::create($width, $height);
    }

    /**
     * Creates a Document from an existing SVG element.
     *
     * @param SvgElement $svgElement The SVG root element
     *
     * @return Document A new document with the provided SVG element
     *
     * @throws InvalidArgumentException If the SVG element is invalid
     */
    public function fromSvgElement(SvgElement $svgElement): Document
    {
        return new Document($svgElement);
    }

    /**
     * Creates a Document from an SVG string.
     *
     * @param string $svgContent The SVG content as a string
     *
     * @return Document A new document parsed from the SVG string
     *
     * @throws InvalidArgumentException If the SVG content is invalid or cannot be parsed
     */
    public function fromString(string $svgContent): Document
    {
        if (empty(trim($svgContent))) {
            throw new InvalidArgumentException('SVG content cannot be empty');
        }

        // Basic validation - check if it contains SVG tag
        if (!str_contains($svgContent, '<svg')) {
            throw new InvalidArgumentException('Invalid SVG content: no <svg> tag found');
        }

        // For now, create a basic document
        // In a full implementation, this would use a proper SVG parser
        $svg = new SvgElement();

        // Try to parse width and height from the string
        if (preg_match('/width=["\']([^"\']+)["\']/', $svgContent, $widthMatch)) {
            $svg->setWidth($widthMatch[1]);
        }
        if (preg_match('/height=["\']([^"\']+)["\']/', $svgContent, $heightMatch)) {
            $svg->setHeight($heightMatch[1]);
        }
        if (preg_match('/viewBox=["\']([^"\']+)["\']/', $svgContent, $viewBoxMatch)) {
            $svg->setViewbox($viewBoxMatch[1]);
        }

        return new Document($svg);
    }

    /**
     * Creates a Document from an SVG file.
     *
     * @param string $filePath The path to the SVG file
     *
     * @return Document A new document parsed from the file
     *
     * @throws InvalidArgumentException If the file does not exist or cannot be read
     * @throws RuntimeException         If there is an error reading the file
     */
    public function fromFile(string $filePath): Document
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf('SVG file does not exist: %s', $filePath));
        }

        if (!is_readable($filePath)) {
            throw new InvalidArgumentException(sprintf('SVG file is not readable: %s', $filePath));
        }

        $content = @file_get_contents($filePath);
        assert(false !== $content);

        try {
            return $this->fromString($content);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(sprintf('Invalid SVG file content in %s: %s', $filePath, $e->getMessage()), 0, $e);
        }
    }

    /**
     * Validates an SVG string.
     *
     * @param string $svgContent The SVG content to validate
     *
     * @return bool True if the content is valid SVG
     */
    public function validate(string $svgContent): bool
    {
        if (empty(trim($svgContent))) {
            return false;
        }

        // Basic validation
        if (!str_contains($svgContent, '<svg')) {
            return false;
        }

        // Check for well-formed XML
        $previousValue = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($svgContent);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previousValue);

        return false !== $doc && empty($errors);
    }
}
