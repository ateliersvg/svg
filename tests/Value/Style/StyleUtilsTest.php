<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value\Style;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Value\Style;
use Atelier\Svg\Value\Style\StyleUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StyleUtils::class)]
final class StyleUtilsTest extends TestCase
{
    public function testAttributesToStyles(): void
    {
        $element = new RectElement();
        $element->setAttribute('fill', 'red');
        $element->setAttribute('stroke', 'blue');
        $element->setAttribute('style', 'opacity: 0.5');

        $style = StyleUtils::attributesToStyles($element);

        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
        $this->assertEquals('0.5', $style->get('opacity'));
    }

    public function testStylesToAttributes(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red; stroke: blue');

        StyleUtils::stylesToAttributes($element);

        $this->assertEquals('red', $element->getAttribute('fill'));
        $this->assertEquals('blue', $element->getAttribute('stroke'));
    }

    public function testMergeStyles(): void
    {
        $style1 = Style::fromArray(['fill' => 'red']);
        $style2 = Style::fromArray(['stroke' => 'blue']);
        $style3 = Style::fromArray(['opacity' => '0.5']);

        $merged = StyleUtils::mergeStyles($style1, $style2, $style3);

        $this->assertEquals('red', $merged->get('fill'));
        $this->assertEquals('blue', $merged->get('stroke'));
        $this->assertEquals('0.5', $merged->get('opacity'));
    }

    public function testGetAllStyles(): void
    {
        $element = new RectElement();
        $element->setAttribute('fill', 'red');
        $element->setAttribute('style', 'stroke: blue');

        $style = StyleUtils::getAllStyles($element);

        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testSetStyles(): void
    {
        $element = new RectElement();

        StyleUtils::setStyles($element, [
            'fill' => 'red',
            'stroke' => 'blue',
        ]);

        $style = Style::parse($element->getAttribute('style'));
        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testGetStyle(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red');
        $element->setAttribute('stroke', 'blue');

        // Should get from inline style first
        $this->assertEquals('red', StyleUtils::getStyle($element, 'fill'));

        // Should fall back to attribute
        $this->assertEquals('blue', StyleUtils::getStyle($element, 'stroke'));
    }

    public function testRemoveStyle(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red; stroke: blue');

        StyleUtils::removeStyle($element, 'fill');

        $style = Style::parse($element->getAttribute('style'));
        $this->assertNull($style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testNormalizeColor(): void
    {
        $this->assertEquals('#000000', StyleUtils::normalizeColor('black'));
        $this->assertEquals('#ffffff', StyleUtils::normalizeColor('white'));
        $this->assertEquals('#ff0000', StyleUtils::normalizeColor('red'));

        // 3-digit to 6-digit hex
        $this->assertEquals('#ff00ff', StyleUtils::normalizeColor('#f0f'));
    }

    public function testMinifyColor(): void
    {
        // 6-digit to 3-digit
        $this->assertEquals('#fff', StyleUtils::minifyColor('#ffffff'));
        $this->assertEquals('#000', StyleUtils::minifyColor('#000000'));

        // Should not minify if can't be shortened
        $this->assertEquals('#123456', StyleUtils::minifyColor('#123456'));
    }

    public function testStylesToAttributesKeepsNonPresentationStyles(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red; cursor: pointer');

        StyleUtils::stylesToAttributes($element);

        $this->assertEquals('red', $element->getAttribute('fill'));
        $style = Style::parse($element->getAttribute('style'));
        $this->assertEquals('pointer', $style->get('cursor'));
    }

    public function testGetStyleReturnsNullForNonPresentationProperty(): void
    {
        $element = new RectElement();

        $this->assertNull(StyleUtils::getStyle($element, 'cursor'));
    }

    public function testRemoveStyleRemovesAttributeWhenStyleBecomesEmpty(): void
    {
        $element = new RectElement();
        $element->setAttribute('style', 'fill: red');

        StyleUtils::removeStyle($element, 'fill');

        $this->assertNull($element->getAttribute('style'));
    }

    public function testNormalizeColorReturnsNoneForNull(): void
    {
        $this->assertEquals('none', StyleUtils::normalizeColor(null));
    }

    public function testNormalizeColorPassthroughForAlreadyNormalizedColor(): void
    {
        $this->assertEquals('#ff0000', StyleUtils::normalizeColor('#ff0000'));
    }

    public function testMinifyColorReturnsNoneForNull(): void
    {
        $this->assertEquals('none', StyleUtils::minifyColor(null));
    }
}
