<?php

namespace Atelier\Svg\Tests\Validation;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Validation\BrokenReference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BrokenReference::class)]
final class BrokenReferenceTest extends TestCase
{
    public function testConstructorAndPropertyAccess(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'test-rect');

        $brokenRef = new BrokenReference(
            referencedId: 'missing-gradient',
            referencingElement: $element,
            attribute: 'fill',
            value: 'url(#missing-gradient)'
        );

        $this->assertEquals('missing-gradient', $brokenRef->referencedId);
        $this->assertSame($element, $brokenRef->referencingElement);
        $this->assertEquals('fill', $brokenRef->attribute);
        $this->assertEquals('url(#missing-gradient)', $brokenRef->value);
    }

    public function testGetDescriptionWithElementId(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'my-rectangle');

        $brokenRef = new BrokenReference(
            referencedId: 'missing-gradient',
            referencingElement: $element,
            attribute: 'fill',
            value: 'url(#missing-gradient)'
        );

        $expected = "Reference to '#missing-gradient' in <rect id=\"my-rectangle\"> attribute 'fill' not found";
        $this->assertEquals($expected, $brokenRef->getDescription());
    }

    public function testGetDescriptionWithoutElementId(): void
    {
        $element = new RectElement();

        $brokenRef = new BrokenReference(
            referencedId: 'missing-filter',
            referencingElement: $element,
            attribute: 'filter',
            value: 'url(#missing-filter)'
        );

        $expected = "Reference to '#missing-filter' in <rect> attribute 'filter' not found";
        $this->assertEquals($expected, $brokenRef->getDescription());
    }

    public function testWithStrokeAttribute(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'stroked-rect');

        $brokenRef = new BrokenReference(
            referencedId: 'missing-stroke',
            referencingElement: $element,
            attribute: 'stroke',
            value: 'url(#missing-stroke)'
        );

        $this->assertEquals('stroke', $brokenRef->attribute);
        $this->assertEquals('missing-stroke', $brokenRef->referencedId);
        $this->assertStringContainsString('stroke', $brokenRef->getDescription());
    }

    public function testWithHrefAttribute(): void
    {
        $element = new RectElement();

        $brokenRef = new BrokenReference(
            referencedId: 'missing-symbol',
            referencingElement: $element,
            attribute: 'href',
            value: '#missing-symbol'
        );

        $this->assertEquals('href', $brokenRef->attribute);
        $this->assertEquals('missing-symbol', $brokenRef->referencedId);
        $this->assertEquals('#missing-symbol', $brokenRef->value);
    }

    public function testWithClipPathAttribute(): void
    {
        $element = new RectElement();

        $brokenRef = new BrokenReference(
            referencedId: 'missing-clip',
            referencingElement: $element,
            attribute: 'clip-path',
            value: 'url(#missing-clip)'
        );

        $this->assertEquals('clip-path', $brokenRef->attribute);
        $this->assertEquals('missing-clip', $brokenRef->referencedId);
        $this->assertStringContainsString('clip-path', $brokenRef->getDescription());
    }

    public function testWithMaskAttribute(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'masked-element');

        $brokenRef = new BrokenReference(
            referencedId: 'missing-mask',
            referencingElement: $element,
            attribute: 'mask',
            value: 'url(#missing-mask)'
        );

        $this->assertEquals('mask', $brokenRef->attribute);
        $this->assertEquals('missing-mask', $brokenRef->referencedId);
    }

    public function testReadonlyProperties(): void
    {
        $element = new RectElement();

        $brokenRef = new BrokenReference(
            referencedId: 'missing-ref',
            referencingElement: $element,
            attribute: 'fill',
            value: 'url(#missing-ref)'
        );

        $this->assertSame($element, $brokenRef->referencingElement);
        $this->assertSame($element, $brokenRef->referencingElement);
        $this->assertEquals('fill', $brokenRef->attribute);
        $this->assertEquals('fill', $brokenRef->attribute);
    }

    public function testGetDescriptionFormat(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'test-id');

        $brokenRef = new BrokenReference(
            referencedId: 'target-id',
            referencingElement: $element,
            attribute: 'fill',
            value: 'url(#target-id)'
        );

        $description = $brokenRef->getDescription();
        $this->assertStringStartsWith("Reference to '#target-id'", $description);
        $this->assertStringContainsString('<rect id="test-id">', $description);
        $this->assertStringContainsString("attribute 'fill'", $description);
        $this->assertStringEndsWith('not found', $description);
    }

    public function testGetDescriptionFormatWithoutId(): void
    {
        $element = new RectElement();

        $brokenRef = new BrokenReference(
            referencedId: 'target-id',
            referencingElement: $element,
            attribute: 'stroke',
            value: 'url(#target-id)'
        );

        $description = $brokenRef->getDescription();
        $this->assertStringStartsWith("Reference to '#target-id'", $description);
        $this->assertStringContainsString('<rect>', $description);
        $this->assertStringNotContainsString('id=', $description);
        $this->assertStringContainsString("attribute 'stroke'", $description);
        $this->assertStringEndsWith('not found', $description);
    }
}
