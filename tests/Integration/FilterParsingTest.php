<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Integration;

use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Parser\DomParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for SVG filter parsing and serialization.
 *
 * Tests that filters can be:
 * - Parsed from SVG strings
 * - Manipulated programmatically
 * - Serialized back to valid SVG
 * - Round-tripped without loss of information
 */
#[CoversClass(DomParser::class)]
final class FilterParsingTest extends TestCase
{
    private DomParser $parser;
    private CompactXmlDumper $dumper;

    protected function setUp(): void
    {
        $this->parser = new DomParser();
        $this->dumper = new CompactXmlDumper();
    }

    public function testParseSimpleBlurFilter(): void
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
  <defs>
    <filter id="blur">
      <feGaussianBlur in="SourceGraphic" stdDeviation="5"/>
    </filter>
  </defs>
  <rect x="50" y="50" width="100" height="100" fill="blue" filter="url(#blur)"/>
</svg>
SVG;

        $doc = $this->parser->parse($svg);
        $root = $doc->getRootElement();

        $this->assertNotNull($root);

        // Find the filter
        $defs = $root->getChildren()[0];
        $this->assertEquals('defs', $defs->getTagName());

        $filter = $defs->getChildren()[0];
        $this->assertEquals('filter', $filter->getTagName());
        $this->assertEquals('blur', $filter->getId());

        // Check filter primitive
        $blur = $filter->getChildren()[0];
        $this->assertEquals('feGaussianBlur', $blur->getTagName());
        $this->assertEquals('SourceGraphic', $blur->getAttribute('in'));
        $this->assertEquals('5', $blur->getAttribute('stdDeviation'));

        // Check filter is applied to rect
        $rect = $root->getChildren()[1];
        $this->assertEquals('url(#blur)', $rect->getAttribute('filter'));
    }

    public function testParseDropShadowFilter(): void
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
  <defs>
    <filter id="shadow">
      <feGaussianBlur in="SourceAlpha" stdDeviation="3" result="blur"/>
      <feOffset in="blur" dx="4" dy="4" result="offsetBlur"/>
      <feFlood flood-color="#000000" flood-opacity="0.5" result="color"/>
      <feComposite in="color" in2="offsetBlur" operator="in" result="shadow"/>
      <feBlend in="SourceGraphic" in2="shadow" mode="normal"/>
    </filter>
  </defs>
</svg>
SVG;

        $doc = $this->parser->parse($svg);
        $root = $doc->getRootElement();

        $defs = $root->getChildren()[0];
        $filter = $defs->getChildren()[0];

        $this->assertEquals('filter', $filter->getTagName());
        $this->assertEquals('shadow', $filter->getId());

        $primitives = iterator_to_array($filter->getChildren());
        $this->assertCount(5, $primitives);

        // Verify each filter primitive
        $this->assertEquals('feGaussianBlur', $primitives[0]->getTagName());
        $this->assertEquals('feOffset', $primitives[1]->getTagName());
        $this->assertEquals('feFlood', $primitives[2]->getTagName());
        $this->assertEquals('feComposite', $primitives[3]->getTagName());
        $this->assertEquals('feBlend', $primitives[4]->getTagName());
    }

    public function testParseColorMatrixFilter(): void
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
  <defs>
    <filter id="grayscale">
      <feColorMatrix type="saturate" values="0"/>
    </filter>
  </defs>
</svg>
SVG;

        $doc = $this->parser->parse($svg);
        $root = $doc->getRootElement();

        $defs = $root->getChildren()[0];
        $filter = $defs->getChildren()[0];
        $colorMatrix = $filter->getChildren()[0];

        $this->assertEquals('feColorMatrix', $colorMatrix->getTagName());
        $this->assertEquals('saturate', $colorMatrix->getAttribute('type'));
        $this->assertEquals('0', $colorMatrix->getAttribute('values'));
    }

    public function testParseFeMergeWithNodes(): void
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
  <defs>
    <filter id="merge">
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>
</svg>
SVG;

        $doc = $this->parser->parse($svg);
        $root = $doc->getRootElement();

        $defs = $root->getChildren()[0];
        $filter = $defs->getChildren()[0];
        $merge = $filter->getChildren()[0];

        $this->assertEquals('feMerge', $merge->getTagName());

        $nodes = iterator_to_array($merge->getChildren());
        $this->assertCount(2, $nodes);
        $this->assertEquals('feMergeNode', $nodes[0]->getTagName());
        $this->assertEquals('blur', $nodes[0]->getAttribute('in'));
        $this->assertEquals('feMergeNode', $nodes[1]->getTagName());
        $this->assertEquals('SourceGraphic', $nodes[1]->getAttribute('in'));
    }

    public function testRoundTripFilter(): void
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
<defs>
<filter id="test">
<feGaussianBlur in="SourceGraphic" stdDeviation="5"/>
<feOffset dx="2" dy="2"/>
<feColorMatrix type="saturate" values="1.5"/>
</filter>
</defs>
<rect x="10" y="10" width="50" height="50" fill="red" filter="url(#test)"/>
</svg>
SVG;

        // Parse
        $doc = $this->parser->parse($svg);

        // Serialize
        $output = $this->dumper->dump($doc);

        // Parse again
        $doc2 = $this->parser->parse($output);

        // Verify structure is preserved
        $root = $doc2->getRootElement();
        $defs = $root->getChildren()[0];
        $filter = $defs->getChildren()[0];

        $this->assertEquals('filter', $filter->getTagName());
        $this->assertEquals('test', $filter->getId());

        $primitives = iterator_to_array($filter->getChildren());
        $this->assertCount(3, $primitives);
        $this->assertEquals('feGaussianBlur', $primitives[0]->getTagName());
        $this->assertEquals('feOffset', $primitives[1]->getTagName());
        $this->assertEquals('feColorMatrix', $primitives[2]->getTagName());

        // Verify attributes are preserved
        $this->assertEquals('5', $primitives[0]->getAttribute('stdDeviation'));
        $this->assertEquals('2', $primitives[1]->getAttribute('dx'));
        $this->assertEquals('saturate', $primitives[2]->getAttribute('type'));
    }

    public function testParseAllCommonFilterPrimitives(): void
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg">
  <defs>
    <filter id="comprehensive">
      <feGaussianBlur stdDeviation="5"/>
      <feOffset dx="1" dy="1"/>
      <feColorMatrix type="matrix" values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 1 0"/>
      <feBlend mode="multiply"/>
      <feComposite operator="over"/>
      <feFlood flood-color="#fff"/>
      <feMerge>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
      <feConvolveMatrix order="3" kernelMatrix="1 0 0 0 1 0 0 0 1"/>
      <feComponentTransfer>
        <feFuncR type="linear" slope="0.5"/>
        <feFuncG type="linear" slope="0.5"/>
        <feFuncB type="linear" slope="0.5"/>
        <feFuncA type="linear" slope="1"/>
      </feComponentTransfer>
      <feDiffuseLighting surfaceScale="1"/>
      <feDisplacementMap scale="10"/>
      <feImage href="#otherElement"/>
      <feMorphology operator="dilate" radius="1"/>
      <feSpecularLighting surfaceScale="5"/>
      <feTile/>
      <feTurbulence type="turbulence" baseFrequency="0.05"/>
    </filter>
  </defs>
</svg>
SVG;

        $doc = $this->parser->parse($svg);
        $root = $doc->getRootElement();
        $defs = $root->getChildren()[0];
        $filter = $defs->getChildren()[0];

        $this->assertEquals('filter', $filter->getTagName());

        $primitives = iterator_to_array($filter->getChildren());

        // Verify all primitives are parsed
        $expectedTypes = [
            'feGaussianBlur',
            'feOffset',
            'feColorMatrix',
            'feBlend',
            'feComposite',
            'feFlood',
            'feMerge',
            'feConvolveMatrix',
            'feComponentTransfer',
            'feDiffuseLighting',
            'feDisplacementMap',
            'feImage',
            'feMorphology',
            'feSpecularLighting',
            'feTile',
            'feTurbulence',
        ];

        $this->assertCount(count($expectedTypes), $primitives);

        foreach ($expectedTypes as $index => $expectedType) {
            $this->assertEquals($expectedType, $primitives[$index]->getTagName());
        }
    }
}
