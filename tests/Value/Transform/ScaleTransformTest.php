<?php

namespace Atelier\Svg\Tests\Value\Transform;

use Atelier\Svg\Value\Transform\ScaleTransform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScaleTransform::class)]
final class ScaleTransformTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $sx = 2.5;
        $sy = 3.0;
        $transform = new ScaleTransform($sx, $sy);

        $this->assertSame($sx, $transform->getSx());
        $this->assertSame($sy, $transform->getSy());
    }

    public function testToStringDifferentSxSy(): void
    {
        $sx = 2.5;
        $sy = 3.0;
        $transform = new ScaleTransform($sx, $sy);

        $this->assertSame('scale(2.5,3)', $transform->toString());
    }

    public function testToStringEqualSxSy(): void
    {
        $sx = 2.5;
        $sy = 2.5;
        $transform = new ScaleTransform($sx, $sy);

        $this->assertSame('scale(2.5)', $transform->toString());
    }

    public function testToStringZeroSxSy(): void
    {
        $sx = 0.0;
        $sy = 0.0;
        $transform = new ScaleTransform($sx, $sy);

        $this->assertSame('scale(0)', $transform->toString());
    }

    public function testToStringNegativeSxSy(): void
    {
        $sx = -2.5;
        $sy = -3.0;
        $transform = new ScaleTransform($sx, $sy);

        $this->assertSame('scale(-2.5,-3)', $transform->toString());
    }
}
