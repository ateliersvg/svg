<?php

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\PathAnalyzer;
use Atelier\Svg\Path\PathBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathAnalyzer::class)]
final class PathAnalyzerCurvesTest extends TestCase
{
    public function testGetLengthWithCubicCurve(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(25, 0, 25, 50, 50, 50)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        // Curve length should be greater than straight line distance
        $straightDistance = sqrt(50 * 50 + 50 * 50);
        $this->assertGreaterThan($straightDistance, $length);
    }

    public function testGetLengthWithQuadraticCurve(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->quadraticCurveTo(25, 50, 50, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        // Curve length should be greater than straight line (50)
        $this->assertGreaterThan(50, $length);
    }

    public function testGetLengthWithArc(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->arcTo(25, 25, 0, false, true, 50, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        // Arc should have measurable length
        $this->assertGreaterThan(0, $length);
    }

    public function testGetPointAtLengthOnCurve(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(0, 100, 100, 100, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $midpoint = $analyzer->getPointAtLength($analyzer->getLength() / 2);

        $this->assertNotNull($midpoint);
        // Midpoint should be somewhere in the middle
        $this->assertGreaterThan(25, $midpoint->x);
        $this->assertLessThan(75, $midpoint->x);
    }

    public function testGetBoundingBoxWithCurves(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(50, 100, 150, 100, 200, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $bbox = $analyzer->getBoundingBox();

        // Bounding box should encompass the curve
        $this->assertEquals(0, $bbox->minX);
        $this->assertGreaterThanOrEqual(200, $bbox->maxX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertGreaterThan(0, $bbox->maxY);
    }

    public function testGetVerticesWithCurves(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(25, 25, 75, 25, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $vertices = $analyzer->getVertices();

        // Should have multiple sampled points from the curve
        $this->assertGreaterThan(1, count($vertices));
    }

    public function testGetVerticesWithQuadraticCurve(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->quadraticCurveTo(50, 100, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $vertices = $analyzer->getVertices();

        // Should have multiple sampled points
        $this->assertGreaterThan(1, count($vertices));
    }

    public function testGetBoundingBoxWithQuadraticCurve(): void
    {
        $path = PathBuilder::new()
            ->moveTo(10, 10)
            ->quadraticCurveTo(50, 80, 90, 10)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $bbox = $analyzer->getBoundingBox();

        // Bounding box should include control point influence
        $this->assertLessThanOrEqual(10, $bbox->minX);
        $this->assertGreaterThanOrEqual(90, $bbox->maxX);
        $this->assertLessThanOrEqual(10, $bbox->minY);
        $this->assertGreaterThan(10, $bbox->maxY);
    }

    public function testMixedSegmentTypes(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(50, 0)
            ->curveTo(75, 0, 75, 50, 50, 50)
            ->lineTo(0, 50)
            ->closePath()
            ->toData();

        $analyzer = new PathAnalyzer($path);

        $length = $analyzer->getLength();
        $this->assertGreaterThan(150, $length);

        $bbox = $analyzer->getBoundingBox();
        $this->assertEqualsWithDelta(0, $bbox->minX, 0.1);
        $this->assertEqualsWithDelta(0, $bbox->minY, 0.1);

        $vertices = $analyzer->getVertices();
        $this->assertGreaterThan(5, count($vertices));
    }

    public function testHorizontalAndVerticalLines(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->horizontalLineTo(50)
            ->verticalLineTo(50)
            ->horizontalLineTo(0)
            ->verticalLineTo(0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        // Should be perimeter of square: 4 * 50 = 200
        $this->assertEqualsWithDelta(200, $length, 0.1);
    }

    public function testComplexCurvePath(): void
    {
        // Create a more complex path with multiple curve types
        $path = PathBuilder::new()
            ->moveTo(10, 10)
            ->curveTo(40, 10, 40, 40, 40, 70)
            ->smoothCurveTo(40, 100, 10, 100)
            ->quadraticCurveTo(5, 55, 10, 10)
            ->closePath()
            ->toData();

        $analyzer = new PathAnalyzer($path);

        // Verify all methods work with complex paths
        $length = $analyzer->getLength();
        $this->assertGreaterThan(0, $length);

        $bbox = $analyzer->getBoundingBox();
        $this->assertNotNull($bbox);

        $center = $analyzer->getCenter();
        $this->assertNotNull($center);

        $vertices = $analyzer->getVertices();
        $this->assertGreaterThan(5, count($vertices));
    }
}
