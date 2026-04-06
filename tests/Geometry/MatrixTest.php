<?php

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Geometry\BoundingBox;
use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Matrix::class)]
final class MatrixTest extends TestCase
{
    public function testMultiply(): void
    {
        $m1 = new Matrix(1, 0, 0, 1, 10, 20);
        $m2 = new Matrix(1, 0, 0, 1, 5, 10);

        $result = $m1->multiply($m2);

        $this->assertEquals(15, $result->e);
        $this->assertEquals(30, $result->f);
    }

    public function testTransform(): void
    {
        $matrix = new Matrix(1, 0, 0, 1, 10, 20);
        $point = new Point(5, 5);

        $result = $matrix->transform($point);

        $this->assertEquals(15, $result->x);
        $this->assertEquals(25, $result->y);
    }

    public function testDeterminant(): void
    {
        $matrix = new Matrix(2, 0, 0, 2, 0, 0);

        $det = $matrix->determinant();

        $this->assertEquals(4, $det);
    }

    public function testInverse(): void
    {
        $matrix = new Matrix(2, 0, 0, 2, 0, 0);

        $inverse = $matrix->inverse();

        $this->assertEquals(0.5, $inverse->a);
        $this->assertEquals(0.5, $inverse->d);
    }

    public function testInverseSingularMatrix(): void
    {
        $matrix = new Matrix(0, 0, 0, 0, 0, 0);

        $this->expectException(\RuntimeException::class);
        $matrix->inverse();
    }

    public function testTransformBBox(): void
    {
        $matrix = new Matrix(1, 0, 0, 1, 10, 20);
        $bbox = new BoundingBox(0, 0, 100, 50);

        $transformed = $matrix->transformBBox($bbox);

        $this->assertEquals(10, $transformed->minX);
        $this->assertEquals(20, $transformed->minY);
        $this->assertEquals(110, $transformed->maxX);
        $this->assertEquals(70, $transformed->maxY);
    }

    public function testIsIdentity(): void
    {
        $identity = new Matrix();
        $this->assertTrue($identity->isIdentity());

        $notIdentity = new Matrix(2, 0, 0, 2, 0, 0);
        $this->assertFalse($notIdentity->isIdentity());
    }

    public function testToString(): void
    {
        $matrix = new Matrix(1, 2, 3, 4, 5, 6);

        $string = $matrix->toString();

        $this->assertEquals('matrix(1, 2, 3, 4, 5, 6)', $string);
    }

    public function testMagicToString(): void
    {
        $matrix = new Matrix(1, 2, 3, 4, 5, 6);

        $this->assertEquals('matrix(1, 2, 3, 4, 5, 6)', (string) $matrix);
    }

    public function testDecomposeWithNegativeDeterminantFlipsScaleY(): void
    {
        // Matrix with negative determinant (a*d - b*c < 0) triggers scaleY flip
        // Using a reflection matrix: scale(-1, 1) = matrix(-1, 0, 0, 1, 0, 0)
        $matrix = new Matrix(-1, 0, 0, 1, 0, 0);

        $result = $matrix->decompose();

        $this->assertLessThan(0, $result['scaleY']);
    }
}
