<?php

declare(strict_types=1);

namespace Atelier\Svg\Dumper\Formatter;

final readonly class XmlFormatter implements XmlFormatterInterface
{
    public function __construct(
        private bool $pretty = false,
        private bool $preserveWhitespace = true,
        private string $outputMode = 'xml', // 'xml', 'html5'
    ) {
    }

    public function configure(\DOMDocument $dom): void
    {
        $dom->formatOutput = $this->pretty;
        $dom->preserveWhiteSpace = $this->preserveWhitespace;

        if ('html5' === $this->outputMode) {
            $dom->encoding = 'UTF-8';
        }
    }

    public function serialize(\DOMDocument $dom): string
    {
        if ('html5' === $this->outputMode) {
            return $this->serializeHtml5($dom);
        }

        $xml = $dom->saveXML();

        return false === $xml ? '' : $xml;
    }

    private function serializeHtml5(\DOMDocument $dom): string
    {
        // This could use saveHTML or a custom serializer
        $html = $dom->saveHTML($dom->documentElement);

        return false === $html ? '' : $html;
    }
}
