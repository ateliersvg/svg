<?php

declare(strict_types=1);

namespace Atelier\Svg\Dumper;

use Atelier\Svg\Document;
use Atelier\Svg\Dumper\Formatter\XmlFormatterInterface;
use Atelier\Svg\Exception\RuntimeException;

abstract class XmlDumper implements DumperInterface
{
    private ?XmlFormatterInterface $formatter = null;

    private bool $includeXmlDeclaration = true;

    public function includeXmlDeclaration(bool $include): static
    {
        $this->includeXmlDeclaration = $include;

        return $this;
    }

    /**
     * @throws RuntimeException If the document has no root element
     */
    public function dump(Document $document): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $this->getFormatter()->configure($dom);

        $root = $this->buildDom($document, $dom);
        $dom->appendChild($root);

        $xml = $this->getFormatter()->serialize($dom);

        return $this->includeXmlDeclaration ? $xml : $this->stripXmlDeclaration($xml);
    }

    /**
     * @throws RuntimeException If the document has no root element or the file cannot be written
     */
    public function dumpToFile(Document $document, string $path): void
    {
        $xml = $this->dump($document);
        if (false === @file_put_contents($path, $xml)) {
            throw new RuntimeException("Failed to write to: $path");
        }
    }

    abstract protected function createFormatter(): XmlFormatterInterface;

    protected function getFormatter(): XmlFormatterInterface
    {
        return $this->formatter ??= $this->createFormatter();
    }

    protected function buildDom(Document $document, \DOMDocument $dom): \DOMElement
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            throw new RuntimeException('Document has no root element');
        }

        return $this->buildElement($rootElement, $dom);
    }

    /**
     * Recursively build a DOM element from an SVG element.
     */
    private function buildElement(\Atelier\Svg\Element\ElementInterface $element, \DOMDocument $dom): \DOMElement
    {
        $tagName = $element->getTagName();

        // Create the DOM element with proper namespace
        if ('svg' === $tagName) {
            $domElement = $dom->createElementNS('http://www.w3.org/2000/svg', $tagName);
        } else {
            $domElement = $dom->createElement($tagName);
        }

        // Set all attributes
        $textContent = null;
        foreach ($element->getAttributes() as $name => $value) {
            // Handle textContent specially - don't set as attribute, save for text node
            if ('textContent' === $name) {
                $textContent = (string) $value;
                continue;
            }

            // Handle xlink:href specially
            if ('href' === $name && str_starts_with((string) $value, '#')) {
                // This might be an xlink:href, set both for compatibility
                $domElement->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', (string) $value);
            }

            $domElement->setAttribute($name, (string) $value);
        }

        // Recursively add children if this is a container element
        if ($element instanceof \Atelier\Svg\Element\ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $childDomElement = $this->buildElement($child, $dom);
                $domElement->appendChild($childDomElement);
            }
        }

        // Add text content as a text node if present
        if (null !== $textContent && '' !== $textContent) {
            $textNode = $dom->createTextNode($textContent);
            $domElement->appendChild($textNode);
        }

        return $domElement;
    }

    private function stripXmlDeclaration(string $xml): string
    {
        return ltrim((string) preg_replace('/^<\?xml[^>]+>\s*/', '', $xml));
    }
}
