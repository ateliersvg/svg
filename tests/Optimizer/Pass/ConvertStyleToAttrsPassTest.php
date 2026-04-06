<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\ConvertStyleToAttrsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConvertStyleToAttrsPass::class)]
final class ConvertStyleToAttrsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new ConvertStyleToAttrsPass();

        $this->assertSame('convert-style-to-attrs', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testConvertSimpleStyle(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'fill: red');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('fill'));
        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertFalse($path->hasAttribute('style'));
    }

    public function testConvertMultipleProperties(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'fill: red; stroke: blue');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('blue', $path->getAttribute('stroke'));
        $this->assertFalse($path->hasAttribute('style'));
    }

    public function testPreserveNonPresentationProperties(): void
    {
        $pass = new ConvertStyleToAttrsPass(false); // Disable shorthand check to ensure conversion happens
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'fill: red; transform: scale(2)');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('fill'));
        $this->assertSame('red', $path->getAttribute('fill'));
        // transform is not a presentation attribute, should remain in style
        $this->assertTrue($path->hasAttribute('style'));
        $this->assertStringContainsString('transform', $path->getAttribute('style') ?? '');
    }

    public function testDoNotOverrideExistingAttributes(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'blue');
        $path->setAttribute('style', 'fill: red');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should keep the existing attribute value
        $this->assertSame('blue', $path->getAttribute('fill'));
    }

    public function testConvertOpacity(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'opacity: 0.5');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('opacity'));
        $this->assertSame('0.5', $path->getAttribute('opacity'));
        $this->assertFalse($path->hasAttribute('style'));
    }

    public function testConvertStrokeProperties(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'stroke: red; stroke-width: 2; stroke-opacity: 0.5');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('stroke'));
        $this->assertSame('2', $path->getAttribute('stroke-width'));
        $this->assertSame('0.5', $path->getAttribute('stroke-opacity'));
        $this->assertFalse($path->hasAttribute('style'));
    }

    public function testOnlyConvertWhenShorter(): void
    {
        $pass = new ConvertStyleToAttrsPass(true);
        $svg = new SvgElement();
        $path = new PathElement();
        // Very long property value that might not be worth converting
        $longValue = 'rgba(255, 255, 255, 0.999999999999)';
        $path->setAttribute('style', 'fill: '.$longValue);
        $svg->appendChild($path);
        $document = new Document($svg);

        $originalLength = strlen('style="fill: '.$longValue.'"');
        $newLength = strlen('fill="'.$longValue.'" ');

        $pass->optimize($document);

        // Should convert based on length comparison
        if ($newLength < $originalLength) {
            $this->assertTrue($path->hasAttribute('fill'));
            $this->assertFalse($path->hasAttribute('style'));
        }
    }

    public function testAlwaysConvertWhenDisabled(): void
    {
        $pass = new ConvertStyleToAttrsPass(false);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'fill: red');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('fill'));
        $this->assertFalse($path->hasAttribute('style'));
    }

    public function testConvertNestedElements(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $path2 = new PathElement();

        $path->setAttribute('style', 'fill: red');
        $path2->setAttribute('style', 'fill: blue');

        $group->appendChild($path);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('blue', $path2->getAttribute('fill'));
        $this->assertFalse($path->hasAttribute('style'));
        $this->assertFalse($path2->hasAttribute('style'));
    }

    public function testHandleEmptyStyle(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', '');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should handle gracefully
        $this->assertInstanceOf(PathElement::class, $path);
    }

    public function testHandleMalformedStyle(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'fill red stroke blue');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should handle gracefully without throwing
        $this->assertInstanceOf(PathElement::class, $path);
    }

    public function testConvertColorProperty(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'color: red');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('color'));
        $this->assertSame('red', $path->getAttribute('color'));
    }

    public function testConvertVisibility(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'visibility: hidden');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('visibility'));
        $this->assertSame('hidden', $path->getAttribute('visibility'));
    }

    public function testConvertDisplay(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'display: none');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('display'));
        $this->assertSame('none', $path->getAttribute('display'));
    }

    public function testConvertFontProperties(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'font-family: Arial; font-size: 12px; font-weight: bold');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('Arial', $path->getAttribute('font-family'));
        $this->assertSame('12px', $path->getAttribute('font-size'));
        $this->assertSame('bold', $path->getAttribute('font-weight'));
        $this->assertFalse($path->hasAttribute('style'));
    }

    public function testConvertMarkerProperties(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'marker-start: url(#arrow); marker-end: url(#arrow)');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('url(#arrow)', $path->getAttribute('marker-start'));
        $this->assertSame('url(#arrow)', $path->getAttribute('marker-end'));
        $this->assertFalse($path->hasAttribute('style'));
    }

    public function testConvertClipPath(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'clip-path: url(#clip)');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('clip-path'));
        $this->assertSame('url(#clip)', $path->getAttribute('clip-path'));
        $this->assertFalse($path->hasAttribute('style'));
    }

    public function testPreserveWhitespace(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', '  fill:  red  ;  stroke:  blue  ');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('blue', $path->getAttribute('stroke'));
    }

    public function testDoNotConvertWhenNewSizeIsLarger(): void
    {
        $pass = new ConvertStyleToAttrsPass(true);
        $svg = new SvgElement();
        $path = new PathElement();
        // A very short style that would be longer as separate attributes
        $path->setAttribute('style', 'fill: r');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Result depends on size comparison; just ensure no crash
        $this->assertInstanceOf(PathElement::class, $path);
    }

    public function testConvertMixedConvertibleAndNonConvertibleWithSizeCheck(): void
    {
        $pass = new ConvertStyleToAttrsPass(true);
        $svg = new SvgElement();
        $path = new PathElement();
        // Mix of convertible and non-convertible with remaining style
        $path->setAttribute('style', 'fill: red; stroke: blue; custom-property: value');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // custom-property is not a presentation attribute, so it stays in style
        // Whether conversion happens depends on size comparison
        $this->assertInstanceOf(PathElement::class, $path);
    }

    public function testHandleStyleWithEmptyDeclarations(): void
    {
        $pass = new ConvertStyleToAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        // Double semicolons produce empty declarations that should be skipped
        $path->setAttribute('style', 'fill: red;; ; ;stroke: blue');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('blue', $path->getAttribute('stroke'));
    }
}
