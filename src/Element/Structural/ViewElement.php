<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Structural;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Value\PreserveAspectRatio;
use Atelier\Svg\Value\Viewbox;

/**
 * Represents an SVG <view> element.
 *
 * The <view> element defines a particular view of the SVG document. A view can be
 * used to zoom and pan to a particular area of the SVG, similar to a named view.
 *
 * @see https://www.w3.org/TR/SVG11/linking.html#ViewElement
 */
final class ViewElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('view');
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
}
