<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value\Transform;

use Atelier\Svg\Value\Angle;
use Atelier\Svg\Value\Transform\SkewXTransform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the SkewXTransform class.
 */
#[CoversClass(SkewXTransform::class)]
final class SkewXTransformTest extends TestCase
{
    public function testConstructorAndGetAngle(): void
    {
        $angle = Angle::fromDegrees(30);
        $transform = new SkewXTransform($angle);

        $this->assertSame($angle, $transform->getAngle());
    }

    public function testToStringPositiveAngle(): void
    {
        $angle = Angle::fromDegrees(45);
        $transform = new SkewXTransform($angle);

        $this->assertEquals('skewX(45)', $transform->toString());
    }

    public function testToStringZeroAngle(): void
    {
        $angle = Angle::fromDegrees(0);
        $transform = new SkewXTransform($angle);

        $this->assertEquals('skewX(0)', $transform->toString());
    }

    public function testToStringNegativeAngle(): void
    {
        $angle = Angle::fromDegrees(-30);
        $transform = new SkewXTransform($angle);

        $this->assertEquals('skewX(-30)', $transform->toString());
    }

    public function testToStringRadians(): void
    {
        $angle = Angle::fromRadians(1.5708); // ~90 degrees
        $transform = new SkewXTransform($angle);

        // Angle toString should handle radians
        $expected = 'skewX('.$angle->__toString().')';
        $this->assertEquals($expected, $transform->toString());
    }
}
