<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

/**
 * Represents an SVG <script> element.
 *
 * The <script> element allows script to be embedded directly within SVG content.
 * Scripts execute when the SVG is loaded or when specific events occur.
 *
 * @see https://www.w3.org/TR/SVG11/script.html#ScriptElement
 */
final class ScriptElement extends AbstractElement
{
    private const string DEFAULT_TYPE = 'text/javascript';

    public function __construct()
    {
        parent::__construct('script');
        $this->setType(self::DEFAULT_TYPE);
    }

    /**
     * Sets the type attribute.
     *
     * @param string $type The MIME type (default: "text/javascript")
     */
    public function setType(string $type): static
    {
        $this->setAttribute('type', $type);

        return $this;
    }

    /**
     * Gets the type attribute.
     *
     * @return string|null The MIME type, or null if not set
     */
    public function getType(): ?string
    {
        return $this->getAttribute('type');
    }

    /**
     * Sets the script content.
     *
     * @param string $content The script content
     */
    public function setContent(string $content): static
    {
        $this->setAttribute('textContent', $content);

        return $this;
    }

    /**
     * Gets the script content.
     *
     * @return string|null The script content, or null if not set
     */
    public function getContent(): ?string
    {
        return $this->getAttribute('textContent');
    }
}
