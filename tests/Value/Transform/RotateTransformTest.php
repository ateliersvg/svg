<?php

namespace Atelier\Svg\Tests\Value\Transform;

use Atelier\Svg\Value\Angle;
use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\Transform\RotateTransform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RotateTransform::class)]
final class RotateTransformTest extends TestCase
{
    public function testGetAngle(): void
    {
        $angle = Angle::fromDegrees(45);
        $transform = new RotateTransform($angle);
        $this->assertSame($angle, $transform->getAngle());
    }

    public function testGetCx(): void
    {
        $cx = Length::parse('10px');
        $transform = new RotateTransform(Angle::fromDegrees(45), $cx);
        $this->assertSame($cx, $transform->getCx());

        $transform = new RotateTransform(Angle::fromDegrees(45));
        $this->assertNull($transform->getCx());
    }

    public function testGetCy(): void
    {
        $cy = Length::parse('20px');
        $transform = new RotateTransform(Angle::fromDegrees(45), null, $cy);
        $this->assertSame($cy, $transform->getCy());

        $transform = new RotateTransform(Angle::fromDegrees(45));
        $this->assertNull($transform->getCy());
    }

    public function testHasCenter(): void
    {
        $transform = new RotateTransform(Angle::fromDegrees(45), Length::parse('10px'), Length::parse('20px'));
        $this->assertTrue($transform->hasCenter());

        $transform = new RotateTransform(Angle::fromDegrees(45));
        $this->assertFalse($transform->hasCenter());

        $transform = new RotateTransform(Angle::fromDegrees(45), Length::parse('10px'));
        $this->assertFalse($transform->hasCenter());

        $transform = new RotateTransform(Angle::fromDegrees(45), null, Length::parse('20px'));
        $this->assertFalse($transform->hasCenter());
    }

    public function testToString(): void
    {
        $transform = new RotateTransform(Angle::fromDegrees(45));
        // SVG transform rotate() takes angles in degrees without unit
        $this->assertEquals('rotate(45)', $transform->toString());

        $transform = new RotateTransform(Angle::fromDegrees(45), Length::parse('10px'), Length::parse('20px'));
        $this->assertEquals('rotate(45,10px,20px)', $transform->toString());
    }
}
