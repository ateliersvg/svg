<?php

namespace Atelier\Svg\Value\Transform;

use Atelier\Svg\Value\Angle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SkewYTransform::class)]
final class SkewYTransformTest extends TestCase
{
    public function testGetAngle(): void
    {
        $angle = Angle::fromDegrees(45);
        $transform = new SkewYTransform($angle);
        $this->assertSame($angle, $transform->getAngle());
    }

    public function testToString(): void
    {
        $angle = Angle::fromDegrees(30);
        $transform = new SkewYTransform($angle);
        // SVG transform skewY() takes angles in degrees without unit
        $this->assertSame('skewY(30)', $transform->toString());

        $angle = Angle::fromDegrees(0);
        $transform = new SkewYTransform($angle);
        $this->assertSame('skewY(0)', $transform->toString());

        $angle = Angle::fromDegrees(-15);
        $transform = new SkewYTransform($angle);
        $this->assertSame('skewY(-15)', $transform->toString());
    }
}
