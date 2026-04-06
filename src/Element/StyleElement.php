<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

/**
 * Represents an SVG <style> element.
 *
 * The <style> element allows style sheets to be embedded directly within SVG content.
 * The element supports CSS styling rules.
 *
 * @see https://www.w3.org/TR/SVG11/styling.html#StyleElement
 */
final class StyleElement extends AbstractElement
{
    private const string DEFAULT_TYPE = 'text/css';

    public function __construct()
    {
        parent::__construct('style');
        $this->setType(self::DEFAULT_TYPE);
    }

    /**
     * Sets the type attribute.
     *
     * @param string $type The MIME type (default: "text/css")
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
     * Sets the CSS content.
     *
     * @param string $content The CSS content
     */
    public function setContent(string $content): static
    {
        $this->setAttribute('textContent', $content);

        return $this;
    }

    /**
     * Gets the CSS content.
     *
     * @return string|null The CSS content, or null if not set
     */
    public function getContent(): ?string
    {
        return $this->getAttribute('textContent');
    }
}
