<?php

namespace Atelier\Svg\Tests\Path\Simplifier;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Simplifier\Simplifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Simplifier::class)]
final class SimplifierTest extends TestCase
{
    private Simplifier $simplifier;

    protected function setUp(): void
    {
        $this->simplifier = new Simplifier();
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

    public function testSimplifySimplePolyline(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 0.1)),
            new LineTo('L', new Point(10, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->y);
        $this->assertEquals(10, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[1]->getTargetPoint()->y);
    }

    public function testSimplifyComplexPolyline(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
            new LineTo('L', new Point(20, 15)),
            new LineTo('L', new Point(30, 20)),
            new LineTo('L', new Point(40, 40)),
        ]);

        $result = $this->simplifier->simplify($pathData, 10.0);
        $segments = $result->getSegments();

        // Should simplify some intermediate points
        $this->assertLessThan(5, count($segments));
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        // First and last points should be preserved
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->y);
        $lastSegment = $segments[count($segments) - 1];
        $this->assertEquals(40, $lastSegment->getTargetPoint()->x);
        $this->assertEquals(40, $lastSegment->getTargetPoint()->y);
    }

    public function testSimplifyPreservesCurveSegments(): void
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
        $this->assertContainsOnlyInstancesOf(\Atelier\Svg\Path\Segment\SegmentInterface::class, $segments);
        $hasCurve = false;
        foreach ($segments as $segment) {
            if ($segment instanceof CurveTo) {
                $hasCurve = true;
                break;
            }
        }
        $this->assertTrue($hasCurve, 'Curve segment should be preserved');
    }

    public function testSimplifyPreservesClosePath(): void
    {
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 0)),
            new LineTo('L', new Point(10, 10)),
            new LineTo('L', new Point(0, 10)),
            new ClosePath('Z'),
        ]);

        $result = $this->simplifier->simplify($pathData, 1.0);
        $segments = $result->getSegments();

        $lastSegment = $segments[count($segments) - 1];
        $this->assertInstanceOf(ClosePath::class, $lastSegment);
    }

    public function testSimplifyMultiplePolylines(): void
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

        // Both polylines should be simplified
        $moveToCount = 0;
        foreach ($segments as $segment) {
            if ($segment instanceof MoveTo) {
                ++$moveToCount;
            }
        }
        $this->assertEquals(2, $moveToCount, 'Should have two MoveTo commands');
    }

    public function testSimplifyPreservesRelativeCommands(): void
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

    public function testSimplifyRDPAlgorithm(): void
    {
        // Test case that specifically tests the RDP (Ramer-Douglas-Peucker) algorithm
        // Create a path with points that form a slight arc
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(1, 1)),
            new LineTo('L', new Point(2, 1.5)),
            new LineTo('L', new Point(3, 1)),
            new LineTo('L', new Point(4, 0)),
        ]);

        // With low tolerance, should keep the peak point
        $result = $this->simplifier->simplify($pathData, 0.5);
        $segments = $result->getSegments();
        $this->assertGreaterThan(2, count($segments));

        // With high tolerance, should simplify to just start and end
        $result = $this->simplifier->simplify($pathData, 5.0);
        $segments = $result->getSegments();
        $this->assertEquals(2, count($segments));
    }

    public function testProcessPolylineWithSinglePointMoveTo(): void
    {
        // MoveTo followed immediately by a non-LineTo segment
        // This triggers processPolyline with count($polylinePoints) < 2
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

    public function testRDPWithZeroLengthLine(): void
    {
        // Tests perpendicularSqDistance when start and end are the same point
        $pathData = new Data([
            new MoveTo('M', new Point(5, 5)),
            new LineTo('L', new Point(5, 5)),
            new LineTo('L', new Point(5, 5)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        $this->assertGreaterThanOrEqual(2, count($segments));
    }

    public function testPerpendicularSqDistanceWithCoincidentStartEnd(): void
    {
        // When start == end in RDP recursion, the perpendicularSqDistance
        // calculation returns dist from point to start (lines 180-184)
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(3, 4)),
            new LineTo('L', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        $this->assertGreaterThanOrEqual(2, count($segments));
    }

    public function testSimplifyWithCoincidentConsecutivePoints(): void
    {
        // Two consecutive LineTo segments to the same point create a zero-length segment
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 5)),
            new LineTo('L', new Point(5, 5)),
            new LineTo('L', new Point(10, 0)),
        ]);

        $result = $this->simplifier->simplify($pathData, 0.1);
        $segments = $result->getSegments();

        $this->assertGreaterThanOrEqual(2, count($segments));
    }

    public function testSimplifyPolylineWithSameFirstAndLastPoint(): void
    {
        // When the first and last points of the polyline are identical,
        // perpendicularSqDistance receives a zero-length line segment
        // (lineStart == lineEnd), triggering the lineLengthSq == 0 branch.
        $pathData = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(5, 5)),
            new LineTo('L', new Point(3, 7)),
            new LineTo('L', new Point(0, 0)),  // same as start -> closed polyline
        ]);

        $result = $this->simplifier->simplify($pathData, 0.5);
        $segments = $result->getSegments();

        $this->assertGreaterThanOrEqual(2, count($segments));
    }
}
