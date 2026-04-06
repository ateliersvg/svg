<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Descriptive;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents an SVG <metadata> element.
 *
 * The <metadata> element can contain metadata information. The metadata content
 * should be elements from other XML namespaces such as RDF, FOAF, etc.
 *
 * @see https://www.w3.org/TR/SVG11/metadata.html#MetadataElement
 */
final class MetadataElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('metadata');
    }

    /**
     * Sets the metadata content.
     *
     * @param string $content The metadata content (typically XML)
     */
    public function setContent(string $content): static
    {
        $this->setAttribute('textContent', $content);

        return $this;
    }

    /**
     * Gets the metadata content.
     *
     * @return string|null The metadata content, or null if not set
     */
    public function getContent(): ?string
    {
        return $this->getAttribute('textContent');
    }
}
