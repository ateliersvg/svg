<?php

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Geometry\TransformBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Atelier\Svg\Element\AbstractElement::class)]
final class AbstractElementTransformTest extends TestCase
{
    public function testTransform(): void
    {
        $element = new RectElement();

        $helper = $element->transform();

        $this->assertInstanceOf(TransformBuilder::class, $helper);
    }

    public function testGetTranslation(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'translate(10,20)');

        $translation = $element->getTranslation();

        $this->assertEquals(10, $translation[0]);
        $this->assertEquals(20, $translation[1]);
    }

    public function testGetRotation(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'rotate(45)');

        $rotation = $element->getRotation();

        $this->assertEquals(45, round($rotation));
    }

    public function testGetScale(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'scale(2)');

        $scale = $element->getScale();

        $this->assertEquals(2, $scale[0]);
        $this->assertEquals(2, $scale[1]);
    }

    public function testSetTranslation(): void
    {
        $element = new RectElement();

        $result = $element->setTranslation(10, 20);

        $this->assertSame($element, $result);
        $translation = $element->getTranslation();
        $this->assertEquals(10, $translation[0]);
        $this->assertEquals(20, $translation[1]);
    }

    public function testSetRotation(): void
    {
        $element = new RectElement();

        $result = $element->setRotation(45);

        $this->assertSame($element, $result);
        $rotation = $element->getRotation();
        $this->assertEquals(45, round($rotation));
    }

    public function testSetRotationWithCenter(): void
    {
        $element = new RectElement();

        $result = $element->setRotation(45, 50, 50);

        $this->assertSame($element, $result);
        $this->assertStringContainsString('rotate', $element->getAttribute('transform'));
    }

    public function testSetScale(): void
    {
        $element = new RectElement();

        $result = $element->setScale(2);

        $this->assertSame($element, $result);
        $scale = $element->getScale();
        $this->assertEquals(2, $scale[0]);
        $this->assertEquals(2, $scale[1]);
    }

    public function testSetScaleNonUniform(): void
    {
        $element = new RectElement();

        $result = $element->setScale(2, 3);

        $this->assertSame($element, $result);
        $scale = $element->getScale();
        $this->assertEquals(2, round($scale[0]));
        $this->assertEquals(3, round($scale[1]));
    }
}
