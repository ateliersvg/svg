<?php

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Geometry\Matrix;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Matrix::class)]
final class MatrixEnhancedTest extends TestCase
{
    public function testDecomposeIdentity(): void
    {
        $matrix = new Matrix();

        $components = $matrix->decompose();

        $this->assertEquals(0, $components['translateX']);
        $this->assertEquals(0, $components['translateY']);
        $this->assertEquals(1, $components['scaleX']);
        $this->assertEquals(1, $components['scaleY']);
        $this->assertEqualsWithDelta(0, $components['rotation'], 0.0001);
        $this->assertEqualsWithDelta(0, $components['skewX'], 0.0001);
    }

    public function testDecomposeTranslation(): void
    {
        $matrix = new Matrix(1, 0, 0, 1, 10, 20);

        $components = $matrix->decompose();

        $this->assertEquals(10, $components['translateX']);
        $this->assertEquals(20, $components['translateY']);
        $this->assertEquals(1, $components['scaleX']);
        $this->assertEquals(1, $components['scaleY']);
    }

    public function testDecomposeScale(): void
    {
        $matrix = new Matrix(2, 0, 0, 3, 0, 0);

        $components = $matrix->decompose();

        $this->assertEquals(2, $components['scaleX']);
        $this->assertEquals(3, $components['scaleY']);
        $this->assertEqualsWithDelta(0, $components['rotation'], 0.0001);
    }

    public function testDecomposeRotation(): void
    {
        // 45 degree rotation
        $angle = deg2rad(45);
        $matrix = new Matrix(
            cos($angle),
            sin($angle),
            -sin($angle),
            cos($angle),
            0,
            0
        );

        $components = $matrix->decompose();

        $this->assertEqualsWithDelta(45, $components['rotation'], 0.0001);
        $this->assertEqualsWithDelta(1, $components['scaleX'], 0.0001);
        $this->assertEqualsWithDelta(1, $components['scaleY'], 0.0001);
    }

    public function testIsUniformScale(): void
    {
        $uniform = new Matrix(2, 0, 0, 2, 0, 0);
        $this->assertTrue($uniform->isUniformScale());

        $nonUniform = new Matrix(2, 0, 0, 3, 0, 0);
        $this->assertFalse($nonUniform->isUniformScale());
    }

    public function testHasShear(): void
    {
        $noShear = new Matrix(1, 0, 0, 1, 0, 0);
        $this->assertFalse($noShear->hasShear());

        // Skew matrix
        $withShear = new Matrix(1, 0, tan(deg2rad(30)), 1, 0, 0);
        $this->assertTrue($withShear->hasShear());
    }
}
