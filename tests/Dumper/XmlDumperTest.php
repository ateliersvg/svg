<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Dumper;

use Atelier\Svg\Document;
use Atelier\Svg\Dumper\PrettyXmlDumper;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Structural\UseElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Element\Text\TextElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrettyXmlDumper::class)]
#[CoversClass(\Atelier\Svg\Dumper\XmlDumper::class)]
final class XmlDumperTest extends TestCase
{
    private PrettyXmlDumper $dumper;

    protected function setUp(): void
    {
        $this->dumper = new PrettyXmlDumper();
    }

    public function testDumpSimpleSvgDocument(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $svg->setWidth(100);
        $svg->setHeight(100);

        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<svg', $output);
        $this->assertStringContainsString('width="100"', $output);
        $this->assertStringContainsString('height="100"', $output);
        $this->assertStringContainsString('xmlns="http://www.w3.org/2000/svg"', $output);
    }

    public function testDumpSvgWithShapes(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $svg->setWidth(200);
        $svg->setHeight(200);

        $rect = new RectElement();
        $rect->setX(10);
        $rect->setY(10);
        $rect->setWidth(80);
        $rect->setHeight(60);
        $rect->setAttribute('fill', 'red');

        $circle = new CircleElement();
        $circle->setCx(150);
        $circle->setCy(150);
        $circle->setRadius(30);
        $circle->setAttribute('fill', 'blue');

        $svg->appendChild($rect);
        $svg->appendChild($circle);

        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<rect', $output);
        $this->assertStringContainsString('x="10"', $output);
        $this->assertStringContainsString('fill="red"', $output);
        $this->assertStringContainsString('<circle', $output);
        $this->assertStringContainsString('cx="150"', $output);
        $this->assertStringContainsString('fill="blue"', $output);
    }

    public function testDumpNestedGroups(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $group1 = new GroupElement();
        $group1->setAttribute('id', 'group1');
        $group1->setAttribute('transform', 'translate(10,20)');

        $group2 = new GroupElement();
        $group2->setAttribute('id', 'group2');

        $rect = new RectElement();
        $rect->setX(0);
        $rect->setY(0);
        $rect->setWidth(50);
        $rect->setHeight(50);

        $group2->appendChild($rect);
        $group1->appendChild($group2);
        $svg->appendChild($group1);

        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('id="group1"', $output);
        $this->assertStringContainsString('id="group2"', $output);
        $this->assertStringContainsString('transform="translate(10,20)"', $output);

        // Verify nesting structure
        $this->assertMatchesRegularExpression('/<g[^>]*id="group1"[^>]*>.*<g[^>]*id="group2"[^>]*>.*<rect/s', $output);
    }

    public function testDumpPathElement(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $path = new PathElement();
        $path->setPathData('M 10 10 L 90 90 L 10 90 Z');
        $path->setAttribute('stroke', 'black');
        $path->setAttribute('fill', 'none');

        $svg->appendChild($path);
        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<path', $output);
        $this->assertStringContainsString('d="M 10 10 L 90 90 L 10 90 Z"', $output);
        $this->assertStringContainsString('stroke="black"', $output);
        $this->assertStringContainsString('fill="none"', $output);
    }

    public function testDumpTextElement(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $text = new TextElement();
        $text->setX(10);
        $text->setY(20);
        $text->setAttribute('font-size', '16');

        $svg->appendChild($text);
        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<text', $output);
        $this->assertStringContainsString('x="10"', $output);
        $this->assertStringContainsString('y="20"', $output);
        $this->assertStringContainsString('font-size="16"', $output);
    }

    public function testDumpDefsAndUse(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $defs = new DefsElement();

        $circle = new CircleElement();
        $circle->setAttribute('id', 'myCircle');
        $circle->setRadius(50);

        $defs->appendChild($circle);

        $use = new UseElement();
        $use->setAttribute('href', '#myCircle');
        $use->setX(100);
        $use->setY(100);

        $svg->appendChild($defs);
        $svg->appendChild($use);

        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<defs', $output);
        $this->assertStringContainsString('id="myCircle"', $output);
        $this->assertStringContainsString('<use', $output);
        $this->assertStringContainsString('href="#myCircle"', $output);
    }

    public function testDumpWithViewBox(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $svg->setWidth(200);
        $svg->setHeight(200);
        $svg->setViewbox('0 0 100 100');

        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('viewBox="0 0 100 100"', $output);
    }

    public function testDumpWithPreserveAspectRatio(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $svg->setPreserveAspectRatio('xMidYMid meet');

        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        // The PreserveAspectRatio class may normalize the value
        $this->assertStringContainsString('preserveAspectRatio=', $output);
        $this->assertStringContainsString('xMidYMid', $output);
    }

    public function testDumpToFile(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $svg->setWidth(100);
        $svg->setHeight(100);

        $document->setRootElement($svg);

        $tempFile = sys_get_temp_dir().'/test_svg_'.uniqid().'.svg';

        try {
            $this->dumper->dumpToFile($document, $tempFile);

            $this->assertFileExists($tempFile);

            $content = file_get_contents($tempFile);
            $this->assertIsString($content);
            $this->assertStringContainsString('<svg', $content);
            $this->assertStringContainsString('width="100"', $content);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testDumpThrowsExceptionWhenNoRootElement(): void
    {
        $document = new Document();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Document has no root element');

        $this->dumper->dump($document);
    }

    public function testDumpComplexSvg(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $svg->setWidth(300);
        $svg->setHeight(200);
        $svg->setViewbox('0 0 300 200');

        $defs = new DefsElement();

        $group = new GroupElement();
        $group->setAttribute('id', 'star');

        $path = new PathElement();
        $path->setPathData('M 50,10 L 61,38 L 90,38 L 67,55 L 78,83 L 50,66 L 22,83 L 33,55 L 10,38 L 39,38 Z');
        $path->setAttribute('fill', 'gold');

        $group->appendChild($path);
        $defs->appendChild($group);

        $use1 = new UseElement();
        $use1->setAttribute('href', '#star');
        $use1->setX(0);
        $use1->setY(0);

        $use2 = new UseElement();
        $use2->setAttribute('href', '#star');
        $use2->setX(100);
        $use2->setY(0);
        $use2->setAttribute('fill', 'silver');

        $svg->appendChild($defs);
        $svg->appendChild($use1);
        $svg->appendChild($use2);

        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<defs', $output);
        $this->assertStringContainsString('id="star"', $output);
        $this->assertStringContainsString('fill="gold"', $output);
        $this->assertStringContainsString('href="#star"', $output);

        // Verify it's valid XML
        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($output));
    }

    public function testDumpTextElementWithTextContent(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $text = new TextElement();
        $text->setX(50);
        $text->setY(100);
        $text->setTextContent('Hello World');

        $svg->appendChild($text);
        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<text', $output);
        $this->assertStringContainsString('x="50"', $output);
        $this->assertStringContainsString('y="100"', $output);
        $this->assertStringContainsString('>Hello World</text>', $output);
        $this->assertStringNotContainsString('textContent=', $output);

        // Verify it's valid XML
        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($output));

        // Verify the text content is accessible as a text node
        $dom->loadXML($output);
        $textElements = $dom->getElementsByTagName('text');
        $this->assertSame(1, $textElements->length);
        $this->assertSame('Hello World', $textElements->item(0)->textContent);
    }

    public function testDumpTextElementWithSpecialCharacters(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $text = new TextElement();
        $text->setX(10);
        $text->setY(20);
        $text->setTextContent('<Hello> & "World"');

        $svg->appendChild($text);
        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        // Verify it's valid XML with escaped special characters
        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($output));

        // Verify the text content is properly decoded
        $dom->loadXML($output);
        $textElements = $dom->getElementsByTagName('text');
        $this->assertSame('<Hello> & "World"', $textElements->item(0)->textContent);
    }

    public function testCanOmitXmlDeclaration(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $document->setRootElement($svg);

        $output = $this->dumper->includeXmlDeclaration(false)->dump($document);

        $this->assertDoesNotMatchRegularExpression('/^<\?xml/i', $output);
        $this->assertStringStartsWith('<svg', ltrim($output));
    }

    public function testDumpToFileThrowsOnWriteFailure(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $document->setRootElement($svg);

        $this->expectException(\Atelier\Svg\Exception\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Failed to write to/');

        $this->dumper->dumpToFile($document, '/nonexistent/directory/output.svg');
    }
}
