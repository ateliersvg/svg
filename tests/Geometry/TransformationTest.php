<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Geometry\Transformation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Transformation::class)]
final class TransformationTest extends TestCase
{
    public function testIdentityReturnsIdentityMatrix(): void
    {
        $matrix = Transformation::identity();

        $this->assertInstanceOf(Matrix::class, $matrix);
        $this->assertTrue($matrix->isIdentity());
    }

    public function testIdentityDoesNotAlterPoint(): void
    {
        $matrix = Transformation::identity();
        $point = $matrix->transform(new Point(5.0, 10.0));

        $this->assertSame(5.0, $point->x);
        $this->assertSame(10.0, $point->y);
    }

    public function testTranslate(): void
    {
        $matrix = Transformation::translate(10.0, 20.0);
        $point = $matrix->transform(new Point(0.0, 0.0));

        $this->assertSame(10.0, $point->x);
        $this->assertSame(20.0, $point->y);
    }

    public function testTranslateNegative(): void
    {
        $matrix = Transformation::translate(-5.0, -3.0);
        $point = $matrix->transform(new Point(10.0, 10.0));

        $this->assertSame(5.0, $point->x);
        $this->assertSame(7.0, $point->y);
    }

    public function testScaleUniform(): void
    {
        $matrix = Transformation::scale(2.0);
        $point = $matrix->transform(new Point(5.0, 10.0));

        $this->assertSame(10.0, $point->x);
        $this->assertSame(20.0, $point->y);
    }

    public function testScaleNonUniform(): void
    {
        $matrix = Transformation::scale(2.0, 3.0);
        $point = $matrix->transform(new Point(5.0, 10.0));

        $this->assertSame(10.0, $point->x);
        $this->assertSame(30.0, $point->y);
    }

    public function testScaleWithSingleArgumentUsesUniformScaling(): void
    {
        $matrix = Transformation::scale(3.0);

        $this->assertSame(3.0, $matrix->a);
        $this->assertSame(3.0, $matrix->d);
    }

    public function testRotate90Degrees(): void
    {
        $matrix = Transformation::rotate(90.0);
        $point = $matrix->transform(new Point(1.0, 0.0));

        $this->assertEqualsWithDelta(0.0, $point->x, 1e-10);
        $this->assertEqualsWithDelta(1.0, $point->y, 1e-10);
    }

    public function testRotate180Degrees(): void
    {
        $matrix = Transformation::rotate(180.0);
        $point = $matrix->transform(new Point(1.0, 0.0));

        $this->assertEqualsWithDelta(-1.0, $point->x, 1e-10);
        $this->assertEqualsWithDelta(0.0, $point->y, 1e-10);
    }

    public function testRotateAroundCenter(): void
    {
        $matrix = Transformation::rotate(90.0, 5.0, 5.0);
        $point = $matrix->transform(new Point(10.0, 5.0));

        $this->assertEqualsWithDelta(5.0, $point->x, 1e-10);
        $this->assertEqualsWithDelta(10.0, $point->y, 1e-10);
    }

    public function testSkewX(): void
    {
        $matrix = Transformation::skewX(45.0);
        $point = $matrix->transform(new Point(0.0, 1.0));

        $this->assertEqualsWithDelta(1.0, $point->x, 1e-10);
        $this->assertEqualsWithDelta(1.0, $point->y, 1e-10);
    }

    public function testSkewXZeroDegrees(): void
    {
        $matrix = Transformation::skewX(0.0);

        $this->assertTrue($matrix->isIdentity());
    }

    public function testSkewY(): void
    {
        $matrix = Transformation::skewY(45.0);
        $point = $matrix->transform(new Point(1.0, 0.0));

        $this->assertEqualsWithDelta(1.0, $point->x, 1e-10);
        $this->assertEqualsWithDelta(1.0, $point->y, 1e-10);
    }

    public function testSkewYZeroDegrees(): void
    {
        $matrix = Transformation::skewY(0.0);

        $this->assertTrue($matrix->isIdentity());
    }

    public function testFromMatrixReturnsSameMatrix(): void
    {
        $original = new Matrix(1, 2, 3, 4, 5, 6);
        $result = Transformation::fromMatrix($original);

        $this->assertSame($original, $result);
    }

    public function testTransformationComposition(): void
    {
        $translated = Transformation::translate(10.0, 0.0);
        $scaled = Transformation::scale(2.0);
        $composed = $scaled->multiply($translated);
        $point = $composed->transform(new Point(0.0, 0.0));

        $this->assertEqualsWithDelta(20.0, $point->x, 1e-10);
        $this->assertEqualsWithDelta(0.0, $point->y, 1e-10);
    }

    public function testAllMethodsReturnMatrixInstance(): void
    {
        $this->assertInstanceOf(Matrix::class, Transformation::identity());
        $this->assertInstanceOf(Matrix::class, Transformation::translate(1, 1));
        $this->assertInstanceOf(Matrix::class, Transformation::scale(2));
        $this->assertInstanceOf(Matrix::class, Transformation::rotate(45));
        $this->assertInstanceOf(Matrix::class, Transformation::skewX(10));
        $this->assertInstanceOf(Matrix::class, Transformation::skewY(10));
        $this->assertInstanceOf(Matrix::class, Transformation::fromMatrix(new Matrix()));
    }
}
