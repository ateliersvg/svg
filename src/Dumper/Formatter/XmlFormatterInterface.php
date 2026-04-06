<?php

declare(strict_types=1);

namespace Atelier\Svg\Dumper\Formatter;

/**
 * Interface for configuring and serializing DOMDocument output.
 */
interface XmlFormatterInterface
{
    /**
     * Configures the DOMDocument settings before serialization.
     */
    public function configure(\DOMDocument $dom): void;

    /**
     * Serializes the DOMDocument to a string.
     */
    public function serialize(\DOMDocument $dom): string;
}
