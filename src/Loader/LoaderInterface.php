<?php

declare(strict_types=1);

namespace Atelier\Svg\Loader;

use Atelier\Svg\Document;

/**
 * Interface for loading SVG documents from various sources.
 */
interface LoaderInterface
{
    /**
     * Loads an SVG document from a string.
     *
     * @param string $svg The SVG content as XML string
     *
     * @return Document The loaded document
     */
    public function loadFromString(string $svg): Document;

    /**
     * Loads an SVG document from a file.
     *
     * @param string $path Path to the SVG file
     *
     * @return Document The loaded document
     */
    public function loadFromFile(string $path): Document;
}
