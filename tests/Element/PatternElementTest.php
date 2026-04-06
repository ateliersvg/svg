<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\Gradient\PatternElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\Viewbox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PatternElement::class)]
final class PatternElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $pattern = new PatternElement();

        $this->assertSame('pattern', $pattern->getTagName());
    }

    public function testSetAndGetX(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setX('10');

        $this->assertSame($pattern, $result);
        $this->assertEquals(Length::parse('10'), $pattern->getX());
    }

    public function testSetAndGetY(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setY('20');

        $this->assertSame($pattern, $result);
        $this->assertEquals(Length::parse('20'), $pattern->getY());
    }

    public function testSetAndGetWidth(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setWidth('100');

        $this->assertSame($pattern, $result);
        $this->assertEquals(Length::parse('100'), $pattern->getWidth());
    }

    public function testSetAndGetHeight(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setHeight('200');

        $this->assertSame($pattern, $result);
        $this->assertEquals(Length::parse('200'), $pattern->getHeight());
    }

    public function testSetAndGetPatternUnits(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setPatternUnits('userSpaceOnUse');

        $this->assertSame($pattern, $result);
        $this->assertSame('userSpaceOnUse', $pattern->getPatternUnits());
    }

    public function testSetAndGetPatternContentUnits(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setPatternContentUnits('objectBoundingBox');

        $this->assertSame($pattern, $result);
        $this->assertSame('objectBoundingBox', $pattern->getPatternContentUnits());
    }

    public function testSetAndGetPatternTransform(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setPatternTransform('rotate(45)');

        $this->assertSame($pattern, $result);
        $this->assertSame('rotate(45)', $pattern->getPatternTransform());
    }

    public function testSetAndGetViewBox(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setViewBox('0 0 100 100');

        $this->assertSame($pattern, $result);
        $this->assertEquals(Viewbox::parse('0 0 100 100'), $pattern->getViewBox());
    }

    public function testSetAndGetPreserveAspectRatio(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setPreserveAspectRatio('xMidYMid meet');

        $this->assertSame($pattern, $result);
        $this->assertSame('xMidYMid meet', $pattern->getPreserveAspectRatio());
    }

    public function testSetBounds(): void
    {
        $pattern = new PatternElement();
        $result = $pattern->setBounds(10, 20, 100, 200);

        $this->assertSame($pattern, $result);
        $this->assertEquals(Length::parse('10'), $pattern->getX());
        $this->assertEquals(Length::parse('20'), $pattern->getY());
        $this->assertEquals(Length::parse('100'), $pattern->getWidth());
        $this->assertEquals(Length::parse('200'), $pattern->getHeight());
    }

    public function testCanContainChildren(): void
    {
        $pattern = new PatternElement();
        $rect = new RectElement();

        $pattern->appendChild($rect);

        $this->assertTrue($pattern->hasChildren());
        $this->assertSame(1, $pattern->getChildCount());
        $this->assertSame($rect, $pattern->getChildren()[0]);
    }

    public function testGettersReturnNullWhenNotSet(): void
    {
        $pattern = new PatternElement();

        $this->assertNull($pattern->getX());
        $this->assertNull($pattern->getY());
        $this->assertNull($pattern->getWidth());
        $this->assertNull($pattern->getHeight());
        $this->assertNull($pattern->getPatternUnits());
        $this->assertNull($pattern->getPatternContentUnits());
        $this->assertNull($pattern->getPatternTransform());
        $this->assertNull($pattern->getViewBox());
        $this->assertNull($pattern->getPreserveAspectRatio());
    }

    public function testWorksWithNumericValues(): void
    {
        $pattern = new PatternElement();
        $pattern->setX(5);
        $pattern->setY(10);
        $pattern->setWidth(50);
        $pattern->setHeight(75);

        $this->assertEquals(Length::parse('5'), $pattern->getX());
        $this->assertEquals(Length::parse('10'), $pattern->getY());
        $this->assertEquals(Length::parse('50'), $pattern->getWidth());
        $this->assertEquals(Length::parse('75'), $pattern->getHeight());
    }
}
