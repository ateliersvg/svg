<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Integration;

use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Sanitizer\Sanitizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sanitizer::class)]
final class SanitizerIntegrationTest extends TestCase
{
    private DomLoader $loader;
    private CompactXmlDumper $dumper;

    protected function setUp(): void
    {
        $this->loader = new DomLoader();
        $this->dumper = new CompactXmlDumper();
    }

    public function testRemovesScriptElementFromLoadedSvg(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40"/><script>alert("xss")</script></svg>';

        $document = $this->loader->loadFromString($svg);
        Sanitizer::default()->sanitize($document);
        $output = $this->dumper->dump($document);

        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringNotContainsString('alert', $output);
        $this->assertStringContainsString('<circle', $output);
    }

    public function testRemovesEventHandlerFromLoadedSvg(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><rect onclick="alert(1)" fill="red" width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        Sanitizer::default()->sanitize($document);
        $output = $this->dumper->dump($document);

        $this->assertStringNotContainsString('onclick', $output);
        $this->assertStringNotContainsString('alert', $output);
        $this->assertStringContainsString('fill="red"', $output);
    }

    public function testRemovesJavascriptUrlFromLoadedSvg(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><a href="javascript:alert(1)"><circle cx="50" cy="50" r="40"/></a></svg>';

        $document = $this->loader->loadFromString($svg);
        Sanitizer::default()->sanitize($document);
        $output = $this->dumper->dump($document);

        $this->assertStringNotContainsString('javascript:', $output);
        $this->assertStringContainsString('<circle', $output);
    }

    public function testStrictRemovesForeignObjectFromLoadedSvg(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100"/><foreignObject width="200" height="200"><div xmlns="http://www.w3.org/1999/xhtml">HTML</div></foreignObject></svg>';

        $document = $this->loader->loadFromString($svg);
        Sanitizer::strict()->sanitize($document);
        $output = $this->dumper->dump($document);

        $this->assertStringNotContainsString('foreignObject', $output);
        $this->assertStringContainsString('<rect', $output);
    }

    public function testPermissiveKeepsEventHandlersFromLoadedSvg(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" onclick="doStuff()"/></svg>';

        $document = $this->loader->loadFromString($svg);
        Sanitizer::permissive()->sanitize($document);
        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('onclick', $output);
    }

    public function testRemovesMultipleXssVectors(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">'
            .'<script>alert("xss")</script>'
            .'<rect onclick="steal()" onmouseover="track()" fill="blue" width="100" height="100"/>'
            .'<a href="javascript:void(0)"><circle cx="50" cy="50" r="10"/></a>'
            .'<circle cx="100" cy="100" r="30" fill="green"/>'
            .'</svg>';

        $document = $this->loader->loadFromString($svg);
        Sanitizer::default()->sanitize($document);
        $output = $this->dumper->dump($document);

        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringNotContainsString('onclick', $output);
        $this->assertStringNotContainsString('onmouseover', $output);
        $this->assertStringNotContainsString('javascript:', $output);
        $this->assertStringContainsString('fill="blue"', $output);
        $this->assertStringContainsString('fill="green"', $output);
    }

    public function testSanitizedSvgRemainsValidXml(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>malicious()</script><g><rect width="50" height="50"/></g></svg>';

        $document = $this->loader->loadFromString($svg);
        Sanitizer::strict()->sanitize($document);
        $output = $this->dumper->dump($document);

        $dom = new \DOMDocument();
        $loaded = @$dom->loadXML($output);
        $this->assertTrue($loaded, 'Sanitized output must be valid XML');
    }

    public function testDataTextHtmlUrlIsRemoved(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><a href="data:text/html,&lt;script&gt;alert(1)&lt;/script&gt;"><rect width="10" height="10"/></a></svg>';

        $document = $this->loader->loadFromString($svg);
        Sanitizer::default()->sanitize($document);
        $output = $this->dumper->dump($document);

        $this->assertStringNotContainsString('data:text/html', $output);
    }

    public function testSanitizationPreservesStructure(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
            .'<defs><linearGradient id="g1"><stop offset="0" stop-color="red"/></linearGradient></defs>'
            .'<g id="layer1"><rect fill="url(#g1)" width="100" height="100"/></g>'
            .'</svg>';

        $document = $this->loader->loadFromString($svg);
        Sanitizer::default()->sanitize($document);
        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('linearGradient', $output);
        $this->assertStringContainsString('id="layer1"', $output);
        $this->assertStringContainsString('viewBox', $output);
    }
}
