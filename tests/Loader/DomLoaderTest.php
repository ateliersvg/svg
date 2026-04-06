<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Loader;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Loader\DomLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DomLoader::class)]
final class DomLoaderTest extends TestCase
{
    private DomLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new DomLoader();
    }

    public function testLoadFromStringSimpleSvg(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"></svg>';

        $document = $this->loader->loadFromString($svg);

        $this->assertInstanceOf(Document::class, $document);

        $rootElement = $document->getRootElement();
        $this->assertInstanceOf(SvgElement::class, $rootElement);
        $this->assertSame('100', $rootElement->getAttribute('width'));
        $this->assertSame('100', $rootElement->getAttribute('height'));
    }

    public function testLoadFromStringWithShapes(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
            <rect x="10" y="10" width="80" height="60" fill="red"/>
            <circle cx="150" cy="150" r="30" fill="blue"/>
        </svg>';

        $document = $this->loader->loadFromString($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $this->assertCount(2, $children);

        $this->assertInstanceOf(RectElement::class, $children[0]);
        $this->assertInstanceOf(CircleElement::class, $children[1]);

        $this->assertSame('red', $children[0]->getAttribute('fill'));
        $this->assertSame('blue', $children[1]->getAttribute('fill'));
    }

    public function testLoadFromStringWithNestedGroups(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <g id="outer" transform="translate(10,20)">
                <g id="inner">
                    <rect x="0" y="0" width="50" height="50"/>
                </g>
            </g>
        </svg>';

        $document = $this->loader->loadFromString($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $outerGroup = $rootElement->getChildren()[0];
        $this->assertInstanceOf(GroupElement::class, $outerGroup);
        $this->assertSame('outer', $outerGroup->getAttribute('id'));
        $this->assertSame('translate(10,20)', $outerGroup->getAttribute('transform'));

        $innerGroup = $outerGroup->getChildren()[0];
        $this->assertInstanceOf(GroupElement::class, $innerGroup);
        $this->assertSame('inner', $innerGroup->getAttribute('id'));
    }

    public function testLoadFromStringWithViewBox(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"></svg>';

        $document = $this->loader->loadFromString($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);
        $this->assertSame('0 0 100 100', $rootElement->getAttribute('viewBox'));
    }

    public function testLoadFromStringWithPreserveAspectRatio(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet"></svg>';

        $document = $this->loader->loadFromString($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);
        $this->assertSame('xMidYMid meet', $rootElement->getAttribute('preserveAspectRatio'));
    }

    public function testLoadFromFile(): void
    {
        $tempFile = sys_get_temp_dir().'/test_load_'.uniqid().'.svg';
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150">
            <circle cx="75" cy="75" r="50" fill="green"/>
        </svg>';

        file_put_contents($tempFile, $svgContent);

        try {
            $document = $this->loader->loadFromFile($tempFile);

            $this->assertInstanceOf(Document::class, $document);

            $rootElement = $document->getRootElement();
            $this->assertNotNull($rootElement);
            $this->assertSame('150', $rootElement->getAttribute('width'));

            $children = $rootElement->getChildren();
            $this->assertCount(1, $children);
            $this->assertInstanceOf(CircleElement::class, $children[0]);
            $this->assertSame('green', $children[0]->getAttribute('fill'));
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testLoadFromFileThrowsExceptionWhenFileNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not read file');

        $this->loader->loadFromFile('/nonexistent/file.svg');
    }

    public function testLoadFromStringWithComplexSvg(): void
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
        <svg xmlns="http://www.w3.org/2000/svg" width="300" height="200" viewBox="0 0 300 200">
            <defs>
                <g id="star">
                    <path d="M 50,10 L 61,38 Z" fill="gold"/>
                </g>
            </defs>
            <use href="#star" x="0" y="0"/>
            <g transform="translate(100, 100)">
                <circle cx="0" cy="0" r="30" fill="red"/>
                <rect x="10" y="10" width="20" height="20" fill="blue"/>
            </g>
        </svg>';

        $document = $this->loader->loadFromString($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);
        $this->assertSame('300', $rootElement->getAttribute('width'));
        $this->assertSame('200', $rootElement->getAttribute('height'));
        $this->assertSame('0 0 300 200', $rootElement->getAttribute('viewBox'));

        $children = $rootElement->getChildren();
        $this->assertGreaterThanOrEqual(2, count($children));
    }

    public function testLoadFromStringWithAttributes(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <rect id="myRect" class="shape primary" style="opacity: 0.5"
                  x="0" y="0" width="100" height="100"
                  fill="red" stroke="black" stroke-width="2"/>
        </svg>';

        $document = $this->loader->loadFromString($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $rect = $rootElement->getChildren()[0];
        $this->assertSame('myRect', $rect->getAttribute('id'));
        $this->assertSame('shape primary', $rect->getAttribute('class'));
        $this->assertSame('opacity: 0.5', $rect->getAttribute('style'));
        $this->assertSame('red', $rect->getAttribute('fill'));
        $this->assertSame('black', $rect->getAttribute('stroke'));
        $this->assertSame('2', $rect->getAttribute('stroke-width'));
    }

    public function testLoadFromStringPreservesAttributeValues(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <circle cx="50.5" cy="75.25" r="30.75"/>
        </svg>';

        $document = $this->loader->loadFromString($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $circle = $rootElement->getChildren()[0];
        $this->assertSame('50.5', $circle->getAttribute('cx'));
        $this->assertSame('75.25', $circle->getAttribute('cy'));
        $this->assertSame('30.75', $circle->getAttribute('r'));
    }

    public function testLoadFromStringHandlesEmptyElements(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <g id="empty"/>
            <rect x="0" y="0" width="10" height="10"/>
        </svg>';

        $document = $this->loader->loadFromString($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $this->assertCount(2, $children);

        $emptyGroup = $children[0];
        $this->assertInstanceOf(GroupElement::class, $emptyGroup);
        $this->assertSame('empty', $emptyGroup->getAttribute('id'));
        $this->assertCount(0, $emptyGroup->getChildren());
    }

    public function testRoundTripLoadAndDump(): void
    {
        $originalSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">
            <rect x="10" y="10" width="80" height="80" fill="red"/>
        </svg>';

        // Load the SVG
        $document = $this->loader->loadFromString($originalSvg);

        // Dump it back
        $dumper = new \Atelier\Svg\Dumper\PrettyXmlDumper();
        $dumpedSvg = $dumper->dump($document);

        // Load it again
        $document2 = $this->loader->loadFromString($dumpedSvg);

        // Verify the structure is preserved
        $rootElement = $document2->getRootElement();
        $this->assertNotNull($rootElement);
        $this->assertSame('100', $rootElement->getAttribute('width'));
        $this->assertSame('100', $rootElement->getAttribute('height'));

        $children = $rootElement->getChildren();
        $this->assertCount(1, $children);

        $rect = $children[0];
        $this->assertInstanceOf(RectElement::class, $rect);
        $this->assertSame('10', $rect->getAttribute('x'));
        $this->assertSame('10', $rect->getAttribute('y'));
        $this->assertSame('80', $rect->getAttribute('width'));
        $this->assertSame('80', $rect->getAttribute('height'));
        $this->assertSame('red', $rect->getAttribute('fill'));
    }

    public function testRoundTripComplexSvg(): void
    {
        $originalSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 100 100">
            <defs>
                <circle id="dot" r="5"/>
            </defs>
            <g id="group1" transform="translate(50,50)">
                <use href="#dot" x="0" y="0" fill="red"/>
                <use href="#dot" x="10" y="0" fill="blue"/>
            </g>
        </svg>';

        // Load -> Dump -> Load cycle
        $document1 = $this->loader->loadFromString($originalSvg);

        $dumper = new \Atelier\Svg\Dumper\PrettyXmlDumper();
        $dumpedSvg = $dumper->dump($document1);

        $document2 = $this->loader->loadFromString($dumpedSvg);

        // Verify structure
        $rootElement = $document2->getRootElement();
        $this->assertNotNull($rootElement);
        $this->assertSame('200', $rootElement->getAttribute('width'));
        $this->assertSame('200', $rootElement->getAttribute('height'));
        $this->assertSame('0 0 100 100', $rootElement->getAttribute('viewBox'));

        $children = $rootElement->getChildren();
        $this->assertGreaterThanOrEqual(2, count($children));
    }

    public function testBuildDocumentFromDomThrowsExceptionWhenSaveXmlFails(): void
    {
        $dom = new class extends \DOMDocument {
            public function saveXML(?\DOMNode $node = null, int $options = 0): string|false
            {
                return false;
            }
        };

        $reflection = new \ReflectionClass(DomLoader::class);
        $method = $reflection->getMethod('buildDocumentFromDom');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to serialize DOM to XML');

        $method->invoke($this->loader, $dom);
    }
}
