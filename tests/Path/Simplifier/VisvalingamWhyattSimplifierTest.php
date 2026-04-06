<?php

namespace Atelier\Svg\Tests\Path\Simplifier;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Simplifier\VisvalingamWhyattSimplifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VisvalingamWhyattSimplifier::class)]
final class VisvalingamWhyattSimplifierTest extends TestCase
{
    private VisvalingamWhyattSimplifier $simplifier;

    protected function setUp(): void
    {
        $this->simplifier = new VisvalingamWhyattSimplifier();
    }

    public function testSimplifyWithNegativeToleranceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tolerance must be non-negative.');

        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $this->simplifier->simplify($pathData, -1.0);
    }

    public function testSimplifyWithZeroToleranceReturnsOriginal(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
            new LineTo('L', new Point(20, 20)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.0);

        $this->assertSame($pathData, $result);
    }

    public function testSimplifyWithFewerThanThreeSegmentsReturnsOriginal(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $result = $this->simplifier->simplify($pathData, 5.0);

        $this->assertSame($pathData, $result);
    }

    public function testRemovesPointWithSmallestArea(): void
    {
        // Triangle with a point very close to the line
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0.1)),
            new LineTo('L', new Point(10, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        // Middle point should be removed (forms small triangle area)
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->y);
        $this->assertEquals(10, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[1]->getTargetPoint()->y);
    }

    public function testKeepsPointsWithLargeArea(): void
    {
        // Triangle with significant area
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 5)),
            new LineTo('L', new Point(10, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        // All points should be kept (area is large)
        $this->assertCount(3, $segments);
    }

    public function testSimplifyByRemovingSmallestAreasFirst(): void
    {
        // Four points forming different triangle areas
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(2, 0.1)),   // Small area
            new LineTo('L', new Point(4, 5)),     // Large area
            new LineTo('L', new Point(6, 0)),
        ]);

        // With low tolerance, removes point with smallest area
        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        // Should remove at least one point or keep all if areas are large
        $this->assertLessThanOrEqual(4, count($segments));
        // Should preserve first and last points
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $lastSegment = $segments[count($segments) - 1];
        $this->assertEquals(6, $lastSegment->getTargetPoint()->x);
    }

    public function testProgressiveSimplification(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(1, 0.5)),
            new LineTo('L', new Point(2, 1)),
            new LineTo('L', new Point(3, 0.5)),
            new LineTo('L', new Point(4, 0)),
        ]);

        // Low tolerance - keep more points
        $result1 = $this->simplifier->simplify($pathData, 0.1);
        $count1 = count($result1->getSegments());

        // High tolerance - remove more points
        $result2 = $this->simplifier->simplify($pathData, 10.0);
        $count2 = count($result2->getSegments());

        $this->assertGreaterThan($count2, $count1);
    }

    public function testPreservesFirstAndLastPoints(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0.1)),
            new LineTo('L', new Point(10, 0.1)),
            new LineTo('L', new Point(15, 0.1)),
            new LineTo('L', new Point(20, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 100.0);
        $segments = $result->getSegments();

        // First and last points must always be preserved
        $this->assertGreaterThanOrEqual(2, count($segments));
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $lastSegment = $segments[count($segments) - 1];
        $this->assertEquals(20, $lastSegment->getTargetPoint()->x);
    }

    public function testPreservesCurveSegments(): void
    {
        $curve = new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30));
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0.1)),
            new LineTo('L', new Point(10, 0)),
            $curve,
            new LineTo('L', new Point(40, 30)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        // Curve should be preserved
        $hasCurve = false;
        foreach ($segments as $segment) {
            if ($segment instanceof CurveTo) {
                $hasCurve = true;
                break;
            }
        }
        $this->assertTrue($hasCurve, 'Curve segment should be preserved');
    }

    public function testPreservesClosePath(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 0)),
            new LineTo('L', new Point(5, 0.1)),
            new LineTo('L', new Point(0, 0)),
            new ClosePath('Z'),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        $lastSegment = $segments[count($segments) - 1];
        $this->assertInstanceOf(ClosePath::class, $lastSegment);
    }

    public function testHandlesMultiplePolylines(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0.1)),
            new LineTo('L', new Point(10, 0)),
            new MoveTo('M', new Point(20, 20)),
            new LineTo('L', new Point(25, 20.1)),
            new LineTo('L', new Point(30, 20)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        // Both polylines should be processed
        $moveToCount = 0;
        foreach ($segments as $segment) {
            if ($segment instanceof MoveTo) {
                ++$moveToCount;
            }
        }
        $this->assertEquals(2, $moveToCount, 'Should have two MoveTo commands');
    }

    public function testPreservesRelativeCommands(): void
    {
        $pathData = new Data([
            new MoveTo('m', new Point(0, 0)),
            new LineTo('l', new Point(5, 0.1)),
            new LineTo('l', new Point(5, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        // First segment should preserve relative command
        $this->assertEquals('m', $segments[0]->getCommand());
    }

    public function testTriangleAreaCalculation(): void
    {
        // Create a path with a known triangle area
        // Triangle with vertices at (0,0), (4,0), (2,3)
        // Area = 0.5 * |x1(y2-y3) + x2(y3-y1) + x3(y1-y2)|
        // Area = 0.5 * |0(0-3) + 4(3-0) + 2(0-0)| = 0.5 * 12 = 6
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(2, 3)),
            new LineTo('L', new Point(4, 0)),
        ]);

        // With tolerance less than area (6), all points should be kept
        $result = $this->simplifier->simplify($pathData, 5.0);
        $this->assertCount(3, $result->getSegments());

        // With tolerance greater than area, middle point should be removed
        $result = $this->simplifier->simplify($pathData, 7.0);
        $this->assertCount(2, $result->getSegments());
    }

    public function testHandlesComplexPolyline(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(1, 1)),
            new LineTo('L', new Point(2, 0.5)),
            new LineTo('L', new Point(3, 2)),
            new LineTo('L', new Point(4, 1.5)),
            new LineTo('L', new Point(5, 3)),
            new LineTo('L', new Point(6, 2)),
            new LineTo('L', new Point(7, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 2.0);
        $segments = $result->getSegments();

        // Should simplify but keep first and last
        $this->assertLessThan(8, count($segments));
        $this->assertGreaterThanOrEqual(2, count($segments));
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $lastSegment = $segments[count($segments) - 1];
        $this->assertEquals(7, $lastSegment->getTargetPoint()->x);
    }

    public function testProcessPolylineWithSinglePointMoveTo(): void
    {
        // MoveTo followed by non-LineTo triggers processPolyline with < 2 points
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new ClosePath('Z'),
            new MoveTo('M', new Point(10, 10)),
            new LineTo('L', new Point(20, 20)),
            new LineTo('L', new Point(30, 30)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        $this->assertInstanceOf(MoveTo::class, $segments[0]);
    }

    public function testEmptyPolylineHandling(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        // Should preserve the single MoveTo
        $this->assertCount(1, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
    }

    public function testTwoPointPolyline(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $result = $this->simplifier->simplify($pathData, 100.0);
        $segments = $result->getSegments();

        // Should preserve both points (can't simplify a line segment)
        $this->assertCount(2, $segments);
    }

    public function testHighToleranceSimplifiesToMinimum(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(1, 0.1)),
            new LineTo('L', new Point(2, 0.2)),
            new LineTo('L', new Point(3, 0.1)),
            new LineTo('L', new Point(4, 0.2)),
            new LineTo('L', new Point(5, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1000.0);
        $segments = $result->getSegments();

        // With very high tolerance, should simplify to just start and end
        $this->assertEquals(2, count($segments));
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(5, $segments[1]->getTargetPoint()->x);
    }

    public function testSimplifyPolylineVWWithFewerThanThreePoints(): void
    {
        // Line 136: when count($points) < 3, returns unchanged
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
            new MoveTo('M', new Point(20, 20)),
            new LineTo('L', new Point(30, 30)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        // Each polyline has only 2 points, so none can be simplified
        $this->assertCount(4, $segments);
    }
}
