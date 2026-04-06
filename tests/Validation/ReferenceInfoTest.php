<?php

namespace Atelier\Svg\Tests\Validation;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Validation\ReferenceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReferenceInfo::class)]
final class ReferenceInfoTest extends TestCase
{
    public function testConstructorAndPropertyAccess(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'test-rect');

        $info = new ReferenceInfo(
            element: $element,
            attribute: 'fill',
            referencedId: 'myGradient',
            value: 'url(#myGradient)'
        );

        $this->assertSame($element, $info->element);
        $this->assertEquals('fill', $info->attribute);
        $this->assertEquals('myGradient', $info->referencedId);
        $this->assertEquals('url(#myGradient)', $info->value);
    }

    public function testWithHrefAttribute(): void
    {
        $element = new RectElement();

        $info = new ReferenceInfo(
            element: $element,
            attribute: 'href',
            referencedId: 'mySymbol',
            value: '#mySymbol'
        );

        $this->assertEquals('href', $info->attribute);
        $this->assertEquals('mySymbol', $info->referencedId);
        $this->assertEquals('#mySymbol', $info->value);
    }

    public function testWithStrokeAttribute(): void
    {
        $element = new RectElement();

        $info = new ReferenceInfo(
            element: $element,
            attribute: 'stroke',
            referencedId: 'strokeGrad',
            value: 'url(#strokeGrad)'
        );

        $this->assertEquals('stroke', $info->attribute);
        $this->assertEquals('strokeGrad', $info->referencedId);
    }

    public function testWithFilterAttribute(): void
    {
        $element = new RectElement();

        $info = new ReferenceInfo(
            element: $element,
            attribute: 'filter',
            referencedId: 'blur-filter',
            value: 'url(#blur-filter)'
        );

        $this->assertEquals('filter', $info->attribute);
        $this->assertEquals('blur-filter', $info->referencedId);
    }

    public function testWithClipPathAttribute(): void
    {
        $element = new RectElement();

        $info = new ReferenceInfo(
            element: $element,
            attribute: 'clip-path',
            referencedId: 'clip1',
            value: 'url(#clip1)'
        );

        $this->assertEquals('clip-path', $info->attribute);
        $this->assertEquals('clip1', $info->referencedId);
    }

    public function testReadonlyProperties(): void
    {
        $element = new RectElement();

        $info = new ReferenceInfo(
            element: $element,
            attribute: 'fill',
            referencedId: 'grad',
            value: 'url(#grad)'
        );

        // Verify that we can read properties multiple times
        $this->assertSame($element, $info->element);
        $this->assertSame($element, $info->element);
        $this->assertEquals('fill', $info->attribute);
        $this->assertEquals('fill', $info->attribute);
    }
}
