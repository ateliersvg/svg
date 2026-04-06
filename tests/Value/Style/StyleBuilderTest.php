<?php

namespace Atelier\Svg\Tests\Value\Style;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Value\Style;
use Atelier\Svg\Value\Style\StyleBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StyleBuilder::class)]
final class StyleBuilderTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->set('fill', 'red');

        $this->assertSame($helper, $result);
        $this->assertEquals('red', $helper->get('fill'));
    }

    public function testFillShorthand(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->fill('#3b82f6');

        $this->assertSame($helper, $result);
        $this->assertEquals('#3b82f6', $helper->get('fill'));
    }

    public function testStrokeShorthand(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->stroke('#1e40af');

        $this->assertSame($helper, $result);
        $this->assertEquals('#1e40af', $helper->get('stroke'));
    }

    public function testStrokeWidth(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->strokeWidth(2);

        $this->assertSame($helper, $result);
        $this->assertEquals('2', $helper->get('stroke-width'));
    }

    public function testOpacity(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->opacity(0.8);

        $this->assertSame($helper, $result);
        $this->assertEquals('0.8', $helper->get('opacity'));
    }

    public function testFillOpacity(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->fillOpacity(0.5);

        $this->assertSame($helper, $result);
        $this->assertEquals('0.5', $helper->get('fill-opacity'));
    }

    public function testStrokeOpacity(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->strokeOpacity(0.7);

        $this->assertSame($helper, $result);
        $this->assertEquals('0.7', $helper->get('stroke-opacity'));
    }

    public function testFontFamily(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->fontFamily('Arial, sans-serif');

        $this->assertSame($helper, $result);
        $this->assertEquals('Arial, sans-serif', $helper->get('font-family'));
    }

    public function testFontSize(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->fontSize('16px');

        $this->assertSame($helper, $result);
        $this->assertEquals('16px', $helper->get('font-size'));
    }

    public function testFontWeight(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->fontWeight('bold');

        $this->assertSame($helper, $result);
        $this->assertEquals('bold', $helper->get('font-weight'));
    }

    public function testDisplay(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->display('none');

        $this->assertSame($helper, $result);
        $this->assertEquals('none', $helper->get('display'));
    }

    public function testVisibility(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->visibility('hidden');

        $this->assertSame($helper, $result);
        $this->assertEquals('hidden', $helper->get('visibility'));
    }

    public function testRemove(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $helper->set('fill', 'red');
        $result = $helper->remove('fill');

        $this->assertSame($helper, $result);
        $this->assertNull($helper->get('fill'));
    }

    public function testHas(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $helper->set('fill', 'red');

        $this->assertTrue($helper->has('fill'));
        $this->assertFalse($helper->has('stroke'));
    }

    public function testMergeWithStyle(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $helper->set('fill', 'red');

        $other = Style::fromArray(['stroke' => 'blue', 'opacity' => '0.8']);
        $result = $helper->merge($other);

        $this->assertSame($helper, $result);
        $this->assertEquals('red', $helper->get('fill'));
        $this->assertEquals('blue', $helper->get('stroke'));
        $this->assertEquals('0.8', $helper->get('opacity'));
    }

    public function testMergeWithArray(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $helper->set('fill', 'red');

        $result = $helper->merge(['stroke' => 'blue', 'opacity' => '0.8']);

        $this->assertSame($helper, $result);
        $this->assertEquals('red', $helper->get('fill'));
        $this->assertEquals('blue', $helper->get('stroke'));
        $this->assertEquals('0.8', $helper->get('opacity'));
    }

    public function testClear(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $helper->set('fill', 'red')->set('stroke', 'blue');
        $result = $helper->clear();

        $this->assertSame($helper, $result);
        $this->assertNull($helper->get('fill'));
        $this->assertNull($helper->get('stroke'));
    }

    public function testToArray(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $helper->set('fill', 'red')->set('stroke', 'blue');
        $array = $helper->toArray();

        $this->assertEquals('red', $array['fill']);
        $this->assertEquals('blue', $array['stroke']);
    }

    public function testGetStyle(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $helper->set('fill', 'red');
        $style = $helper->getStyle();

        $this->assertInstanceOf(Style::class, $style);
        $this->assertEquals('red', $style->get('fill'));
    }

    public function testApply(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $result = $helper->set('fill', 'red')->set('stroke', 'blue')->apply();

        $this->assertSame($element, $result);
        $style = $element->getStyle();
        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testApplyRemovesAttributeWhenEmpty(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red');

        $helper = new StyleBuilder($element);
        $helper->clear()->apply();

        $this->assertNull($element->getAttribute('style'));
    }

    public function testToString(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $helper->set('fill', 'red')->set('stroke', 'blue');
        $string = $helper->toString();

        $this->assertStringContainsString('fill: red', $string);
        $this->assertStringContainsString('stroke: blue', $string);
    }

    public function testMagicToString(): void
    {
        $element = new RectElement();
        $helper = new StyleBuilder($element);

        $helper->set('fill', 'red');
        $string = (string) $helper;

        $this->assertStringContainsString('fill: red', $string);
    }

    public function testChaining(): void
    {
        $element = new RectElement();

        $result = $element->style()
            ->fill('#3b82f6')
            ->stroke('#1e40af')
            ->strokeWidth(2)
            ->opacity(0.8)
            ->apply();

        $this->assertSame($element, $result);
        $style = $element->getStyle();
        $this->assertEquals('#3b82f6', $style->get('fill'));
        $this->assertEquals('#1e40af', $style->get('stroke'));
        $this->assertEquals('2', $style->get('stroke-width'));
        $this->assertEquals('0.8', $style->get('opacity'));
    }
}
