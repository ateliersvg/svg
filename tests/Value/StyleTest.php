<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Value\Style;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Style::class)]
final class StyleTest extends TestCase
{
    public function testParseEmpty(): void
    {
        $style = Style::parse('');

        $this->assertTrue($style->isEmpty());
    }

    public function testParseNull(): void
    {
        $style = Style::parse(null);

        $this->assertTrue($style->isEmpty());
    }

    public function testParseSimple(): void
    {
        $style = Style::parse('fill: red; stroke: blue');

        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testParseWithSpaces(): void
    {
        $style = Style::parse('  fill : red  ;  stroke : blue  ');

        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testParseComplex(): void
    {
        $style = Style::parse('fill: #3b82f6; stroke-width: 2px; opacity: 0.5');

        $this->assertEquals('#3b82f6', $style->get('fill'));
        $this->assertEquals('2px', $style->get('stroke-width'));
        $this->assertEquals('0.5', $style->get('opacity'));
    }

    public function testFromArray(): void
    {
        $style = Style::fromArray([
            'fill' => 'red',
            'stroke' => 'blue',
        ]);

        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testSet(): void
    {
        $style = Style::fromArray([]);
        $style->set('fill', 'red');

        $this->assertEquals('red', $style->get('fill'));
    }

    public function testRemove(): void
    {
        $style = Style::fromArray(['fill' => 'red']);
        $style->remove('fill');

        $this->assertNull($style->get('fill'));
    }

    public function testHas(): void
    {
        $style = Style::fromArray(['fill' => 'red']);

        $this->assertTrue($style->has('fill'));
        $this->assertFalse($style->has('stroke'));
    }

    public function testGetAll(): void
    {
        $style = Style::fromArray([
            'fill' => 'red',
            'stroke' => 'blue',
        ]);

        $all = $style->getAll();

        $this->assertCount(2, $all);
        $this->assertEquals('red', $all['fill']);
        $this->assertEquals('blue', $all['stroke']);
    }

    public function testMerge(): void
    {
        $style1 = Style::fromArray(['fill' => 'red']);
        $style2 = Style::fromArray(['stroke' => 'blue']);

        $style1->merge($style2);

        $this->assertEquals('red', $style1->get('fill'));
        $this->assertEquals('blue', $style1->get('stroke'));
    }

    public function testMergeOverride(): void
    {
        $style1 = Style::fromArray(['fill' => 'red']);
        $style2 = Style::fromArray(['fill' => 'blue']);

        $style1->merge($style2);

        $this->assertEquals('blue', $style1->get('fill'));
    }

    public function testClear(): void
    {
        $style = Style::fromArray(['fill' => 'red', 'stroke' => 'blue']);
        $style->clear();

        $this->assertTrue($style->isEmpty());
    }

    public function testIsEmpty(): void
    {
        $style = Style::fromArray([]);
        $this->assertTrue($style->isEmpty());

        $style->set('fill', 'red');
        $this->assertFalse($style->isEmpty());
    }

    public function testToString(): void
    {
        $style = Style::fromArray([
            'fill' => 'red',
            'stroke' => 'blue',
        ]);

        $string = $style->toString();

        $this->assertStringContainsString('fill: red', $string);
        $this->assertStringContainsString('stroke: blue', $string);
    }

    public function testMagicToString(): void
    {
        $style = Style::fromArray(['fill' => 'red']);

        $string = (string) $style;

        $this->assertStringContainsString('fill: red', $string);
    }

    public function testCopy(): void
    {
        $style1 = Style::fromArray(['fill' => 'red']);
        $style2 = $style1->copy();

        $style2->set('fill', 'blue');

        $this->assertEquals('red', $style1->get('fill'));
        $this->assertEquals('blue', $style2->get('fill'));
    }

    public function testToArray(): void
    {
        $style = Style::fromArray([
            'fill' => 'red',
            'stroke' => 'blue',
        ]);

        $array = $style->toArray();

        $this->assertCount(2, $array);
        $this->assertEquals('red', $array['fill']);
        $this->assertEquals('blue', $array['stroke']);
    }

    public function testToArrayIsSameAsGetAll(): void
    {
        $style = Style::fromArray([
            'fill' => 'red',
            'stroke' => 'blue',
            'opacity' => '0.8',
        ]);

        $this->assertEquals($style->getAll(), $style->toArray());
    }

    public function testParseWithTrailingSemicolon(): void
    {
        $style = Style::parse('fill: red;');

        $this->assertEquals('red', $style->get('fill'));
        $this->assertCount(1, $style->getAll());
    }

    public function testToStringEmptyStyle(): void
    {
        $style = Style::fromArray([]);

        $this->assertSame('', $style->toString());
    }
}
