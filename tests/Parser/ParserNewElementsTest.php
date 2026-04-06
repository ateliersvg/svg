<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Parser;

use Atelier\Svg\Element\Animation\AnimateElement;
use Atelier\Svg\Element\Clipping\ClipPathElement;
use Atelier\Svg\Element\Clipping\MaskElement;
use Atelier\Svg\Element\Filter\FeDistantLightElement;
use Atelier\Svg\Element\Filter\FePointLightElement;
use Atelier\Svg\Element\Filter\FeSpotLightElement;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\PatternElement;
use Atelier\Svg\Element\Gradient\RadialGradientElement;
use Atelier\Svg\Element\Gradient\StopElement;
use Atelier\Svg\Element\Structural\MarkerElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Element\Text\TextPathElement;
use Atelier\Svg\Element\Text\TspanElement;
use Atelier\Svg\Parser\DomParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for newly implemented SVG 1.1 elements in the parser.
 *
 * These tests verify that Pattern, Mask, ClipPath, Filter Light Sources,
 * Gradients, Markers, Symbols, Text elements, and Animations can be
 * correctly parsed from SVG strings.
 */
#[CoversClass(DomParser::class)]
final class ParserNewElementsTest extends TestCase
{
    private DomParser $parser;

    protected function setUp(): void
    {
        $this->parser = new DomParser();
    }

    public function testParseLinearGradient(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="red"/>
                    <stop offset="100%" stop-color="blue"/>
                </linearGradient>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $gradient = $defs->getChildren()[0];

        $this->assertInstanceOf(LinearGradientElement::class, $gradient);
        $this->assertSame('grad1', $gradient->getAttribute('id'));
        $this->assertSame('0%', $gradient->getAttribute('x1'));
        $this->assertSame('100%', $gradient->getAttribute('x2'));
        $this->assertCount(2, $gradient->getChildren());
        $this->assertInstanceOf(StopElement::class, $gradient->getChildren()[0]);
    }

    public function testParseRadialGradient(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <radialGradient id="grad2" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="white"/>
                    <stop offset="100%" stop-color="black"/>
                </radialGradient>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $gradient = $defs->getChildren()[0];

        $this->assertInstanceOf(RadialGradientElement::class, $gradient);
        $this->assertSame('grad2', $gradient->getAttribute('id'));
        $this->assertSame('50%', $gradient->getAttribute('cx'));
    }

    public function testParsePattern(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="pattern1" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                    <circle cx="10" cy="10" r="5" fill="red"/>
                </pattern>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $pattern = $defs->getChildren()[0];

        $this->assertInstanceOf(PatternElement::class, $pattern);
        $this->assertSame('pattern1', $pattern->getAttribute('id'));
        $this->assertSame('20', $pattern->getAttribute('width'));
        $this->assertSame('userSpaceOnUse', $pattern->getAttribute('patternUnits'));
        $this->assertTrue($pattern->hasChildren());
    }

    public function testParseMask(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <mask id="mask1" x="0" y="0" width="100" height="100">
                    <rect x="0" y="0" width="100" height="100" fill="white"/>
                </mask>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $mask = $defs->getChildren()[0];

        $this->assertInstanceOf(MaskElement::class, $mask);
        $this->assertSame('mask1', $mask->getAttribute('id'));
        $this->assertSame('100', $mask->getAttribute('width'));
        $this->assertTrue($mask->hasChildren());
    }

    public function testParseClipPath(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <clipPath id="clip1" clipPathUnits="userSpaceOnUse">
                    <rect x="0" y="0" width="100" height="100"/>
                </clipPath>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $clipPath = $defs->getChildren()[0];

        $this->assertInstanceOf(ClipPathElement::class, $clipPath);
        $this->assertSame('clip1', $clipPath->getAttribute('id'));
        $this->assertSame('userSpaceOnUse', $clipPath->getAttribute('clipPathUnits'));
        $this->assertTrue($clipPath->hasChildren());
    }

    public function testParseMarker(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <marker id="arrow" markerWidth="10" markerHeight="10" refX="5" refY="5">
                    <path d="M 0 0 L 10 5 L 0 10 z" fill="black"/>
                </marker>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $marker = $defs->getChildren()[0];

        $this->assertInstanceOf(MarkerElement::class, $marker);
        $this->assertSame('arrow', $marker->getAttribute('id'));
        $this->assertSame('10', $marker->getAttribute('markerWidth'));
        $this->assertTrue($marker->hasChildren());
    }

    public function testParseSymbol(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <symbol id="icon" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                </symbol>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $symbol = $defs->getChildren()[0];

        $this->assertInstanceOf(SymbolElement::class, $symbol);
        $this->assertSame('icon', $symbol->getAttribute('id'));
        $this->assertSame('0 0 24 24', $symbol->getAttribute('viewBox'));
        $this->assertTrue($symbol->hasChildren());
    }

    public function testParseTspan(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <text x="10" y="20">
                Hello <tspan fill="red">World</tspan>
            </text>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $text = $document->getRootElement()->getChildren()[0];
        $tspan = $text->getChildren()[0];

        $this->assertInstanceOf(TspanElement::class, $tspan);
        $this->assertSame('red', $tspan->getAttribute('fill'));
    }

    public function testParseTextPath(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <path id="curve" d="M 10 80 Q 95 10 180 80"/>
            </defs>
            <text>
                <textPath href="#curve">Text on a path</textPath>
            </text>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $text = $document->getRootElement()->getChildren()[1];
        $textPath = $text->getChildren()[0];

        $this->assertInstanceOf(TextPathElement::class, $textPath);
        $this->assertSame('#curve', $textPath->getAttribute('href'));
    }

    public function testParseFilterWithPointLight(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="light1">
                    <feDiffuseLighting surfaceScale="5">
                        <fePointLight x="100" y="100" z="50"/>
                    </feDiffuseLighting>
                </filter>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $filter = $defs->getChildren()[0];
        $diffuse = $filter->getChildren()[0];
        $light = $diffuse->getChildren()[0];

        $this->assertInstanceOf(FePointLightElement::class, $light);
        $this->assertSame('100', $light->getAttribute('x'));
        $this->assertSame('100', $light->getAttribute('y'));
        $this->assertSame('50', $light->getAttribute('z'));
    }

    public function testParseFilterWithSpotLight(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="light2">
                    <feSpecularLighting>
                        <feSpotLight x="100" y="100" z="50" pointsAtX="0" pointsAtY="0" pointsAtZ="0"/>
                    </feSpecularLighting>
                </filter>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $filter = $defs->getChildren()[0];
        $specular = $filter->getChildren()[0];
        $light = $specular->getChildren()[0];

        $this->assertInstanceOf(FeSpotLightElement::class, $light);
        $this->assertSame('100', $light->getAttribute('x'));
        $this->assertSame('0', $light->getAttribute('pointsAtX'));
    }

    public function testParseFilterWithDistantLight(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="light3">
                    <feDiffuseLighting>
                        <feDistantLight azimuth="45" elevation="60"/>
                    </feDiffuseLighting>
                </filter>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];
        $filter = $defs->getChildren()[0];
        $diffuse = $filter->getChildren()[0];
        $light = $diffuse->getChildren()[0];

        $this->assertInstanceOf(FeDistantLightElement::class, $light);
        $this->assertSame('45', $light->getAttribute('azimuth'));
        $this->assertSame('60', $light->getAttribute('elevation'));
    }

    public function testParseAnimateElement(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="100" height="100">
                <animate attributeName="x" from="0" to="100" dur="1s" repeatCount="indefinite"/>
            </rect>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $rect = $document->getRootElement()->getChildren()[0];
        $animate = $rect->getChildren()[0];

        $this->assertInstanceOf(AnimateElement::class, $animate);
        $this->assertSame('x', $animate->getAttribute('attributeName'));
        $this->assertSame('1s', $animate->getAttribute('dur'));
    }

    public function testParseComplexSvgWithMultipleNewElements(): void
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="grad">
                    <stop offset="0%" stop-color="red"/>
                </linearGradient>
                <pattern id="pat" width="10" height="10">
                    <circle cx="5" cy="5" r="2"/>
                </pattern>
                <mask id="mask">
                    <rect width="100" height="100" fill="white"/>
                </mask>
                <clipPath id="clip">
                    <circle cx="50" cy="50" r="40"/>
                </clipPath>
                <marker id="arrow" markerWidth="10" markerHeight="10">
                    <path d="M 0 0 L 10 5 L 0 10 z"/>
                </marker>
                <symbol id="icon" viewBox="0 0 10 10">
                    <circle cx="5" cy="5" r="4"/>
                </symbol>
            </defs>
        </svg>
        SVG;

        $document = $this->parser->parse($svg);
        $defs = $document->getRootElement()->getChildren()[0];

        $this->assertCount(6, $defs->getChildren());
        $this->assertInstanceOf(LinearGradientElement::class, $defs->getChildren()[0]);
        $this->assertInstanceOf(PatternElement::class, $defs->getChildren()[1]);
        $this->assertInstanceOf(MaskElement::class, $defs->getChildren()[2]);
        $this->assertInstanceOf(ClipPathElement::class, $defs->getChildren()[3]);
        $this->assertInstanceOf(MarkerElement::class, $defs->getChildren()[4]);
        $this->assertInstanceOf(SymbolElement::class, $defs->getChildren()[5]);
    }
}
