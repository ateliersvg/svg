<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveUnknownsAndDefaultsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveUnknownsAndDefaultsPass::class)]
final class RemoveUnknownsAndDefaultsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveUnknownsAndDefaultsPass();
        $this->assertSame('remove-unknowns-and-defaults', $pass->getName());
    }

    public function testRemovesDefaultFillBlack(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('fill', 'black');
        $rect->setAttribute('width', '100');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass();
        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('fill'));
        $this->assertTrue($rect->hasAttribute('width'));
    }

    public function testRemovesDefaultStrokeNone(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('stroke', 'none');
        $rect->setAttribute('width', '100');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass();
        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('stroke'));
        $this->assertTrue($rect->hasAttribute('width'));
    }

    public function testRemovesDefaultOpacity(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('opacity', '1');
        $rect->setAttribute('fill-opacity', '1');
        $rect->setAttribute('stroke-opacity', '1');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass();
        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('opacity'));
        $this->assertFalse($rect->hasAttribute('fill-opacity'));
        $this->assertFalse($rect->hasAttribute('stroke-opacity'));
    }

    public function testPreservesNonDefaultValues(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('stroke', 'blue');
        $rect->setAttribute('opacity', '0.5');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass();
        $pass->optimize($document);

        $this->assertTrue($rect->hasAttribute('fill'));
        $this->assertSame('red', $rect->getAttribute('fill'));
        $this->assertTrue($rect->hasAttribute('stroke'));
        $this->assertSame('blue', $rect->getAttribute('stroke'));
        $this->assertTrue($rect->hasAttribute('opacity'));
        $this->assertSame('0.5', $rect->getAttribute('opacity'));
    }

    public function testRemovesDefaultStrokeAttributes(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('stroke-width', '1');
        $rect->setAttribute('stroke-linecap', 'butt');
        $rect->setAttribute('stroke-linejoin', 'miter');
        $rect->setAttribute('stroke-miterlimit', '4');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass();
        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('stroke-width'));
        $this->assertFalse($rect->hasAttribute('stroke-linecap'));
        $this->assertFalse($rect->hasAttribute('stroke-linejoin'));
        $this->assertFalse($rect->hasAttribute('stroke-miterlimit'));
    }

    public function testCanDisableRemovingDefaults(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('fill', 'black');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass(removeDefaults: false);
        $pass->optimize($document);

        $this->assertTrue($rect->hasAttribute('fill'));
    }

    public function testRemovesDefaultFillRule(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('fill-rule', 'nonzero');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass();
        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('fill-rule'));
    }

    public function testRemovesDefaultVisibility(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('visibility', 'visible');
        $rect->setAttribute('display', 'inline');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass();
        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('visibility'));
        $this->assertFalse($rect->hasAttribute('display'));
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();
        $pass = new RemoveUnknownsAndDefaultsPass();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testSkipsAttributeWithNullValue(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('fill', 'red');
        $rect->removeAttribute('fill');
        $rect->setAttribute('stroke', 'blue');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass();
        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('fill'));
        $this->assertTrue($rect->hasAttribute('stroke'));
    }

    public function testAttributeNotInDefaultValuesIsPreserved(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('data-custom', 'value');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveUnknownsAndDefaultsPass();
        $pass->optimize($document);

        $this->assertTrue($rect->hasAttribute('data-custom'));
        $this->assertSame('value', $rect->getAttribute('data-custom'));
    }
}
