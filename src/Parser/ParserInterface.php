<?php

declare(strict_types=1);

namespace Atelier\Svg\Parser;

use Atelier\Svg\Document;

/**
 * Interface for parsing SVG content into a Document.
 */
interface ParserInterface
{
    /**
     * Parses an SVG string into a Document.
     *
     * @param string $string The SVG content to parse
     *
     * @return Document The parsed document
     */
    public function parse(string $string): Document;
}
