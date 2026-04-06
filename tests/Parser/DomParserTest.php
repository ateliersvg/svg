<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Parser;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Filter\FeBlendElement;
use Atelier\Svg\Element\Filter\FeColorMatrixElement;
use Atelier\Svg\Element\Filter\FeCompositeElement;
use Atelier\Svg\Element\Filter\FeFloodElement;
use Atelier\Svg\Element\Filter\FeGaussianBlurElement;
use Atelier\Svg\Element\Filter\FeMergeElement;
use Atelier\Svg\Element\Filter\FeMergeNodeElement;
use Atelier\Svg\Element\Filter\FeOffsetElement;
use Atelier\Svg\Element\Filter\FilterElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Structural\UseElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Exception\ParseException;
use Atelier\Svg\Parser\DomParser;
use Atelier\Svg\Parser\ParseProfile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DomParser::class)]
final class DomParserTest extends TestCase
{
    private DomParser $parser;

    protected function setUp(): void
    {
        $this->parser = new DomParser();
    }

    public function testParseSimpleSvg(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"></svg>';

        $document = $this->parser->parse($svg);

        $this->assertInstanceOf(Document::class, $document);

        $rootElement = $document->getRootElement();
        $this->assertInstanceOf(SvgElement::class, $rootElement);
        $this->assertSame('100', $rootElement->getAttribute('width'));
        $this->assertSame('100', $rootElement->getAttribute('height'));
    }

    public function testParseSvgWithViewBox(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"></svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);
        $this->assertSame('0 0 100 100', $rootElement->getAttribute('viewBox'));
    }

    public function testParseSvgWithPreserveAspectRatio(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet"></svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);
        $this->assertSame('xMidYMid meet', $rootElement->getAttribute('preserveAspectRatio'));
    }

    public function testParseRectElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <rect x="10" y="20" width="100" height="50" rx="5" ry="5" fill="red"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $this->assertCount(1, $children);

        $rect = $children[0];
        $this->assertInstanceOf(RectElement::class, $rect);
        $this->assertSame('10', $rect->getAttribute('x'));
        $this->assertSame('20', $rect->getAttribute('y'));
        $this->assertSame('100', $rect->getAttribute('width'));
        $this->assertSame('50', $rect->getAttribute('height'));
        $this->assertSame('5', $rect->getAttribute('rx'));
        $this->assertSame('5', $rect->getAttribute('ry'));
        $this->assertSame('red', $rect->getAttribute('fill'));
    }

    public function testParseCircleElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="40" fill="blue" stroke="black" stroke-width="2"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $this->assertCount(1, $children);

        $circle = $children[0];
        $this->assertInstanceOf(CircleElement::class, $circle);
        $this->assertSame('50', $circle->getAttribute('cx'));
        $this->assertSame('50', $circle->getAttribute('cy'));
        $this->assertSame('40', $circle->getAttribute('r'));
        $this->assertSame('blue', $circle->getAttribute('fill'));
        $this->assertSame('black', $circle->getAttribute('stroke'));
        $this->assertSame('2', $circle->getAttribute('stroke-width'));
    }

    public function testParseEllipseElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="100" cy="100" rx="80" ry="40"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $ellipse = $children[0];

        $this->assertInstanceOf(EllipseElement::class, $ellipse);
        $this->assertSame('100', $ellipse->getAttribute('cx'));
        $this->assertSame('100', $ellipse->getAttribute('cy'));
        $this->assertSame('80', $ellipse->getAttribute('rx'));
        $this->assertSame('40', $ellipse->getAttribute('ry'));
    }

    public function testParseLineElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <line x1="0" y1="0" x2="100" y2="100" stroke="red"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $line = $children[0];

        $this->assertInstanceOf(LineElement::class, $line);
        $this->assertSame('0', $line->getAttribute('x1'));
        $this->assertSame('0', $line->getAttribute('y1'));
        $this->assertSame('100', $line->getAttribute('x2'));
        $this->assertSame('100', $line->getAttribute('y2'));
    }

    public function testParsePolylineElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <polyline points="0,0 50,25 50,75 100,100" fill="none" stroke="black"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $polyline = $children[0];

        $this->assertInstanceOf(PolylineElement::class, $polyline);
        $this->assertSame('0,0 50,25 50,75 100,100', $polyline->getAttribute('points'));
    }

    public function testParsePolygonElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <polygon points="50,0 100,50 50,100 0,50" fill="green"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $polygon = $children[0];

        $this->assertInstanceOf(PolygonElement::class, $polygon);
        $this->assertSame('50,0 100,50 50,100 0,50', $polygon->getAttribute('points'));
    }

    public function testParsePathElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <path d="M 10 10 L 90 90 L 10 90 Z" fill="yellow"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $path = $children[0];

        $this->assertInstanceOf(PathElement::class, $path);
        $this->assertSame('M 10 10 L 90 90 L 10 90 Z', $path->getAttribute('d'));
        $this->assertSame('yellow', $path->getAttribute('fill'));
    }

    public function testParseTextElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <text x="10" y="20" font-size="16" fill="black">Hello</text>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $text = $children[0];

        $this->assertInstanceOf(TextElement::class, $text);
        $this->assertSame('10', $text->getAttribute('x'));
        $this->assertSame('20', $text->getAttribute('y'));
        $this->assertSame('16', $text->getAttribute('font-size'));
    }

    public function testParseGroupElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <g id="myGroup" transform="translate(10,20)">
                <circle cx="0" cy="0" r="5"/>
                <rect x="10" y="10" width="20" height="20"/>
            </g>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $group = $children[0];

        $this->assertInstanceOf(GroupElement::class, $group);
        $this->assertSame('myGroup', $group->getAttribute('id'));
        $this->assertSame('translate(10,20)', $group->getAttribute('transform'));

        $groupChildren = $group->getChildren();
        $this->assertCount(2, $groupChildren);
        $this->assertInstanceOf(CircleElement::class, $groupChildren[0]);
        $this->assertInstanceOf(RectElement::class, $groupChildren[1]);
    }

    public function testParseDefsElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <circle id="myCircle" r="50"/>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $defs = $children[0];

        $this->assertInstanceOf(DefsElement::class, $defs);

        $defsChildren = $defs->getChildren();
        $this->assertCount(1, $defsChildren);
        $this->assertInstanceOf(CircleElement::class, $defsChildren[0]);
    }

    public function testParseUseElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <use href="#myCircle" x="100" y="100"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $use = $children[0];

        $this->assertInstanceOf(UseElement::class, $use);
        $this->assertSame('#myCircle', $use->getAttribute('href'));
        $this->assertSame('100', $use->getAttribute('x'));
        $this->assertSame('100', $use->getAttribute('y'));
    }

    public function testParseUseElementWithXlinkHref(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <use xlink:href="#myCircle" x="100" y="100"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $children = $rootElement->getChildren();
        $use = $children[0];

        $this->assertInstanceOf(UseElement::class, $use);
        $this->assertSame('#myCircle', $use->getAttribute('href'));
    }

    public function testParseNestedGroups(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <g id="outer">
                <g id="inner">
                    <rect x="0" y="0" width="10" height="10"/>
                </g>
            </g>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $outerGroup = $rootElement->getChildren()[0];
        $this->assertInstanceOf(GroupElement::class, $outerGroup);
        $this->assertSame('outer', $outerGroup->getAttribute('id'));

        $innerGroup = $outerGroup->getChildren()[0];
        $this->assertInstanceOf(GroupElement::class, $innerGroup);
        $this->assertSame('inner', $innerGroup->getAttribute('id'));

        $rect = $innerGroup->getChildren()[0];
        $this->assertInstanceOf(RectElement::class, $rect);
    }

    public function testParseElementWithClassAttribute(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <rect class="shape primary" x="0" y="0" width="100" height="100"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $rect = $rootElement->getChildren()[0];
        $this->assertSame('shape primary', $rect->getAttribute('class'));
    }

    public function testParseElementWithStyleAttribute(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <rect style="fill: red; stroke: blue;" x="0" y="0" width="100" height="100"/>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $rect = $rootElement->getChildren()[0];
        $this->assertSame('fill: red; stroke: blue;', $rect->getAttribute('style'));
    }

    public function testParseFromFile(): void
    {
        $tempFile = sys_get_temp_dir().'/test_parse_'.uniqid().'.svg';
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"></svg>';

        file_put_contents($tempFile, $svgContent);

        try {
            $document = $this->parser->parseFile($tempFile);

            $this->assertInstanceOf(Document::class, $document);

            $rootElement = $document->getRootElement();
            $this->assertInstanceOf(SvgElement::class, $rootElement);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testParseFileThrowsExceptionWhenFileNotFound(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('File not found');

        $this->parser->parseFile('/nonexistent/file.svg');
    }

    public function testParseThrowsExceptionForInvalidXml(): void
    {
        $invalidXml = '<svg><rect></svg>';

        $this->expectException(ParseException::class);

        $this->parser->parse($invalidXml);
    }

    public function testParseThrowsExceptionWhenNoSvgElement(): void
    {
        $xml = '<div>Not an SVG</div>';

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('No SVG root element found');

        $this->parser->parse($xml);
    }

    public function testParseComplexSvg(): void
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
        <svg xmlns="http://www.w3.org/2000/svg" width="300" height="200" viewBox="0 0 300 200">
            <defs>
                <g id="star">
                    <path d="M 50,10 L 61,38 L 90,38 L 67,55 L 78,83 L 50,66 L 22,83 L 33,55 L 10,38 L 39,38 Z" fill="gold"/>
                </g>
            </defs>
            <use href="#star" x="0" y="0"/>
            <use href="#star" x="100" y="0" fill="silver"/>
            <g transform="translate(200, 100)">
                <circle cx="0" cy="0" r="30" fill="red"/>
                <text x="0" y="50" text-anchor="middle">Label</text>
            </g>
        </svg>';

        $document = $this->parser->parse($svg);

        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);
        $this->assertSame('300', $rootElement->getAttribute('width'));
        $this->assertSame('200', $rootElement->getAttribute('height'));
        $this->assertSame('0 0 300 200', $rootElement->getAttribute('viewBox'));

        $children = $rootElement->getChildren();
        $this->assertCount(4, $children);

        $this->assertInstanceOf(DefsElement::class, $children[0]);
        $this->assertInstanceOf(UseElement::class, $children[1]);
        $this->assertInstanceOf(UseElement::class, $children[2]);
        $this->assertInstanceOf(GroupElement::class, $children[3]);
    }

    public function testParseFilterElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="myFilter" x="-10%" y="-10%" width="120%" height="120%">
                    <feGaussianBlur stdDeviation="5"/>
                </filter>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();
        $this->assertNotNull($rootElement);

        $defs = $rootElement->getChildren()[0];
        $filter = $defs->getChildren()[0];

        $this->assertInstanceOf(FilterElement::class, $filter);
        $this->assertSame('myFilter', $filter->getAttribute('id'));
        $this->assertSame('-10%', $filter->getAttribute('x'));
        $this->assertSame('-10%', $filter->getAttribute('y'));
        $this->assertSame('120%', $filter->getAttribute('width'));
        $this->assertSame('120%', $filter->getAttribute('height'));

        $primitives = $filter->getChildren();
        $this->assertCount(1, $primitives);
        $this->assertInstanceOf(FeGaussianBlurElement::class, $primitives[0]);
    }

    public function testParseFeGaussianBlurElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="blur">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="5" edgeMode="duplicate" result="blurred"/>
                </filter>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $filter = $rootElement->getChildren()[0]->getChildren()[0];
        $blur = $filter->getChildren()[0];

        $this->assertInstanceOf(FeGaussianBlurElement::class, $blur);
        $this->assertSame('SourceGraphic', $blur->getAttribute('in'));
        $this->assertSame('5', $blur->getAttribute('stdDeviation'));
        $this->assertSame('duplicate', $blur->getAttribute('edgeMode'));
        $this->assertSame('blurred', $blur->getAttribute('result'));
    }

    public function testParseFeOffsetElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="offset">
                    <feOffset dx="10" dy="20" in="SourceAlpha" result="offsetResult"/>
                </filter>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $filter = $rootElement->getChildren()[0]->getChildren()[0];
        $offset = $filter->getChildren()[0];

        $this->assertInstanceOf(FeOffsetElement::class, $offset);
        $this->assertSame('10', $offset->getAttribute('dx'));
        $this->assertSame('20', $offset->getAttribute('dy'));
        $this->assertSame('SourceAlpha', $offset->getAttribute('in'));
        $this->assertSame('offsetResult', $offset->getAttribute('result'));
    }

    public function testParseFeColorMatrixElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="colorMatrix">
                    <feColorMatrix type="saturate" values="0.5" in="SourceGraphic"/>
                </filter>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $filter = $rootElement->getChildren()[0]->getChildren()[0];
        $colorMatrix = $filter->getChildren()[0];

        $this->assertInstanceOf(FeColorMatrixElement::class, $colorMatrix);
        $this->assertSame('saturate', $colorMatrix->getAttribute('type'));
        $this->assertSame('0.5', $colorMatrix->getAttribute('values'));
        $this->assertSame('SourceGraphic', $colorMatrix->getAttribute('in'));
    }

    public function testParseFeBlendElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="blend">
                    <feBlend mode="multiply" in="SourceGraphic" in2="BackgroundImage"/>
                </filter>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $filter = $rootElement->getChildren()[0]->getChildren()[0];
        $blend = $filter->getChildren()[0];

        $this->assertInstanceOf(FeBlendElement::class, $blend);
        $this->assertSame('multiply', $blend->getAttribute('mode'));
        $this->assertSame('SourceGraphic', $blend->getAttribute('in'));
        $this->assertSame('BackgroundImage', $blend->getAttribute('in2'));
    }

    public function testParseFeCompositeElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="composite">
                    <feComposite operator="in" in="SourceGraphic" in2="mask" result="composited"/>
                </filter>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $filter = $rootElement->getChildren()[0]->getChildren()[0];
        $composite = $filter->getChildren()[0];

        $this->assertInstanceOf(FeCompositeElement::class, $composite);
        $this->assertSame('in', $composite->getAttribute('operator'));
        $this->assertSame('SourceGraphic', $composite->getAttribute('in'));
        $this->assertSame('mask', $composite->getAttribute('in2'));
        $this->assertSame('composited', $composite->getAttribute('result'));
    }

    public function testParseFeMergeElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="merge">
                    <feMerge>
                        <feMergeNode in="blur"/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $filter = $rootElement->getChildren()[0]->getChildren()[0];
        $merge = $filter->getChildren()[0];

        $this->assertInstanceOf(FeMergeElement::class, $merge);

        $nodes = $merge->getChildren();
        $this->assertCount(2, $nodes);
        $this->assertInstanceOf(FeMergeNodeElement::class, $nodes[0]);
        $this->assertInstanceOf(FeMergeNodeElement::class, $nodes[1]);
        $this->assertSame('blur', $nodes[0]->getAttribute('in'));
        $this->assertSame('SourceGraphic', $nodes[1]->getAttribute('in'));
    }

    public function testParseFeFloodElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="flood">
                    <feFlood flood-color="#ff0000" flood-opacity="0.5" result="floodResult"/>
                </filter>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $filter = $rootElement->getChildren()[0]->getChildren()[0];
        $flood = $filter->getChildren()[0];

        $this->assertInstanceOf(FeFloodElement::class, $flood);
        $this->assertSame('#ff0000', $flood->getAttribute('flood-color'));
        $this->assertSame('0.5', $flood->getAttribute('flood-opacity'));
        $this->assertSame('floodResult', $flood->getAttribute('result'));
    }

    public function testParseComplexFilterWithMultiplePrimitives(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="dropShadow">
                    <feGaussianBlur in="SourceAlpha" stdDeviation="3" result="blur"/>
                    <feOffset in="blur" dx="4" dy="4" result="offsetBlur"/>
                    <feFlood flood-color="#000000" flood-opacity="0.5" result="color"/>
                    <feComposite in="color" in2="offsetBlur" operator="in" result="shadow"/>
                    <feBlend in="SourceGraphic" in2="shadow" mode="normal"/>
                </filter>
            </defs>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $filter = $rootElement->getChildren()[0]->getChildren()[0];

        $this->assertInstanceOf(FilterElement::class, $filter);
        $this->assertSame('dropShadow', $filter->getAttribute('id'));

        $primitives = $filter->getChildren();
        $this->assertCount(5, $primitives);

        $this->assertInstanceOf(FeGaussianBlurElement::class, $primitives[0]);
        $this->assertInstanceOf(FeOffsetElement::class, $primitives[1]);
        $this->assertInstanceOf(FeFloodElement::class, $primitives[2]);
        $this->assertInstanceOf(FeCompositeElement::class, $primitives[3]);
        $this->assertInstanceOf(FeBlendElement::class, $primitives[4]);

        // Verify the filter chain connections
        $this->assertSame('blur', $primitives[0]->getAttribute('result'));
        $this->assertSame('blur', $primitives[1]->getAttribute('in'));
        $this->assertSame('offsetBlur', $primitives[1]->getAttribute('result'));
        $this->assertSame('offsetBlur', $primitives[3]->getAttribute('in2'));
    }

    public function testParseRectWithFilterReference(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="myBlur">
                    <feGaussianBlur stdDeviation="5"/>
                </filter>
            </defs>
            <rect x="10" y="10" width="100" height="100" fill="blue" filter="url(#myBlur)"/>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $rect = $rootElement->getChildren()[1];
        $this->assertInstanceOf(RectElement::class, $rect);
        $this->assertSame('url(#myBlur)', $rect->getAttribute('filter'));
    }

    public function testGetProfileReturnsDefaultProfile(): void
    {
        $this->assertSame(ParseProfile::LENIENT, $this->parser->getProfile());
    }

    public function testSetProfileChangesProfile(): void
    {
        $result = $this->parser->setProfile(ParseProfile::STRICT);

        $this->assertSame($this->parser, $result);
        $this->assertSame(ParseProfile::STRICT, $this->parser->getProfile());
    }

    public function testParseThrowsExceptionWhenInputExceedsMaxSize(): void
    {
        $parser = new DomParser(maxInputSize: 10);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('exceeds maximum allowed size');

        $parser->parse($svg);
    }

    public function testParseStrictModeThrowsOnXmlWarnings(): void
    {
        $parser = new DomParser(ParseProfile::STRICT);
        // Mismatched tags produce XML errors
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><rect></svg>';

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('SVG parse error');

        $parser->parse($svg);
    }

    public function testParseFileThrowsExceptionWhenFileCannotBeRead(): void
    {
        if ('root' === ($_SERVER['USER'] ?? '')) {
            $this->markTestSkipped('Cannot test file permission as root');
        }

        $tempFile = sys_get_temp_dir().'/test_unreadable_'.uniqid().'.svg';
        file_put_contents($tempFile, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        chmod($tempFile, 0000);

        try {
            $this->expectException(ParseException::class);
            $this->expectExceptionMessage('Failed to read the file');

            $this->parser->parseFile($tempFile);
        } finally {
            chmod($tempFile, 0644);
            unlink($tempFile);
        }
    }

    public function testParseSvgWithoutNamespace(): void
    {
        $svg = '<?xml version="1.0"?><svg width="100" height="100"><rect x="0" y="0" width="50" height="50"/></svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $this->assertInstanceOf(SvgElement::class, $rootElement);
        $this->assertSame('100', $rootElement->getAttribute('width'));
    }

    public function testParseNestedSvgElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
            <svg x="10" y="10" width="100" height="100">
                <circle cx="50" cy="50" r="40"/>
            </svg>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $children = $rootElement->getChildren();
        $this->assertCount(1, $children);

        $nestedSvg = $children[0];
        $this->assertInstanceOf(SvgElement::class, $nestedSvg);
        $this->assertSame('10', $nestedSvg->getAttribute('x'));

        $nestedChildren = $nestedSvg->getChildren();
        $this->assertCount(1, $nestedChildren);
        $this->assertInstanceOf(CircleElement::class, $nestedChildren[0]);
    }

    public function testParseUnknownElementIsIgnored(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <customElement>Test</customElement>
            <rect x="0" y="0" width="10" height="10"/>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $children = $rootElement->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(RectElement::class, $children[0]);
    }

    public function testParseSkipsXmlnsAttributes(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">
            <rect xmlnsfoo="bar" width="10" height="10"/>
        </svg>';

        $document = $this->parser->parse($svg);
        $rootElement = $document->getRootElement();

        $rect = $rootElement->getChildren()[0];
        $this->assertInstanceOf(RectElement::class, $rect);
        $this->assertNull($rect->getAttribute('xmlnsfoo'));
        $this->assertSame('10', $rect->getAttribute('width'));
    }
}
