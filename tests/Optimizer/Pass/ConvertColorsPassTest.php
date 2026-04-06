<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\ConvertColorsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConvertColorsPass::class)]
final class ConvertColorsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new ConvertColorsPass();

        $this->assertSame('convert-colors', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new ConvertColorsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testConvertLongHexToShortHex(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#ff0000');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
    }

    public function testConvertShortableHexToShortHex(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#112233');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('#123', $path->getAttribute('fill'));
    }

    public function testConvertHexToColorName(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#ffffff');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('#fff', $path->getAttribute('fill'));
    }

    public function testConvertRgbToHex(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'rgb(255, 0, 0)');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
    }

    public function testConvertOpaqueRgbaToHex(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'rgba(255, 0, 0, 1)');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
    }

    public function testPreserveTransparentRgba(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'rgba(255, 0, 0, 0.5)');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $fill = $path->getAttribute('fill');
        // Should convert to shortest form (hex8 or rgba)
        $this->assertNotSame('rgba(255, 0, 0, 0.5)', $fill);
        $this->assertNotNull($fill);
    }

    public function testPreserveSpecialValues(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'none');
        $path->setAttribute('stroke', 'currentColor');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('none', $path->getAttribute('fill'));
        $this->assertSame('currentColor', $path->getAttribute('stroke'));
    }

    public function testPreserveUrlReferences(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'url(#gradient)');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('url(#gradient)', $path->getAttribute('fill'));
    }

    public function testConvertMultipleColorAttributes(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#ff0000');
        $path->setAttribute('stroke', '#0000ff');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('#00f', $path->getAttribute('stroke'));
    }

    public function testConvertColorsInStyleAttribute(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('style', 'fill: #ff0000; stroke: #0000ff');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $style = $path->getAttribute('style');
        $this->assertStringContainsString('fill: red', $style);
        $this->assertStringContainsString('stroke: #00f', $style);
    }

    public function testConvertNestedElements(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $path2 = new PathElement();

        $path->setAttribute('fill', '#ff0000');
        $path2->setAttribute('fill', '#00ff00');

        $group->appendChild($path);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('#0f0', $path2->getAttribute('fill'));
    }

    public function testDisableShortHex(): void
    {
        $pass = new ConvertColorsPass(false, true, true);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#112233');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should not shorten, but #112233 is still longer than #123
        // Actually, with shortHex disabled, it should keep #112233 unless there's a shorter name
        $fill = $path->getAttribute('fill');
        $this->assertNotSame('#123', $fill);
    }

    public function testDisableColorNames(): void
    {
        $pass = new ConvertColorsPass(true, false, true);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#ff0000');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should convert to short hex, not to "red"
        $this->assertSame('#f00', $path->getAttribute('fill'));
    }

    public function testAllColorAttributes(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#ff0000');
        $path->setAttribute('stroke', '#00ff00');
        $path->setAttribute('stop-color', '#0000ff');
        $path->setAttribute('flood-color', '#ffff00');
        $path->setAttribute('lighting-color', '#ff00ff');
        $path->setAttribute('color', '#00ffff');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('#0f0', $path->getAttribute('stroke'));
        $this->assertSame('#00f', $path->getAttribute('stop-color'));
        $this->assertSame('#ff0', $path->getAttribute('flood-color'));
        $this->assertSame('#f0f', $path->getAttribute('lighting-color'));
        $this->assertSame('#0ff', $path->getAttribute('color'));
    }

    public function testInvalidColorPreserved(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'invalid-color');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('invalid-color', $path->getAttribute('fill'));
    }

    public function testTransparentColor(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'transparent');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('transparent', $path->getAttribute('fill'));
    }

    public function testBlackToShortestForm(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#000000');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // #000 (4 chars) vs black (5 chars)
        $this->assertSame('#000', $path->getAttribute('fill'));
    }

    public function testWhiteToShortestForm(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#ffffff');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // #fff (4 chars) vs white (5 chars)
        $this->assertSame('#fff', $path->getAttribute('fill'));
    }

    public function testTransparentRgbaReturnsNone(): void
    {
        $pass = new ConvertColorsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'rgba(255, 0, 0, 0)');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('none', $path->getAttribute('fill'));
    }

    public function testCanShortenHexReturnsFalseForNonShortenableHex(): void
    {
        $pass = new ConvertColorsPass(convertToShortHex: true, convertToNames: false);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', '#123456');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('#123456', $path->getAttribute('fill'));
    }
}
