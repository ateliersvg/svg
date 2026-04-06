<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value\Transform;

use Atelier\Svg\Value\Transform;
use Atelier\Svg\Value\Transform\MatrixTransform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MatrixTransform::class)]
final class MatrixTransformTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $transform = new MatrixTransform(1.0, 2.0, 3.0, 4.0, 5.0, 6.0);

        $this->assertSame(1.0, $transform->getA());
        $this->assertSame(2.0, $transform->getB());
        $this->assertSame(3.0, $transform->getC());
        $this->assertSame(4.0, $transform->getD());
        $this->assertSame(5.0, $transform->getE());
        $this->assertSame(6.0, $transform->getF());
    }

    public function testImplementsTransformInterface(): void
    {
        $transform = new MatrixTransform(1, 0, 0, 1, 0, 0);

        $this->assertInstanceOf(Transform::class, $transform);
    }

    public function testToStringIdentityMatrix(): void
    {
        $transform = new MatrixTransform(1, 0, 0, 1, 0, 0);

        $this->assertSame('matrix(1,0,0,1,0,0)', $transform->toString());
    }

    public function testToStringWithValues(): void
    {
        $transform = new MatrixTransform(1.5, 2.0, 3.0, 4.5, 10.0, 20.0);

        $this->assertSame('matrix(1.5,2,3,4.5,10,20)', $transform->toString());
    }

    public function testToStringWithNegativeValues(): void
    {
        $transform = new MatrixTransform(-1, 0, 0, -1, -10, -20);

        $this->assertSame('matrix(-1,0,0,-1,-10,-20)', $transform->toString());
    }

    public function testToStringWithZeroValues(): void
    {
        $transform = new MatrixTransform(0, 0, 0, 0, 0, 0);

        $this->assertSame('matrix(0,0,0,0,0,0)', $transform->toString());
    }

    public function testGettersReturnExactConstructorValues(): void
    {
        $transform = new MatrixTransform(0.1, 0.2, 0.3, 0.4, 0.5, 0.6);

        $this->assertEqualsWithDelta(0.1, $transform->getA(), 1e-10);
        $this->assertEqualsWithDelta(0.2, $transform->getB(), 1e-10);
        $this->assertEqualsWithDelta(0.3, $transform->getC(), 1e-10);
        $this->assertEqualsWithDelta(0.4, $transform->getD(), 1e-10);
        $this->assertEqualsWithDelta(0.5, $transform->getE(), 1e-10);
        $this->assertEqualsWithDelta(0.6, $transform->getF(), 1e-10);
    }
}
