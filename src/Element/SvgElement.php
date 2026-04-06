<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\PreserveAspectRatio;
use Atelier\Svg\Value\Viewbox;

/**
 * Represents an SVG <svg> root element.
 *
 * The <svg> element is a container that defines a new coordinate system and viewport.
 * It can contain other SVG elements and is the root element of an SVG document.
 */
final class SvgElement extends AbstractContainerElement
{
    private const string DEFAULT_XMLNS = 'http://www.w3.org/2000/svg';
    private const string DEFAULT_VERSION = '1.1';

    public function __construct()
    {
        parent::__construct('svg');

        // Set default xmlns and version
        $this->setXmlns(self::DEFAULT_XMLNS);
        $this->setVersion(self::DEFAULT_VERSION);
    }

    /**
     * Sets the width of the SVG element.
     *
     * @param string|int|float|Length $width The width value
     */
    public function setWidth(string|int|float|Length $width): static
    {
        if ($width instanceof Length) {
            $this->setAttribute('width', $width->toString());
        } else {
            $this->setAttribute('width', (string) $width);
        }

        return $this;
    }

    /**
     * Gets the width of the SVG element.
     *
     * @return Length|null The width as a Length object, or null if not set
     */
    public function getWidth(): ?Length
    {
        $width = $this->getAttribute('width');

        return null !== $width ? Length::parse($width) : null;
    }

    /**
     * Sets the height of the SVG element.
     *
     * @param string|int|float|Length $height The height value
     */
    public function setHeight(string|int|float|Length $height): static
    {
        if ($height instanceof Length) {
            $this->setAttribute('height', $height->toString());
        } else {
            $this->setAttribute('height', (string) $height);
        }

        return $this;
    }

    /**
     * Gets the height of the SVG element.
     *
     * @return Length|null The height as a Length object, or null if not set
     */
    public function getHeight(): ?Length
    {
        $height = $this->getAttribute('height');

        return null !== $height ? Length::parse($height) : null;
    }

    /**
     * Sets the viewBox attribute.
     *
     * @param string|Viewbox $viewbox The viewBox value
     */
    public function setViewbox(string|Viewbox $viewbox): static
    {
        if ($viewbox instanceof Viewbox) {
            $this->setAttribute('viewBox', $viewbox->toString());
        } else {
            // Validate by parsing
            $parsed = Viewbox::parse($viewbox);
            $this->setAttribute('viewBox', $parsed->toString());
        }

        return $this;
    }

    /**
     * Gets the viewBox attribute.
     *
     * @return Viewbox|null The viewBox as a Viewbox object, or null if not set
     */
    public function getViewbox(): ?Viewbox
    {
        $viewBox = $this->getAttribute('viewBox');

        return null !== $viewBox ? Viewbox::parse($viewBox) : null;
    }

    /**
     * Sets the preserveAspectRatio attribute.
     *
     * @param string|PreserveAspectRatio $preserveAspectRatio The preserveAspectRatio value
     */
    public function setPreserveAspectRatio(string|PreserveAspectRatio $preserveAspectRatio): static
    {
        if ($preserveAspectRatio instanceof PreserveAspectRatio) {
            $this->setAttribute('preserveAspectRatio', $preserveAspectRatio->toString());
        } else {
            // Validate by parsing
            $parsed = PreserveAspectRatio::parse($preserveAspectRatio);
            $this->setAttribute('preserveAspectRatio', $parsed->toString());
        }

        return $this;
    }

    /**
     * Gets the preserveAspectRatio attribute.
     *
     * @return PreserveAspectRatio|null The preserveAspectRatio as an object, or null if not set
     */
    public function getPreserveAspectRatio(): ?PreserveAspectRatio
    {
        $par = $this->getAttribute('preserveAspectRatio');

        return null !== $par ? PreserveAspectRatio::parse($par) : null;
    }

    /**
     * Sets the xmlns (XML namespace) attribute.
     *
     * @param string $xmlns The namespace URI
     */
    public function setXmlns(string $xmlns = self::DEFAULT_XMLNS): static
    {
        $this->setAttribute('xmlns', $xmlns);

        return $this;
    }

    /**
     * Gets the xmlns attribute.
     *
     * @return string|null The namespace URI, or null if not set
     */
    public function getXmlns(): ?string
    {
        return $this->getAttribute('xmlns');
    }

    /**
     * Sets the version attribute.
     *
     * @param string $version The SVG version
     */
    public function setVersion(string $version = self::DEFAULT_VERSION): static
    {
        $this->setAttribute('version', $version);

        return $this;
    }

    /**
     * Gets the version attribute.
     *
     * @return string|null The SVG version, or null if not set
     */
    public function getVersion(): ?string
    {
        return $this->getAttribute('version');
    }
}
