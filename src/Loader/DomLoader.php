<?php

declare(strict_types=1);

namespace Atelier\Svg\Loader;

use Atelier\Svg\Document;
use Atelier\Svg\Exception\RuntimeException;

/**
 * Loads SVG documents from strings or files using PHP's DOM extension.
 *
 * This loader uses the built-in DOMDocument class to parse SVG XML data
 * and converts it into the library's Document structure. It provides a
 * robust way to load SVG content with proper XML parsing and validation.
 *
 * The loader suppresses XML parsing errors and warnings by default to
 * handle common SVG quirks gracefully. For stricter validation, consider
 * using a specialized parser.
 *
 * Example:
 * ```php
 * $loader = new DomLoader();
 *
 * // Load from string
 * $svg = '<svg width="100" height="100"><circle cx="50" cy="50" r="40"/></svg>';
 * $document = $loader->loadFromString($svg);
 *
 * // Load from file
 * $document = $loader->loadFromFile('path/to/image.svg');
 * ```
 */
final class DomLoader implements LoaderInterface
{
    /**
     * Loads an SVG document from a string.
     *
     * Parses the provided SVG XML string and converts it into a Document
     * object representing the SVG structure. The string must contain valid
     * XML markup.
     *
     * @param string $svg The SVG content as an XML string
     *
     * @return Document The loaded SVG document
     *
     * @throws RuntimeException If the SVG content cannot be parsed or converted
     */
    public function loadFromString(string $svg): Document
    {
        $dom = new \DOMDocument();
        $dom->loadXML($svg, LIBXML_NOERROR | LIBXML_NOWARNING);

        return $this->buildDocumentFromDom($dom);
    }

    /**
     * Loads an SVG document from a file.
     *
     * Reads the file at the specified path and parses its content as SVG.
     * The file must exist, be readable, and contain valid SVG XML.
     *
     * @param string $path The file system path to the SVG file
     *
     * @return Document The loaded SVG document
     *
     * @throws RuntimeException If the file cannot be read or parsed
     */
    public function loadFromFile(string $path): Document
    {
        $contents = @file_get_contents($path);
        if (false === $contents) {
            throw new RuntimeException("Could not read file: $path");
        }

        return $this->loadFromString($contents);
    }

    /**
     * Converts a DOMDocument into the library's Document structure.
     *
     * This internal method bridges PHP's DOMDocument representation and
     * the library's native Document/Element structure by delegating to
     * the DomParser class.
     *
     * @param \DOMDocument $dom The DOM document to convert
     *
     * @return Document The converted document
     *
     * @throws RuntimeException If the DOM cannot be serialized or parsed
     */
    private function buildDocumentFromDom(\DOMDocument $dom): Document
    {
        // Use DomParser to convert the DOM to our Document structure
        $parser = new \Atelier\Svg\Parser\DomParser();

        // Get the XML string from the DOM and parse it
        $xmlString = $dom->saveXML();

        if (false === $xmlString) {
            throw new RuntimeException('Failed to serialize DOM to XML');
        }

        return $parser->parse($xmlString);
    }
}
