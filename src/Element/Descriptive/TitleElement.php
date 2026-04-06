<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Descriptive;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents the <title> SVG element.
 *
 * The <title> element provides an accessible title for the SVG content.
 * Each container element or graphics element in an SVG drawing can supply
 * a title description string where the description is text-only.
 *
 * @see https://www.w3.org/TR/SVG11/struct.html#TitleElement
 */
final class TitleElement extends AbstractElement
{
    public function __construct(array $initialAttributes = [])
    {
        parent::__construct('title', [], $initialAttributes);
    }

    /**
     * Sets the text content of the title.
     *
     * @param string $content The title text
     */
    public function setContent(string $content): static
    {
        $this->setAttribute('textContent', $content);

        return $this;
    }

    /**
     * Gets the text content of the title.
     *
     * @return string|null The title text, or null if not set
     */
    public function getContent(): ?string
    {
        return $this->getAttribute('textContent');
    }
}
