<?php

declare(strict_types=1);

namespace Atelier\Svg\Dumper;

use Atelier\Svg\Document;

/**
 * Interface for serializing SVG documents to string output.
 */
interface DumperInterface
{
    /**
     * Dumps an SVG document to a string.
     *
     * @param Document $document The document to serialize
     *
     * @return string The serialized SVG markup
     */
    public function dump(Document $document): string;
}
