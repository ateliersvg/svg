<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Clipping;

use Atelier\Svg\Element\Clipping\MaskElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaskElement::class)]
final class MaskElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $mask = new MaskElement();
        $this->assertSame('mask', $mask->getTagName());
    }

    public function testSetAndGetX(): void
    {
        $mask = new MaskElement();
        $result = $mask->setX('-10%');

        $this->assertSame($mask, $result);
        $this->assertEquals(Length::parse('-10%'), $mask->getX());
    }

    public function testSetAndGetY(): void
    {
        $mask = new MaskElement();
        $result = $mask->setY('-10%');

        $this->assertSame($mask, $result);
        $this->assertEquals(Length::parse('-10%'), $mask->getY());
    }

    public function testSetAndGetWidth(): void
    {
        $mask = new MaskElement();
        $result = $mask->setWidth('120%');

        $this->assertSame($mask, $result);
        $this->assertEquals(Length::parse('120%'), $mask->getWidth());
    }

    public function testSetAndGetHeight(): void
    {
        $mask = new MaskElement();
        $result = $mask->setHeight('120%');

        $this->assertSame($mask, $result);
        $this->assertEquals(Length::parse('120%'), $mask->getHeight());
    }

    public function testSetAndGetMaskUnits(): void
    {
        $mask = new MaskElement();
        $result = $mask->setMaskUnits('userSpaceOnUse');

        $this->assertSame($mask, $result);
        $this->assertSame('userSpaceOnUse', $mask->getMaskUnits());
    }

    public function testSetAndGetMaskContentUnits(): void
    {
        $mask = new MaskElement();
        $result = $mask->setMaskContentUnits('objectBoundingBox');

        $this->assertSame($mask, $result);
        $this->assertSame('objectBoundingBox', $mask->getMaskContentUnits());
    }

    public function testSetBounds(): void
    {
        $mask = new MaskElement();
        $result = $mask->setBounds(0, 0, 100, 100);

        $this->assertSame($mask, $result);
        $this->assertEquals(Length::parse('0'), $mask->getX());
        $this->assertEquals(Length::parse('0'), $mask->getY());
        $this->assertEquals(Length::parse('100'), $mask->getWidth());
        $this->assertEquals(Length::parse('100'), $mask->getHeight());
    }

    public function testCanContainChildren(): void
    {
        $mask = new MaskElement();
        $circle = new CircleElement();

        $mask->appendChild($circle);

        $this->assertTrue($mask->hasChildren());
        $this->assertSame(1, $mask->getChildCount());
        $this->assertSame($circle, $mask->getChildren()[0]);
    }

    public function testGettersReturnNullWhenNotSet(): void
    {
        $mask = new MaskElement();

        $this->assertNull($mask->getX());
        $this->assertNull($mask->getY());
        $this->assertNull($mask->getWidth());
        $this->assertNull($mask->getHeight());
        $this->assertNull($mask->getMaskUnits());
        $this->assertNull($mask->getMaskContentUnits());
    }
}
