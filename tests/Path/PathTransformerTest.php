<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\PathTransformer;
use Atelier\Svg\Path\Segment\ArcTo;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\HorizontalLineTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\QuadraticCurveTo;
use Atelier\Svg\Path\Segment\SmoothCurveTo;
use Atelier\Svg\Path\Segment\SmoothQuadraticCurveTo;
use Atelier\Svg\Path\Segment\VerticalLineTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathTransformer::class)]
final class PathTransformerTest extends TestCase
{
    private PathTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new PathTransformer();
    }

    public function testTransformReturnsDataInstance(): void
    {
        $data = new Data([new MoveTo('M', new Point(10, 20))]);
        $matrix = new Matrix(); // identity

        $result = $this->transformer->transform($data, $matrix);
        $this->assertInstanceOf(Data::class, $result);
    }

    public function testTransformEmptyData(): void
    {
        $data = new Data([]);
        $matrix = new Matrix(2, 0, 0, 2, 0, 0); // scale 2x

        $result = $this->transformer->transform($data, $matrix);
        $this->assertTrue($result->isEmpty());
    }

    public function testTransformMoveToWithTranslation(): void
    {
        $data = new Data([new MoveTo('M', new Point(10, 20))]);
        $matrix = new Matrix(1, 0, 0, 1, 5, 10); // translate(5, 10)

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertCount(1, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertEqualsWithDelta(15.0, $segments[0]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(30.0, $segments[0]->getTargetPoint()->y, 0.001);
    }

    public function testTransformMoveToWithScale(): void
    {
        $data = new Data([new MoveTo('M', new Point(10, 20))]);
        $matrix = new Matrix(2, 0, 0, 3, 0, 0); // scale(2, 3)

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertEqualsWithDelta(20.0, $segments[0]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(60.0, $segments[0]->getTargetPoint()->y, 0.001);
    }

    public function testTransformLineTo(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 20)),
        ]);
        $matrix = new Matrix(1, 0, 0, 1, 100, 200); // translate(100, 200)

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertEqualsWithDelta(110.0, $segments[1]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(220.0, $segments[1]->getTargetPoint()->y, 0.001);
    }

    public function testTransformHorizontalLineToBecomesLineTo(): void
    {
        $data = new Data([new HorizontalLineTo('H', 50.0)]);
        $matrix = new Matrix(1, 0, 0, 1, 10, 20);

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        // H is converted to L after transformation
        $this->assertInstanceOf(LineTo::class, $segments[0]);
        $this->assertSame('L', $segments[0]->getCommand());
    }

    public function testTransformVerticalLineToBecomesLineTo(): void
    {
        $data = new Data([new VerticalLineTo('V', 80.0)]);
        $matrix = new Matrix(1, 0, 0, 1, 10, 20);

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        // V is converted to L after transformation
        $this->assertInstanceOf(LineTo::class, $segments[0]);
        $this->assertSame('L', $segments[0]->getCommand());
    }

    public function testTransformCurveTo(): void
    {
        $data = new Data([
            new CurveTo('C', new Point(10, 20), new Point(30, 40), new Point(50, 60)),
        ]);
        $matrix = new Matrix(2, 0, 0, 2, 0, 0); // scale(2)

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertInstanceOf(CurveTo::class, $segments[0]);
        $this->assertEqualsWithDelta(20.0, $segments[0]->getControlPoint1()->x, 0.001);
        $this->assertEqualsWithDelta(40.0, $segments[0]->getControlPoint1()->y, 0.001);
        $this->assertEqualsWithDelta(60.0, $segments[0]->getControlPoint2()->x, 0.001);
        $this->assertEqualsWithDelta(80.0, $segments[0]->getControlPoint2()->y, 0.001);
        $this->assertEqualsWithDelta(100.0, $segments[0]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(120.0, $segments[0]->getTargetPoint()->y, 0.001);
    }

    public function testTransformSmoothCurveTo(): void
    {
        $data = new Data([
            new SmoothCurveTo('S', new Point(30, 40), new Point(50, 60)),
        ]);
        $matrix = new Matrix(2, 0, 0, 2, 0, 0);

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertInstanceOf(SmoothCurveTo::class, $segments[0]);
        $this->assertEqualsWithDelta(60.0, $segments[0]->getControlPoint2()->x, 0.001);
        $this->assertEqualsWithDelta(80.0, $segments[0]->getControlPoint2()->y, 0.001);
        $this->assertEqualsWithDelta(100.0, $segments[0]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(120.0, $segments[0]->getTargetPoint()->y, 0.001);
    }

    public function testTransformQuadraticCurveTo(): void
    {
        $data = new Data([
            new QuadraticCurveTo('Q', new Point(20, 30), new Point(40, 50)),
        ]);
        $matrix = new Matrix(2, 0, 0, 2, 0, 0);

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertInstanceOf(QuadraticCurveTo::class, $segments[0]);
        $this->assertEqualsWithDelta(40.0, $segments[0]->getControlPoint()->x, 0.001);
        $this->assertEqualsWithDelta(60.0, $segments[0]->getControlPoint()->y, 0.001);
        $this->assertEqualsWithDelta(80.0, $segments[0]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(100.0, $segments[0]->getTargetPoint()->y, 0.001);
    }

    public function testTransformSmoothQuadraticCurveTo(): void
    {
        $data = new Data([
            new SmoothQuadraticCurveTo('T', new Point(40, 50)),
        ]);
        $matrix = new Matrix(2, 0, 0, 2, 0, 0);

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertInstanceOf(SmoothQuadraticCurveTo::class, $segments[0]);
        $this->assertEqualsWithDelta(80.0, $segments[0]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(100.0, $segments[0]->getTargetPoint()->y, 0.001);
    }

    public function testTransformArcTo(): void
    {
        $data = new Data([
            new ArcTo('A', 25.0, 26.0, 30.0, false, true, new Point(50, 25)),
        ]);
        $matrix = new Matrix(2, 0, 0, 3, 10, 20); // scale(2,3) + translate(10,20)

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertInstanceOf(ArcTo::class, $segments[0]);
        // Radii are scaled by abs(matrix.a) and abs(matrix.d)
        $this->assertEqualsWithDelta(50.0, $segments[0]->getRx(), 0.001);
        $this->assertEqualsWithDelta(78.0, $segments[0]->getRy(), 0.001);
        // Flags preserved
        $this->assertFalse($segments[0]->getLargeArcFlag());
        $this->assertTrue($segments[0]->getSweepFlag());
        // Target point transformed
        $this->assertEqualsWithDelta(110.0, $segments[0]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(95.0, $segments[0]->getTargetPoint()->y, 0.001);
    }

    public function testTransformClosePathIsUnchanged(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(50, 50)),
            new ClosePath('Z'),
        ]);
        $matrix = new Matrix(2, 0, 0, 2, 0, 0);

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertCount(3, $segments);
        $this->assertInstanceOf(ClosePath::class, $segments[2]);
        $this->assertSame('Z', $segments[2]->getCommand());
    }

    public function testTransformWithIdentityMatrixPreservesCoordinates(): void
    {
        $data = new Data([
            new MoveTo('M', new Point(10, 20)),
            new LineTo('L', new Point(30, 40)),
        ]);
        $matrix = new Matrix(); // identity

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertEqualsWithDelta(10.0, $segments[0]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(20.0, $segments[0]->getTargetPoint()->y, 0.001);
        $this->assertEqualsWithDelta(30.0, $segments[1]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(40.0, $segments[1]->getTargetPoint()->y, 0.001);
    }

    public function testTransformConvertsRelativeCommandToAbsolute(): void
    {
        $data = new Data([new MoveTo('m', new Point(10, 20))]);
        $matrix = new Matrix(); // identity

        $result = $this->transformer->transform($data, $matrix);
        $segments = $result->getSegments();

        $this->assertSame('M', $segments[0]->getCommand());
    }
}
