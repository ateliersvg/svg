<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Data::class)]
final class DataTest extends TestCase
{
    public function testConstructWithEmptyArray(): void
    {
        $data = new Data([]);
        $this->assertTrue($data->isEmpty());
        $this->assertSame(0, $data->count());
    }

    public function testConstructWithDefaultParameter(): void
    {
        $data = new Data();
        $this->assertTrue($data->isEmpty());
    }

    public function testConstructWithSegments(): void
    {
        $segments = [
            new MoveTo('M', new Point(10, 20)),
            new LineTo('L', new Point(30, 40)),
        ];
        $data = new Data($segments);

        $this->assertFalse($data->isEmpty());
        $this->assertSame(2, $data->count());
    }

    public function testGetSegments(): void
    {
        $segments = [
            new MoveTo('M', new Point(10, 20)),
            new LineTo('L', new Point(30, 40)),
        ];
        $data = new Data($segments);

        $result = $data->getSegments();
        $this->assertCount(2, $result);
        $this->assertInstanceOf(MoveTo::class, $result[0]);
        $this->assertInstanceOf(LineTo::class, $result[1]);
    }

    public function testAddSegment(): void
    {
        $data = new Data();
        $this->assertSame(0, $data->count());

        $data->addSegment(new MoveTo('M', new Point(10, 20)));
        $this->assertSame(1, $data->count());

        $data->addSegment(new LineTo('L', new Point(30, 40)));
        $this->assertSame(2, $data->count());
    }

    public function testCount(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 20)),
            new ClosePath('Z'),
        ]);

        $this->assertSame(3, $data->count());
    }

    public function testIsEmpty(): void
    {
        $empty = new Data([]);
        $this->assertTrue($empty->isEmpty());

        $notEmpty = new Data([new MoveTo('M', new Point(0, 0))]);
        $this->assertFalse($notEmpty->isEmpty());
    }

    public function testReverse(): void
    {
        $segments = [
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 20)),
            new ClosePath('Z'),
        ];
        $data = new Data($segments);

        $reversed = $data->reverse();

        // Reversed should be a new Data instance
        $this->assertNotSame($data, $reversed);
        $this->assertSame(3, $reversed->count());

        $reversedSegments = $reversed->getSegments();
        // Correct reversal:
        // Original: (0,0) --L--> (10,20) --Z--> (0,0)
        // Reversed: (0,0) --L--> (10,20) --L--> (0,0)
        $this->assertInstanceOf(MoveTo::class, $reversedSegments[0]);
        $this->assertInstanceOf(LineTo::class, $reversedSegments[1]);
        $this->assertInstanceOf(LineTo::class, $reversedSegments[2]);
    }

    public function testReverseDoesNotMutateOriginal(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 20)),
        ]);

        $data->reverse();
        $segments = $data->getSegments();

        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
    }

    public function testSubpath(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 20)),
            new LineTo('L', new Point(30, 40)),
            new ClosePath('Z'),
        ]);

        $sub = $data->subpath(1, 2);

        $this->assertSame(2, $sub->count());
        $segments = $sub->getSegments();
        $this->assertInstanceOf(LineTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
    }

    public function testSubpathReturnsNewInstance(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 20)),
        ]);

        $sub = $data->subpath(0, 1);
        $this->assertNotSame($data, $sub);
    }

    public function testToString(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(10, 20)),
            new LineTo('L', new Point(30, 40)),
            new ClosePath('Z'),
        ]);

        $result = $data->toString();
        $this->assertSame('M 10,20 L 30,40 Z', $result);
    }

    public function testToStringEmpty(): void
    {
        $data = new Data([]);
        $this->assertSame('', $data->toString());
    }

    public function testMagicToString(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(10, 20)),
            new LineTo('L', new Point(30, 40)),
        ]);

        $this->assertSame($data->toString(), (string) $data);
    }

    public function testImplementsStringable(): void
    {
        $data = new Data();
        $this->assertInstanceOf(\Stringable::class, $data);
    }

    public function testReverseEmptySegmentsReturnsEmpty(): void
    {
        $data = new Data([]);
        $reversed = $data->reverse();

        $this->assertNotSame($data, $reversed);
        $this->assertTrue($reversed->isEmpty());
        $this->assertSame(0, $reversed->count());
    }

    public function testReverseWithCurveToSegments(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new \Atelier\Svg\Path\Segment\CurveTo(
                'C',
                new Point(10, 20),
                new Point(30, 40),
                new Point(50, 50)
            ),
        ]);

        $reversed = $data->reverse();
        $segments = $reversed->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        // The reversed CurveTo should swap control points
        $this->assertInstanceOf(\Atelier\Svg\Path\Segment\CurveTo::class, $segments[1]);

        $curveSeg = $segments[1];
        // Control points should be swapped: cp2 becomes cp1, cp1 becomes cp2
        $this->assertEqualsWithDelta(30, $curveSeg->getControlPoint1()->x, 0.001);
        $this->assertEqualsWithDelta(40, $curveSeg->getControlPoint1()->y, 0.001);
        $this->assertEqualsWithDelta(10, $curveSeg->getControlPoint2()->x, 0.001);
        $this->assertEqualsWithDelta(20, $curveSeg->getControlPoint2()->y, 0.001);
        // Target should be the previous point (the MoveTo origin)
        $this->assertEqualsWithDelta(0, $curveSeg->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(0, $curveSeg->getTargetPoint()->y, 0.001);
    }

    public function testReverseWithQuadraticCurveToSegments(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new \Atelier\Svg\Path\Segment\QuadraticCurveTo(
                'Q',
                new Point(25, 50),
                new Point(50, 0)
            ),
        ]);

        $reversed = $data->reverse();
        $segments = $reversed->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(\Atelier\Svg\Path\Segment\QuadraticCurveTo::class, $segments[1]);

        $quadSeg = $segments[1];
        // Control point stays the same
        $this->assertEqualsWithDelta(25, $quadSeg->getControlPoint()->x, 0.001);
        $this->assertEqualsWithDelta(50, $quadSeg->getControlPoint()->y, 0.001);
        // Target should be the previous point
        $this->assertEqualsWithDelta(0, $quadSeg->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(0, $quadSeg->getTargetPoint()->y, 0.001);
    }

    public function testReverseWithArcToFallback(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new \Atelier\Svg\Path\Segment\ArcTo(
                'A',
                25.0,
                25.0,
                0.0,
                false,
                true,
                new Point(50, 50)
            ),
        ]);

        $reversed = $data->reverse();
        $segments = $reversed->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        // ArcTo falls back to LineTo in reverse
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertEqualsWithDelta(0, $segments[1]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(0, $segments[1]->getTargetPoint()->y, 0.001);
    }
}
