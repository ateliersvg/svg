<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveDefaultAttributesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveDefaultAttributesPass::class)]
final class RemoveDefaultAttributesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveDefaultAttributesPass();

        $this->assertSame('remove-default-attributes', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveDefaultFillOnRect(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'black');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('fill'));
    }

    public function testRemoveDefaultStrokeOnRect(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'none');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke'));
    }

    public function testRemoveDefaultStrokeWidth(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke-width', '1');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-width'));
    }

    public function testRemoveDefaultOpacity(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('opacity', '1');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('opacity'));
    }

    public function testRemoveDefaultFillOpacity(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill-opacity', '1');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('fill-opacity'));
    }

    public function testRemoveDefaultStrokeOpacity(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke-opacity', '1');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-opacity'));
    }

    public function testPreserveNonDefaultFill(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'red');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('fill'));
        $this->assertSame('red', $path->getAttribute('fill'));
    }

    public function testPreserveNonDefaultOpacity(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('opacity', '0.5');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('opacity'));
        $this->assertSame('0.5', $path->getAttribute('opacity'));
    }

    public function testRemoveDefaultXYOnSvg(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('x', '0');
        $svg->setAttribute('y', '0');
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($svg->hasAttribute('x'));
        $this->assertFalse($svg->hasAttribute('y'));
    }

    public function testRemoveDefaultStrokeLinecap(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke-linecap', 'butt');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-linecap'));
    }

    public function testRemoveDefaultStrokeLinejoin(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke-linejoin', 'miter');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-linejoin'));
    }

    public function testRemoveDefaultFillRule(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill-rule', 'nonzero');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('fill-rule'));
    }

    public function testRemoveDefaultsFromNestedElements(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'black');
        $path2 = new PathElement();
        $path2->setAttribute('stroke', 'none');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path1->hasAttribute('fill'));
        $this->assertFalse($path2->hasAttribute('stroke'));
    }

    public function testRemoveSpecificAttributesOnly(): void
    {
        $pass = new RemoveDefaultAttributesPass(['fill', 'stroke']);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'black');
        $path->setAttribute('stroke', 'none');
        $path->setAttribute('opacity', '1');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('fill'));
        $this->assertFalse($path->hasAttribute('stroke'));
        // Opacity should not be removed because it's not in the list
        $this->assertTrue($path->hasAttribute('opacity'));
    }

    public function testNormalizeNumericValues(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        // Test with trailing zeros
        $path->setAttribute('stroke-width', '1.0');
        $path->setAttribute('opacity', '1.00');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-width'));
        $this->assertFalse($path->hasAttribute('opacity'));
    }

    public function testCaseInsensitiveComparison(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'None');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke'));
    }

    public function testRemoveDefaultsFromGroup(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('fill', 'black');
        $group->setAttribute('stroke', 'none');
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($group->hasAttribute('fill'));
        $this->assertFalse($group->hasAttribute('stroke'));
    }

    public function testPreserveNonDefaultStrokeLinecap(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke-linecap', 'round');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('stroke-linecap'));
        $this->assertSame('round', $path->getAttribute('stroke-linecap'));
    }

    public function testRemoveDefaultVisibility(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('visibility', 'visible');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('visibility'));
    }

    public function testRemoveDefaultDisplay(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('display', 'inline');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('display'));
    }

    public function testNullAttributeValueIsSkipped(): void
    {
        $pass = new RemoveDefaultAttributesPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'red');
        $path->removeAttribute('fill');
        $path->setAttribute('stroke', 'blue');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('fill'));
        $this->assertTrue($path->hasAttribute('stroke'));
    }
}
