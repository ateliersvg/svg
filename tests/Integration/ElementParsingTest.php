<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Integration;

use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Element\Descriptive\DescElement;
use Atelier\Svg\Element\Descriptive\MetadataElement;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Element\ScriptElement;
use Atelier\Svg\Element\Structural\ForeignObjectElement;
use Atelier\Svg\Element\Structural\ViewElement;
use Atelier\Svg\Element\StyleElement;
use Atelier\Svg\Loader\DomLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DomLoader::class)]
final class ElementParsingTest extends TestCase
{
    private DomLoader $loader;
    private CompactXmlDumper $dumper;

    protected function setUp(): void
    {
        $this->loader = new DomLoader();
        $this->dumper = new CompactXmlDumper();
    }

    public function testParsesTitleElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><title>My Chart</title><rect width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $children = $document->getRootElement()->getChildren();

        $this->assertCount(2, $children);
        $this->assertInstanceOf(TitleElement::class, $children[0]);
        $this->assertSame('My Chart', $children[0]->getContent());
    }

    public function testParsesDescElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><desc>A bar chart showing sales</desc><rect width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $children = $document->getRootElement()->getChildren();

        $this->assertCount(2, $children);
        $this->assertInstanceOf(DescElement::class, $children[0]);
        $this->assertSame('A bar chart showing sales', $children[0]->getContent());
    }

    public function testParsesMetadataElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><metadata>some metadata</metadata><rect width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $children = $document->getRootElement()->getChildren();

        $this->assertCount(2, $children);
        $this->assertInstanceOf(MetadataElement::class, $children[0]);
    }

    public function testParsesStyleElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><style>.cls { fill: red; }</style><rect class="cls" width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $children = $document->getRootElement()->getChildren();

        $this->assertCount(2, $children);
        $this->assertInstanceOf(StyleElement::class, $children[0]);
        $this->assertSame('.cls { fill: red; }', $children[0]->getContent());
    }

    public function testParsesScriptElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>console.log("hi")</script><rect width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $children = $document->getRootElement()->getChildren();

        $this->assertCount(2, $children);
        $this->assertInstanceOf(ScriptElement::class, $children[0]);
        $this->assertSame('console.log("hi")', $children[0]->getContent());
    }

    public function testParsesImageElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><image href="photo.png" x="10" y="20" width="200" height="150"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $children = $document->getRootElement()->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(ImageElement::class, $children[0]);
        $this->assertSame('photo.png', $children[0]->getAttribute('href'));
        $this->assertSame('10', $children[0]->getAttribute('x'));
    }

    public function testParsesForeignObjectElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><foreignObject width="200" height="100"></foreignObject></svg>';

        $document = $this->loader->loadFromString($svg);
        $children = $document->getRootElement()->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(ForeignObjectElement::class, $children[0]);
        $this->assertSame('200', $children[0]->getAttribute('width'));
    }

    public function testParsesViewElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><view id="overview" viewBox="0 0 100 100"/><rect width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $children = $document->getRootElement()->getChildren();

        $this->assertCount(2, $children);
        $this->assertInstanceOf(ViewElement::class, $children[0]);
        $this->assertSame('overview', $children[0]->getAttribute('id'));
        $this->assertSame('0 0 100 100', $children[0]->getAttribute('viewBox'));
    }

    public function testTitleContentSurvivesRoundTrip(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><title>My Title</title><rect width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<title>My Title</title>', $output);
    }

    public function testDescContentSurvivesRoundTrip(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><desc>My Description</desc><rect width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<desc>My Description</desc>', $output);
    }

    public function testStyleContentSurvivesRoundTrip(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><style>.cls { fill: red; }</style><rect width="100" height="100"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('.cls { fill: red; }', $output);
    }

    public function testImageSurvivesRoundTrip(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><image href="photo.png" width="200" height="150"/></svg>';

        $document = $this->loader->loadFromString($svg);
        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<image', $output);
        $this->assertStringContainsString('href="photo.png"', $output);
    }

    public function testFullAccessibleSvgRoundTrip(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
            .'<title>Sales Chart</title>'
            .'<desc>A bar chart showing Q1 sales data</desc>'
            .'<style>.bar { fill: steelblue; }</style>'
            .'<rect class="bar" x="10" y="20" width="30" height="80"/>'
            .'</svg>';

        $document = $this->loader->loadFromString($svg);
        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<title>Sales Chart</title>', $output);
        $this->assertStringContainsString('<desc>A bar chart showing Q1 sales data</desc>', $output);
        $this->assertStringContainsString('.bar { fill: steelblue; }', $output);
        $this->assertStringContainsString('<rect', $output);

        // Verify valid XML
        $dom = new \DOMDocument();
        $this->assertTrue(@$dom->loadXML($output));
    }

    public function testDoubleRoundTripPreservesContent(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><title>Test</title><rect width="50" height="50"/></svg>';

        // First round-trip
        $doc1 = $this->loader->loadFromString($svg);
        $output1 = $this->dumper->dump($doc1);

        // Second round-trip
        $doc2 = $this->loader->loadFromString($output1);
        $output2 = $this->dumper->dump($doc2);

        $this->assertSame($output1, $output2);
    }
}
