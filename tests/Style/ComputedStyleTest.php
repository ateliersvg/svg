<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Style;

use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Style\ComputedStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComputedStyle::class)]
final class ComputedStyleTest extends TestCase
{
    public function testResolveOwnAttribute(): void
    {
        $circle = new CircleElement();
        $circle->setAttribute('fill', 'red');

        $style = ComputedStyle::of($circle);

        $this->assertSame('red', $style->get('fill'));
    }

    public function testInheritFromParent(): void
    {
        $group = new GroupElement();
        $group->setAttribute('fill', 'blue');

        $circle = new CircleElement();
        $group->appendChild($circle);

        $style = ComputedStyle::of($circle);

        $this->assertSame('blue', $style->get('fill'));
    }

    public function testOwnAttributeOverridesParent(): void
    {
        $group = new GroupElement();
        $group->setAttribute('fill', 'blue');

        $circle = new CircleElement();
        $circle->setAttribute('fill', 'red');
        $group->appendChild($circle);

        $style = ComputedStyle::of($circle);

        $this->assertSame('red', $style->get('fill'));
    }

    public function testInheritFromGrandparent(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('fill', 'green');

        $group = new GroupElement();
        $svg->appendChild($group);

        $circle = new CircleElement();
        $group->appendChild($circle);

        $style = ComputedStyle::of($circle);

        $this->assertSame('green', $style->get('fill'));
    }

    public function testNonInheritableAttributeNotInherited(): void
    {
        $group = new GroupElement();
        $group->setAttribute('x', '10');

        $circle = new CircleElement();
        $group->appendChild($circle);

        $style = ComputedStyle::of($circle);

        $this->assertNull($style->get('x'));
    }

    public function testReturnsNullForUnsetAttribute(): void
    {
        $circle = new CircleElement();

        $style = ComputedStyle::of($circle);

        $this->assertNull($style->get('fill'));
    }

    public function testAllReturnsAllResolvedProperties(): void
    {
        $group = new GroupElement();
        $group->setAttribute('fill', 'blue');
        $group->setAttribute('stroke', 'black');

        $circle = new CircleElement();
        $circle->setAttribute('fill', 'red');
        $group->appendChild($circle);

        $style = ComputedStyle::of($circle);
        $all = $style->all();

        $this->assertSame('red', $all['fill']);
        $this->assertSame('black', $all['stroke']);
    }

    public function testIsInheritable(): void
    {
        $this->assertTrue(ComputedStyle::isInheritable('fill'));
        $this->assertTrue(ComputedStyle::isInheritable('stroke'));
        $this->assertTrue(ComputedStyle::isInheritable('font-size'));
        $this->assertFalse(ComputedStyle::isInheritable('x'));
        $this->assertFalse(ComputedStyle::isInheritable('width'));
    }

    public function testGetInheritableAttributes(): void
    {
        $attrs = ComputedStyle::getInheritableAttributes();

        $this->assertContains('fill', $attrs);
        $this->assertContains('stroke', $attrs);
        $this->assertContains('font-family', $attrs);
    }

    public function testMultipleInheritableAttributesFromDifferentAncestors(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('font-family', 'Arial');

        $group = new GroupElement();
        $group->setAttribute('fill', 'blue');
        $svg->appendChild($group);

        $rect = new RectElement();
        $group->appendChild($rect);

        $style = ComputedStyle::of($rect);

        $this->assertSame('Arial', $style->get('font-family'));
        $this->assertSame('blue', $style->get('fill'));
    }

    public function testOwnNonInheritableAttributeIsPreserved(): void
    {
        $rect = new RectElement();
        $rect->setAttribute('x', '10');
        $rect->setAttribute('width', '100');

        $style = ComputedStyle::of($rect);

        $this->assertSame('10', $style->get('x'));
        $this->assertSame('100', $style->get('width'));
    }

    public function testAllInheritableAttributesCovered(): void
    {
        $attrs = ComputedStyle::getInheritableAttributes();

        $this->assertContains('clip-rule', $attrs);
        $this->assertContains('color', $attrs);
        $this->assertContains('cursor', $attrs);
        $this->assertContains('direction', $attrs);
        $this->assertContains('dominant-baseline', $attrs);
        $this->assertContains('fill-opacity', $attrs);
        $this->assertContains('fill-rule', $attrs);
        $this->assertContains('font-style', $attrs);
        $this->assertContains('font-weight', $attrs);
        $this->assertContains('letter-spacing', $attrs);
        $this->assertContains('marker-end', $attrs);
        $this->assertContains('opacity', $attrs);
        $this->assertContains('paint-order', $attrs);
        $this->assertContains('pointer-events', $attrs);
        $this->assertContains('shape-rendering', $attrs);
        $this->assertContains('stroke-dasharray', $attrs);
        $this->assertContains('stroke-linecap', $attrs);
        $this->assertContains('stroke-width', $attrs);
        $this->assertContains('text-anchor', $attrs);
        $this->assertContains('text-rendering', $attrs);
        $this->assertContains('visibility', $attrs);
        $this->assertContains('writing-mode', $attrs);
    }

    public function testClosestAncestorWins(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('stroke', 'red');

        $group = new GroupElement();
        $group->setAttribute('stroke', 'green');
        $svg->appendChild($group);

        $circle = new CircleElement();
        $group->appendChild($circle);

        $style = ComputedStyle::of($circle);

        $this->assertSame('green', $style->get('stroke'));
    }

    public function testElementWithNoParent(): void
    {
        $circle = new CircleElement();
        $circle->setAttribute('fill', 'red');
        $circle->setAttribute('cx', '50');

        $style = ComputedStyle::of($circle);

        $this->assertSame('red', $style->get('fill'));
        $this->assertSame('50', $style->get('cx'));
        $this->assertNull($style->get('stroke'));
    }

    public function testDeeplyNestedInheritance(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('fill', 'root-fill');

        $g1 = new GroupElement();
        $svg->appendChild($g1);
        $g2 = new GroupElement();
        $g1->appendChild($g2);
        $g3 = new GroupElement();
        $g2->appendChild($g3);

        $rect = new RectElement();
        $g3->appendChild($rect);

        $style = ComputedStyle::of($rect);

        $this->assertSame('root-fill', $style->get('fill'));
    }

    public function testMixedInheritableAndNonInheritable(): void
    {
        $group = new GroupElement();
        $group->setAttribute('fill', 'blue');
        $group->setAttribute('transform', 'rotate(45)');

        $circle = new CircleElement();
        $circle->setAttribute('r', '50');
        $group->appendChild($circle);

        $style = ComputedStyle::of($circle);

        $this->assertSame('blue', $style->get('fill'));
        $this->assertSame('50', $style->get('r'));
        $this->assertNull($style->get('transform'));
    }
}
