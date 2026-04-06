<?php

namespace Atelier\Svg\Tests\Path\Simplifier;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Simplifier\CollinearPointRemover;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollinearPointRemover::class)]
final class CollinearPointRemoverTest extends TestCase
{
    private CollinearPointRemover $simplifier;

    protected function setUp(): void
    {
        $this->simplifier = new CollinearPointRemover();
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

    public function testRemovesCollinearPoints(): void
    {
        // Three collinear points on a horizontal line
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0)),
            new LineTo('L', new Point(10, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        // Middle point should be removed
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->y);
        $this->assertEquals(10, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[1]->getTargetPoint()->y);
    }

    public function testRemovesMultipleCollinearPoints(): void
    {
        // Multiple collinear points on a diagonal line
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(1, 1)),
            new LineTo('L', new Point(2, 2)),
            new LineTo('L', new Point(3, 3)),
            new LineTo('L', new Point(4, 4)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        // All middle points should be removed
        $this->assertCount(2, $segments);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->y);
        $this->assertEquals(4, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(4, $segments[1]->getTargetPoint()->y);
    }

    public function testKeepsNonCollinearPoints(): void
    {
        // Points that form a right angle
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        // All points should be kept
        $this->assertCount(3, $segments);
    }

    public function testRemovesNearlyCollinearPoints(): void
    {
        // Points that are nearly collinear (within tolerance)
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0.05)),
            new LineTo('L', new Point(10, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        // Middle point should be removed as it's within tolerance
        $this->assertCount(2, $segments);
    }

    public function testKeepsPointsOutsideTolerance(): void
    {
        // Points that are not collinear enough to be removed
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 1)),
            new LineTo('L', new Point(10, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        // All points should be kept
        $this->assertCount(3, $segments);
    }

    public function testPreservesCurveSegments(): void
    {
        $curve = new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30));
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0)),
            new LineTo('L', new Point(10, 0)),
            $curve,
            new LineTo('L', new Point(40, 30)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
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
            new LineTo('L', new Point(5, 0)),
            new ClosePath('Z'),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        $lastSegment = $segments[count($segments) - 1];
        $this->assertInstanceOf(ClosePath::class, $lastSegment);
    }

    public function testHandlesMultiplePolylines(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0)),
            new LineTo('L', new Point(10, 0)),
            new MoveTo('M', new Point(20, 20)),
            new LineTo('L', new Point(25, 20)),
            new LineTo('L', new Point(30, 20)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        // Both polylines should be simplified
        $moveToCount = 0;
        foreach ($segments as $segment) {
            if ($segment instanceof MoveTo) {
                ++$moveToCount;
            }
        }
        $this->assertEquals(2, $moveToCount, 'Should have two MoveTo commands');
        $this->assertCount(4, $segments); // 2 MoveTo + 2 LineTo (one per polyline)
    }

    public function testPreservesRelativeCommands(): void
    {
        $pathData = new Data([
            new MoveTo('m', new Point(0, 0)),
            new LineTo('l', new Point(5, 0)),
            new LineTo('l', new Point(5, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        // First segment should preserve relative command
        $this->assertEquals('m', $segments[0]->getCommand());
    }

    public function testComplexPolylineWithMixedCollinearity(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0)),    // Collinear with start
            new LineTo('L', new Point(10, 0)),   // Collinear continues
            new LineTo('L', new Point(10, 5)),   // Turn
            new LineTo('L', new Point(10, 10)),  // Collinear vertical
            new LineTo('L', new Point(5, 10)),   // Turn
            new LineTo('L', new Point(0, 10)),   // Collinear horizontal
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        // Should remove collinear points but keep turns
        $this->assertLessThan(8, count($segments));
        $this->assertGreaterThan(2, count($segments));
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

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        // Should preserve both points (can't simplify a line segment)
        $this->assertCount(2, $segments);
    }

    public function testProcessPolylineWithSinglePointMoveTo(): void
    {
        // MoveTo followed by a non-LineTo: processPolyline is called with only 1 point
        // This covers the branch where count($polylinePoints) < 2 and startSegment != null
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new ClosePath('Z'),
            new MoveTo('M', new Point(10, 10)),
            new LineTo('L', new Point(20, 20)),
            new LineTo('L', new Point(30, 30)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        // The first MoveTo should be preserved even with only 1 point
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
    }

    public function testCollinearPointsWithZeroLengthLine(): void
    {
        // Tests perpendicularSqDistance when lineStart == lineEnd (zero length line)
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 5)),
            new LineTo('L', new Point(5, 5)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        $this->assertGreaterThanOrEqual(2, count($segments));
    }

    public function testSimplifyPolylineWithFewerThanThreePoints(): void
    {
        // A polyline with exactly 2 points (MoveTo + 1 LineTo) should return unchanged
        // This hits the simplifyPolylineCollinear branch where count($points) < 3
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
            new MoveTo('M', new Point(20, 20)),
            new LineTo('L', new Point(30, 30)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        $this->assertCount(4, $segments);
    }

    public function testPerpendicularSqDistanceWithCoincidentPoints(): void
    {
        // When lineStart == lineEnd, the perpendicular distance calculation
        // uses Euclidean distance from the point to lineStart (line 172-176)
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(0, 0)),
            new LineTo('L', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.001);
        $segments = $result->getSegments();

        $this->assertGreaterThanOrEqual(2, count($segments));
    }
}
