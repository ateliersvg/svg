<?php

declare(strict_types=1);

namespace Atelier\Svg\Parser;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Animation\AnimateElement;
use Atelier\Svg\Element\Animation\AnimateTransformElement;
use Atelier\Svg\Element\Clipping\ClipPathElement;
use Atelier\Svg\Element\Clipping\MaskElement;
use Atelier\Svg\Element\Descriptive\DescElement;
use Atelier\Svg\Element\Descriptive\MetadataElement;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Filter\FeBlendElement;
use Atelier\Svg\Element\Filter\FeColorMatrixElement;
use Atelier\Svg\Element\Filter\FeComponentTransferElement;
use Atelier\Svg\Element\Filter\FeCompositeElement;
use Atelier\Svg\Element\Filter\FeConvolveMatrixElement;
use Atelier\Svg\Element\Filter\FeDiffuseLightingElement;
use Atelier\Svg\Element\Filter\FeDisplacementMapElement;
use Atelier\Svg\Element\Filter\FeDistantLightElement;
use Atelier\Svg\Element\Filter\FeFloodElement;
use Atelier\Svg\Element\Filter\FeFuncAElement;
use Atelier\Svg\Element\Filter\FeFuncBElement;
use Atelier\Svg\Element\Filter\FeFuncGElement;
use Atelier\Svg\Element\Filter\FeFuncRElement;
use Atelier\Svg\Element\Filter\FeGaussianBlurElement;
use Atelier\Svg\Element\Filter\FeImageElement;
use Atelier\Svg\Element\Filter\FeMergeElement;
use Atelier\Svg\Element\Filter\FeMergeNodeElement;
use Atelier\Svg\Element\Filter\FeMorphologyElement;
use Atelier\Svg\Element\Filter\FeOffsetElement;
use Atelier\Svg\Element\Filter\FePointLightElement;
use Atelier\Svg\Element\Filter\FeSpecularLightingElement;
use Atelier\Svg\Element\Filter\FeSpotLightElement;
use Atelier\Svg\Element\Filter\FeTileElement;
use Atelier\Svg\Element\Filter\FeTurbulenceElement;
use Atelier\Svg\Element\Filter\FilterElement;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\PatternElement;
use Atelier\Svg\Element\Gradient\RadialGradientElement;
use Atelier\Svg\Element\Gradient\StopElement;
use Atelier\Svg\Element\Hyperlinking\AnchorElement;
use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\ScriptElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\ForeignObjectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Structural\MarkerElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Element\Structural\UseElement;
use Atelier\Svg\Element\Structural\ViewElement;
use Atelier\Svg\Element\StyleElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TextPathElement;
use Atelier\Svg\Element\Text\TspanElement;
use Atelier\Svg\Exception\ParseException;

final class DomParser implements ParserInterface
{
    /**
     * SVG namespace constant.
     */
    private const string SVG_NAMESPACE = 'http://www.w3.org/2000/svg';

    /**
     * XLink namespace constant.
     */
    private const string XLINK_NAMESPACE = 'http://www.w3.org/1999/xlink';

    /**
     * Maximum allowed input size in bytes (10 MB by default).
     * This helps prevent memory exhaustion attacks from extremely large SVG files.
     */
    private const DEFAULT_MAX_INPUT_SIZE = 10 * 1024 * 1024;

    /**
     * Safe libxml options that prevent XXE and entity expansion attacks.
     *
     * LIBXML_NONET: Disable network access during parsing
     * LIBXML_NOBLANKS: Remove blank nodes for cleaner parsing
     *
     * Note: We explicitly DO NOT use LIBXML_NOENT as it causes entity expansion
     * which can lead to "Billion Laughs" attacks (exponential entity expansion).
     */
    private const SAFE_LIBXML_OPTIONS = LIBXML_NONET | LIBXML_NOBLANKS;

    /**
     * Element factory map for O(1) element type lookups.
     *
     * @var array<string, class-string>
     */
    private const array ELEMENT_MAP = [
        'svg' => SvgElement::class,
        'a' => AnchorElement::class,
        'g' => GroupElement::class,
        'title' => TitleElement::class,
        'desc' => DescElement::class,
        'metadata' => MetadataElement::class,
        'style' => StyleElement::class,
        'script' => ScriptElement::class,
        'image' => ImageElement::class,
        'foreignobject' => ForeignObjectElement::class,
        'view' => ViewElement::class,
        'rect' => RectElement::class,
        'circle' => CircleElement::class,
        'ellipse' => EllipseElement::class,
        'line' => LineElement::class,
        'polyline' => PolylineElement::class,
        'polygon' => PolygonElement::class,
        'path' => PathElement::class,
        'text' => TextElement::class,
        'use' => UseElement::class,
        'defs' => DefsElement::class,
        'filter' => FilterElement::class,
        'fegaussianblur' => FeGaussianBlurElement::class,
        'feoffset' => FeOffsetElement::class,
        'fecolormatrix' => FeColorMatrixElement::class,
        'feblend' => FeBlendElement::class,
        'fecomposite' => FeCompositeElement::class,
        'femerge' => FeMergeElement::class,
        'femergenode' => FeMergeNodeElement::class,
        'feflood' => FeFloodElement::class,
        'feconvolvematrix' => FeConvolveMatrixElement::class,
        'fecomponenttransfer' => FeComponentTransferElement::class,
        'fediffuselighting' => FeDiffuseLightingElement::class,
        'fedisplacementmap' => FeDisplacementMapElement::class,
        'feimage' => FeImageElement::class,
        'femorphology' => FeMorphologyElement::class,
        'fespecularlighting' => FeSpecularLightingElement::class,
        'fetile' => FeTileElement::class,
        'feturbulence' => FeTurbulenceElement::class,
        'fefunca' => FeFuncAElement::class,
        'fefuncb' => FeFuncBElement::class,
        'fefuncg' => FeFuncGElement::class,
        'fefuncr' => FeFuncRElement::class,
        'fepointlight' => FePointLightElement::class,
        'fespotlight' => FeSpotLightElement::class,
        'fedistantlight' => FeDistantLightElement::class,
        'lineargradient' => LinearGradientElement::class,
        'radialgradient' => RadialGradientElement::class,
        'stop' => StopElement::class,
        'pattern' => PatternElement::class,
        'mask' => MaskElement::class,
        'clippath' => ClipPathElement::class,
        'marker' => MarkerElement::class,
        'symbol' => SymbolElement::class,
        'tspan' => TspanElement::class,
        'textpath' => TextPathElement::class,
        'animate' => AnimateElement::class,
        'animatetransform' => AnimateTransformElement::class,
    ];

    /**
     * Elements that are container elements and need child parsing.
     *
     * @var array<string, bool>
     */
    private const array CONTAINER_ELEMENTS = [
        'svg' => true,
        'a' => true,
        'g' => true,
        'defs' => true,
        'filter' => true,
        'femerge' => true,
        'fecomponenttransfer' => true,
        'fediffuselighting' => true,
        'feimage' => true,
        'fespecularlighting' => true,
        'lineargradient' => true,
        'radialgradient' => true,
        'pattern' => true,
        'mask' => true,
        'clippath' => true,
        'foreignobject' => true,
        'marker' => true,
        'symbol' => true,
        'text' => true,
        'tspan' => true,
        'textpath' => true,
        'rect' => true,
        'circle' => true,
        'ellipse' => true,
        'line' => true,
        'polyline' => true,
        'polygon' => true,
        'path' => true,
    ];

    /**
     * Elements whose text content should be extracted during parsing.
     *
     * @var array<string, bool>
     */
    private const array TEXT_CONTENT_ELEMENTS = [
        'title' => true,
        'desc' => true,
        'style' => true,
        'script' => true,
        'metadata' => true,
        'text' => true,
        'tspan' => true,
        'textpath' => true,
    ];

    /**
     * Elements that need xlink:href handling.
     *
     * @var array<string, bool>
     */
    private const array XLINK_ELEMENTS = [
        'use' => true,
        'textpath' => true,
    ];

    /**
     * Creates a new DomParser with the specified profile.
     *
     * @param ParseProfile $profile      The parsing profile to use (default: LENIENT)
     * @param int          $maxInputSize Maximum allowed input size in bytes (default: 10 MB)
     */
    public function __construct(private ParseProfile $profile = ParseProfile::LENIENT, private readonly int $maxInputSize = self::DEFAULT_MAX_INPUT_SIZE)
    {
    }

    /**
     * Gets the current parsing profile.
     */
    public function getProfile(): ParseProfile
    {
        return $this->profile;
    }

    /**
     * Sets the parsing profile.
     *
     * @return $this
     */
    public function setProfile(ParseProfile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Parse SVG content string into a Document object.
     *
     * @param string $content SVG content to parse
     *
     * @return Document The parsed SVG document
     *
     * @throws ParseException If the SVG cannot be parsed
     */
    public function parse(string $content): Document
    {
        // Security: Check input size to prevent memory exhaustion attacks
        $contentLength = strlen($content);
        if ($contentLength > $this->maxInputSize) {
            throw new ParseException(sprintf('Input size (%d bytes) exceeds maximum allowed size (%d bytes)', $contentLength, $this->maxInputSize));
        }

        try {
            // Create a new DOM document
            $dom = new \DOMDocument();

            // Disable errors while loading XML to handle them ourselves
            $previousErrorSetting = libxml_use_internal_errors(true);
            libxml_clear_errors();

            // Load the SVG content with safe options (prevents XXE and entity expansion attacks)
            $result = $dom->loadXML($content, self::SAFE_LIBXML_OPTIONS);

            $xmlErrors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorSetting);

            // In strict mode, if there are any errors, throw exception
            if ($this->profile->shouldThrowOnError() && count($xmlErrors) > 0) {
                $messages = array_map(fn ($e) => trim($e->message), $xmlErrors);
                throw new ParseException('SVG parse error: '.implode(', ', $messages));
            }

            if (!$result) {
                // If parsing failed and we haven't thrown yet (e.g. lenient mode but fatal error), throw now
                $messages = array_map(fn ($e) => trim($e->message), $xmlErrors);
                throw new ParseException('SVG parse error: '.implode(', ', $messages));
            }

            // Get the root SVG element
            $rootElement = $this->findRootSvgElement($dom);

            if (!$rootElement) {
                throw new ParseException('No SVG root element found');
            }

            // Parse the root SVG element and its children
            $document = new Document();
            $svgElement = $this->parseSvgElement($rootElement);
            $document->setRootElement($svgElement);

            return $document;
        } catch (ParseException $e) {
            throw $e;
        }
    }

    /**
     * Adds a diagnostic to the collection.
     */

    /**
     * Parse an SVG file into a Document object.
     *
     * @param string $filePath Path to the SVG file
     *
     * @return Document The parsed SVG document
     *
     * @throws ParseException If the file cannot be read or parsed
     */
    public function parseFile(string $filePath): Document
    {
        if (!file_exists($filePath)) {
            throw new ParseException("File not found: $filePath");
        }

        $content = @file_get_contents($filePath);
        if (false === $content) {
            throw new ParseException("Failed to read the file: $filePath");
        }

        return $this->parse($content);
    }

    /**
     * Find the root SVG element in a DOM document.
     */
    private function findRootSvgElement(\DOMDocument $dom): ?\DOMElement
    {
        $svgElements = $dom->getElementsByTagNameNS(self::SVG_NAMESPACE, 'svg');

        if (0 === $svgElements->length) {
            // Try without namespace
            $svgElements = $dom->getElementsByTagName('svg');
        }

        return $svgElements->length > 0 ? $svgElements->item(0) : null;
    }

    /**
     * Parse an SVG element and its children.
     */
    private function parseSvgElement(\DOMElement $element): SvgElement
    {
        $svg = new SvgElement();

        // Parse all attributes (including width, height, viewBox, preserveAspectRatio)
        $this->parseCommonAttributes($element, $svg);

        // Parse children
        $this->parseChildElements($element, $svg);

        return $svg;
    }

    /**
     * Parse child elements and add them to the parent element.
     *
     * Uses a map-based lookup for O(1) element type resolution instead of a switch statement.
     */
    private function parseChildElements(\DOMElement $parentNode, ElementInterface $parentElement): void
    {
        foreach ($parentNode->childNodes as $childNode) {
            if (!$childNode instanceof \DOMElement) {
                continue;
            }

            $tagName = strtolower((string) $childNode->localName);

            // Special case: nested SVG elements need their own parsing
            if ('svg' === $tagName) {
                if ($parentElement instanceof \Atelier\Svg\Element\ContainerElementInterface) {
                    $parentElement->appendChild($this->parseSvgElement($childNode));
                }
                continue;
            }

            // Use map-based lookup for O(1) element type resolution
            $child = $this->createElementFromMap($tagName, $childNode);

            if (null !== $child && $parentElement instanceof \Atelier\Svg\Element\ContainerElementInterface) {
                $parentElement->appendChild($child);
            }
        }
    }

    /**
     * Creates an element from the element map using O(1) lookup.
     *
     * @param string      $tagName    The lowercase tag name
     * @param \DOMElement $domElement The DOM element to parse
     *
     * @return ElementInterface|null The created element or null if unknown
     */
    private function createElementFromMap(string $tagName, \DOMElement $domElement): ?ElementInterface
    {
        // Check if we know this element type
        if (!isset(self::ELEMENT_MAP[$tagName])) {
            return null;
        }

        // Create the element
        $elementClass = self::ELEMENT_MAP[$tagName];
        $element = new $elementClass();

        // Parse common attributes
        $this->parseCommonAttributes($domElement, $element);

        // Parse children for container elements
        if (isset(self::CONTAINER_ELEMENTS[$tagName])) {
            $this->parseChildElements($domElement, $element);
        }

        // Handle xlink:href for specific elements
        if (isset(self::XLINK_ELEMENTS[$tagName])) {
            if ($domElement->hasAttributeNS(self::XLINK_NAMESPACE, 'href')) {
                $href = $domElement->getAttributeNS(self::XLINK_NAMESPACE, 'href');
                // Only set non-empty href values
                if ('' !== $href) {
                    $element->setAttribute('href', $href);
                }
            }
        }

        // Extract text content for text-bearing elements
        if (isset(self::TEXT_CONTENT_ELEMENTS[$tagName])) {
            $textContent = $domElement->textContent;
            if ('' !== $textContent) {
                $element->setAttribute('textContent', $textContent);
            }
        }

        return $element;
    }

    /**
     * Parse common attributes shared by most SVG elements.
     */
    private function parseCommonAttributes(\DOMElement $element, ElementInterface $svgElement): void
    {
        // Parse all attributes generically
        foreach ($element->attributes as $attr) {
            $name = $attr->nodeName;
            $value = $attr->nodeValue;

            // Skip xmlns attributes as they're handled separately
            if (str_starts_with($name, 'xmlns')) {
                continue;
            }

            $svgElement->setAttribute($name, (string) $value);
        }
    }
}
