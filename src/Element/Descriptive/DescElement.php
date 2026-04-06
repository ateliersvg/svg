<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Descriptive;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents the <desc> SVG element.
 *
 * The <desc> element provides an accessible description of the SVG content.
 * Each container element or graphics element in an SVG drawing can supply
 * a description string where the description is text-only.
 *
 * @see https://www.w3.org/TR/SVG11/struct.html#DescElement
 */
final class DescElement extends AbstractElement
{
    public function __construct(array $initialAttributes = [])
    {
        parent::__construct('desc', [], $initialAttributes);
    }

    /**
     * Sets the text content of the description.
     *
     * @param string $content The description text
     */
    public function setContent(string $content): static
    {
        $this->setAttribute('textContent', $content);

        return $this;
    }

    /**
     * Gets the text content of the description.
     *
     * @return string|null The description text, or null if not set
     */
    public function getContent(): ?string
    {
        return $this->getAttribute('textContent');
    }
}
