<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Dumper;

use Atelier\Svg\Document;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Dumper\XmlDumper;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompactXmlDumper::class)]
#[CoversClass(XmlDumper::class)]
final class CompactXmlDumperTest extends TestCase
{
    private CompactXmlDumper $dumper;

    protected function setUp(): void
    {
        $this->dumper = new CompactXmlDumper();
    }

    public function testDumpSimpleSvg(): void
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
    }

    public function testDumpProducesValidXml(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $svg->setWidth(200);
        $svg->setHeight(200);
        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($output));
    }

    public function testDumpWithChildElements(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10);
        $rect->setY(20);
        $rect->setWidth(50);
        $rect->setHeight(30);

        $svg->appendChild($rect);
        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<rect', $output);
        $this->assertStringContainsString('x="10"', $output);
    }

    public function testDumpNestedElements(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $group = new GroupElement();
        $group->setAttribute('id', 'g1');

        $circle = new CircleElement();
        $circle->setRadius(25);

        $group->appendChild($circle);
        $svg->appendChild($group);
        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<g', $output);
        $this->assertStringContainsString('id="g1"', $output);
        $this->assertStringContainsString('<circle', $output);
    }

    public function testDumpPathElement(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $path = new PathElement();
        $path->setPathData('M 0 0 L 10 10');
        $path->setAttribute('fill', 'none');

        $svg->appendChild($path);
        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<path', $output);
        $this->assertStringContainsString('d="M 0 0 L 10 10"', $output);
        $this->assertStringContainsString('fill="none"', $output);
    }

    public function testDumpWithoutXmlDeclaration(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $document->setRootElement($svg);

        $output = $this->dumper->includeXmlDeclaration(false)->dump($document);

        $this->assertDoesNotMatchRegularExpression('/^<\?xml/i', $output);
        $this->assertStringStartsWith('<svg', ltrim($output));
    }

    public function testDumpWithXmlDeclarationByDefault(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $document->setRootElement($svg);

        $output = $this->dumper->dump($document);

        $this->assertStringContainsString('<?xml', $output);
    }

    public function testDumpThrowsExceptionWhenNoRootElement(): void
    {
        $document = new Document();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Document has no root element');

        $this->dumper->dump($document);
    }

    public function testDumpToFile(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $svg->setWidth(50);
        $document->setRootElement($svg);

        $tempFile = sys_get_temp_dir().'/test_compact_dumper_'.uniqid().'.svg';

        try {
            $this->dumper->dumpToFile($document, $tempFile);

            $this->assertFileExists($tempFile);

            $content = file_get_contents($tempFile);
            $this->assertIsString($content);
            $this->assertStringContainsString('<svg', $content);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testDumpCompactOutputIsNotPrettyPrinted(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setWidth(10);
        $rect->setHeight(10);
        $svg->appendChild($rect);
        $document->setRootElement($svg);

        $output = $this->dumper->includeXmlDeclaration(false)->dump($document);

        // Compact output should not have indentation between elements
        $this->assertStringNotContainsString('  <rect', $output);
    }

    public function testIncludeXmlDeclarationReturnsSelf(): void
    {
        $result = $this->dumper->includeXmlDeclaration(true);

        $this->assertSame($this->dumper, $result);
    }
}
