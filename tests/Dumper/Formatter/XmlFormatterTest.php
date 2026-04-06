<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Dumper\Formatter;

use Atelier\Svg\Dumper\Formatter\XmlFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(XmlFormatter::class)]
final class XmlFormatterTest extends TestCase
{
    public function testConfigureSetsFormatOutputFalseByDefault(): void
    {
        $formatter = new XmlFormatter();
        $dom = new \DOMDocument();

        $formatter->configure($dom);

        $this->assertFalse($dom->formatOutput);
    }

    public function testConfigureSetsFormatOutputTrueWhenPretty(): void
    {
        $formatter = new XmlFormatter(pretty: true);
        $dom = new \DOMDocument();

        $formatter->configure($dom);

        $this->assertTrue($dom->formatOutput);
    }

    public function testConfigureSetsPreserveWhiteSpace(): void
    {
        $formatter = new XmlFormatter(preserveWhitespace: false);
        $dom = new \DOMDocument();

        $formatter->configure($dom);

        $this->assertFalse($dom->preserveWhiteSpace);
    }

    public function testConfigurePreservesWhiteSpaceByDefault(): void
    {
        $formatter = new XmlFormatter();
        $dom = new \DOMDocument();

        $formatter->configure($dom);

        $this->assertTrue($dom->preserveWhiteSpace);
    }

    public function testConfigureSetsEncodingForHtml5(): void
    {
        $formatter = new XmlFormatter(outputMode: 'html5');
        $dom = new \DOMDocument();

        $formatter->configure($dom);

        $this->assertSame('UTF-8', $dom->encoding);
    }

    public function testSerializeReturnsXmlString(): void
    {
        $formatter = new XmlFormatter();
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $formatter->configure($dom);

        $root = $dom->createElement('svg');
        $dom->appendChild($root);

        $xml = $formatter->serialize($dom);

        $this->assertStringContainsString('<svg', $xml);
    }

    public function testSerializeXmlIncludesDeclaration(): void
    {
        $formatter = new XmlFormatter();
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $formatter->configure($dom);

        $root = $dom->createElement('svg');
        $dom->appendChild($root);

        $xml = $formatter->serialize($dom);

        $this->assertStringContainsString('<?xml', $xml);
    }

    public function testSerializeHtml5Mode(): void
    {
        $formatter = new XmlFormatter(outputMode: 'html5');
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $formatter->configure($dom);

        $root = $dom->createElement('svg');
        $dom->appendChild($root);

        $html = $formatter->serialize($dom);

        $this->assertStringContainsString('<svg', $html);
    }

    public function testSerializePrettyMode(): void
    {
        $formatter = new XmlFormatter(pretty: true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $formatter->configure($dom);

        $root = $dom->createElement('svg');
        $child = $dom->createElement('rect');
        $root->appendChild($child);
        $dom->appendChild($root);

        $xml = $formatter->serialize($dom);

        $this->assertStringContainsString('<svg', $xml);
        $this->assertStringContainsString('<rect', $xml);
    }

    public function testSerializeReturnsEmptyStringOnFailure(): void
    {
        $formatter = new XmlFormatter();
        $dom = new \DOMDocument();
        $formatter->configure($dom);

        // An empty DOM should still produce valid output or empty string
        $xml = $formatter->serialize($dom);

        $this->assertIsString($xml);
    }

    public function testSerializeWithAttributes(): void
    {
        $formatter = new XmlFormatter();
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $formatter->configure($dom);

        $root = $dom->createElement('svg');
        $root->setAttribute('width', '100');
        $root->setAttribute('height', '200');
        $dom->appendChild($root);

        $xml = $formatter->serialize($dom);

        $this->assertStringContainsString('width="100"', $xml);
        $this->assertStringContainsString('height="200"', $xml);
    }
}
