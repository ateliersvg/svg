<?php

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Value\Style;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Atelier\Svg\Element\AbstractElement::class)]
final class AbstractElementStyleTest extends TestCase
{
    public function testGetStyle(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red; stroke: blue');

        $style = $element->getStyle();

        $this->assertInstanceOf(Style::class, $style);
        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testGetStyleEmpty(): void
    {
        $element = new RectElement();

        $style = $element->getStyle();

        $this->assertInstanceOf(Style::class, $style);
        $this->assertTrue($style->isEmpty());
    }

    public function testSetStyles(): void
    {
        $element = new RectElement();

        $result = $element->setStyles([
            'fill' => 'red',
            'stroke' => 'blue',
        ]);

        $this->assertSame($element, $result);
        $style = $element->getStyle();
        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testSetStyle(): void
    {
        $element = new RectElement();

        $result = $element->setStyle('fill', 'red');

        $this->assertSame($element, $result);
        $style = $element->getStyle();
        $this->assertEquals('red', $style->get('fill'));
    }

    public function testGetStyleProperty(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red');

        $value = $element->getStyleProperty('fill');

        $this->assertEquals('red', $value);
    }

    public function testGetStylePropertyFromAttribute(): void
    {
        $element = new RectElement();
        $element->setAttribute('fill', 'red');

        $value = $element->getStyleProperty('fill');

        $this->assertEquals('red', $value);
    }

    public function testGetStylePropertyNotFound(): void
    {
        $element = new RectElement();

        $value = $element->getStyleProperty('fill');

        $this->assertNull($value);
    }

    public function testRemoveStyle(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red; stroke: blue');

        $result = $element->removeStyle('fill');

        $this->assertSame($element, $result);
        $style = $element->getStyle();
        $this->assertNull($style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testInlineStyles(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red; stroke: blue');

        $result = $element->inlineStyles();

        $this->assertSame($element, $result);
        // Styles should be converted to attributes
        $this->assertEquals('red', $element->getAttribute('fill'));
        $this->assertEquals('blue', $element->getAttribute('stroke'));
    }

    public function testExtractStyles(): void
    {
        $element = new RectElement();
        $element->setAttribute('fill', 'red');
        $element->setAttribute('stroke', 'blue');

        $result = $element->extractStyles();

        $this->assertSame($element, $result);
        // Attributes should be in inline styles
        $style = $element->getStyle();
        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testGetStyles(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red; stroke: blue');
        $element->setAttribute('opacity', '0.8');

        $styles = $element->getStyles();

        $this->assertIsArray($styles);
        $this->assertEquals('red', $styles['fill']);
        $this->assertEquals('blue', $styles['stroke']);
        $this->assertEquals('0.8', $styles['opacity']);
    }

    public function testGetStylesEmpty(): void
    {
        $element = new RectElement();

        $styles = $element->getStyles();

        $this->assertIsArray($styles);
        $this->assertEmpty($styles);
    }

    public function testHasStyle(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red');
        $element->setAttribute('stroke', 'blue');

        $this->assertTrue($element->hasStyle('fill'));
        $this->assertTrue($element->hasStyle('stroke'));
        $this->assertFalse($element->hasStyle('opacity'));
    }

    public function testStyleBuilder(): void
    {
        $element = new RectElement();

        $helper = $element->style();

        $this->assertInstanceOf(Style\StyleBuilder::class, $helper);
    }

    public function testStyleBuilderChaining(): void
    {
        $element = new RectElement();

        $result = $element->style()
            ->fill('red')
            ->stroke('blue')
            ->opacity(0.8)
            ->apply();

        $this->assertSame($element, $result);
        $style = $element->getStyle();
        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
        $this->assertEquals('0.8', $style->get('opacity'));
    }
}
