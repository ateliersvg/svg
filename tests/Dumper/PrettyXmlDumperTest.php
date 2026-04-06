<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Dumper;

use Atelier\Svg\Document;
use Atelier\Svg\Dumper\PrettyXmlDumper;
use Atelier\Svg\Dumper\XmlDumper;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrettyXmlDumper::class)]
#[CoversClass(XmlDumper::class)]
final class PrettyXmlDumperTest extends TestCase
{
    private PrettyXmlDumper $dumper;

    protected function setUp(): void
    {
        $this->dumper = new PrettyXmlDumper();
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

    public function testDumpOutputIsPrettyPrinted(): void
    {
        $document = new Document();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setWidth(10);
        $rect->setHeight(10);
        $svg->appendChild($rect);
        $document->setRootElement($svg);

        $output = $this->dumper->includeXmlDeclaration(false)->dump($document);

        $this->assertStringContainsString("\n", $output);
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

        $tempFile = sys_get_temp_dir().'/test_pretty_dumper_'.uniqid().'.svg';

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

    public function testIncludeXmlDeclarationReturnsSelf(): void
    {
        $result = $this->dumper->includeXmlDeclaration(true);

        $this->assertSame($this->dumper, $result);
    }
}
