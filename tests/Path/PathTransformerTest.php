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

    public function testTransformArcToUnderNonUniformScaleOfATurnedEllipse(): void
    {
        // scale(2,3) on an ellipse already turned by 30 degrees. Scaling each
        // radius by its own axis would only be right at a rotation of zero.
        $data = new Data([
            new ArcTo('A', 25.0, 26.0, 30.0, false, true, new Point(50, 25)),
        ]);
        $matrix = new Matrix(2, 0, 0, 3, 10, 20);

        $result = $this->transformer->transform($data, $matrix);
        $arc = $result->getSegments()[0];

        $this->assertInstanceOf(ArcTo::class, $arc);
        // The area of an ellipse scales by the determinant, here 25 * 26 * 6.
        $this->assertEqualsWithDelta(3900.0, $arc->getRx() * $arc->getRy(), 0.001);
        $this->assertGreaterThan($arc->getRy(), $arc->getRx());
        $this->assertNotEqualsWithDelta(30.0, $arc->getXAxisRotation(), 0.1, 'the major axis turns');
        // Flags preserved, the determinant stays positive.
        $this->assertFalse($arc->getLargeArcFlag());
        $this->assertTrue($arc->getSweepFlag());
        $this->assertEqualsWithDelta(110.0, $arc->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(95.0, $arc->getTargetPoint()->y, 0.001);
    }

    public function testTransformArcToUnderTranslateKeepsTheEllipse(): void
    {
        $data = new Data([new ArcTo('A', 25.0, 10.0, 30.0, false, true, new Point(50, 25))]);

        $result = $this->transformer->transform($data, new Matrix(1, 0, 0, 1, 10, 20));
        $arc = $result->getSegments()[0];

        $this->assertInstanceOf(ArcTo::class, $arc);
        $this->assertEqualsWithDelta(25.0, $arc->getRx(), 0.001);
        $this->assertEqualsWithDelta(10.0, $arc->getRy(), 0.001);
        $this->assertEqualsWithDelta(30.0, $arc->getXAxisRotation(), 0.001);
        $this->assertTrue($arc->getSweepFlag());
        $this->assertEqualsWithDelta(60.0, $arc->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(45.0, $arc->getTargetPoint()->y, 0.001);
    }

    public function testTransformArcToUnderUniformScaleScalesBothRadii(): void
    {
        $data = new Data([new ArcTo('A', 25.0, 10.0, 30.0, false, true, new Point(50, 25))]);

        $result = $this->transformer->transform($data, new Matrix(2, 0, 0, 2));
        $arc = $result->getSegments()[0];

        $this->assertInstanceOf(ArcTo::class, $arc);
        $this->assertEqualsWithDelta(50.0, $arc->getRx(), 0.001);
        $this->assertEqualsWithDelta(20.0, $arc->getRy(), 0.001);
        $this->assertEqualsWithDelta(30.0, $arc->getXAxisRotation(), 0.001);
    }

    public function testTransformArcToUnderRotationTurnsTheEllipse(): void
    {
        // A 90 degree rotation keeps the radii and turns the major axis by 90.
        $data = new Data([new ArcTo('A', 30.0, 10.0, 0.0, false, true, new Point(50, 25))]);

        $result = $this->transformer->transform($data, new Matrix(0, 1, -1, 0));
        $arc = $result->getSegments()[0];

        $this->assertInstanceOf(ArcTo::class, $arc);
        $this->assertEqualsWithDelta(30.0, $arc->getRx(), 0.001);
        $this->assertEqualsWithDelta(10.0, $arc->getRy(), 0.001);
        $this->assertEqualsWithDelta(90.0, $arc->getXAxisRotation(), 0.001);
    }

    public function testTransformArcToUnderReflectionFlipsTheSweepFlag(): void
    {
        $data = new Data([new ArcTo('A', 30.0, 10.0, 0.0, true, true, new Point(50, 25))]);

        $result = $this->transformer->transform($data, new Matrix(-1, 0, 0, 1));
        $arc = $result->getSegments()[0];

        $this->assertInstanceOf(ArcTo::class, $arc);
        $this->assertFalse($arc->getSweepFlag(), 'A reflection reverses the sweep direction');
        $this->assertTrue($arc->getLargeArcFlag(), 'The large-arc flag is unaffected');
        $this->assertEqualsWithDelta(30.0, $arc->getRx(), 0.001);
        $this->assertEqualsWithDelta(10.0, $arc->getRy(), 0.001);
    }

    public function testTransformArcToUnderSkewScalesTheEllipseAreaByTheDeterminant(): void
    {
        // skewX(45): the determinant is 1, so rx * ry is preserved, but the
        // ellipse is no longer axis aligned and neither radius survives as is.
        $data = new Data([new ArcTo('A', 20.0, 10.0, 0.0, false, true, new Point(50, 25))]);

        $result = $this->transformer->transform($data, new Matrix(1, 0, 1, 1));
        $arc = $result->getSegments()[0];

        $this->assertInstanceOf(ArcTo::class, $arc);
        $this->assertEqualsWithDelta(200.0, $arc->getRx() * $arc->getRy(), 0.001);
        $this->assertGreaterThan($arc->getRy(), $arc->getRx(), 'rx stays the major radius');
        $this->assertNotEqualsWithDelta(20.0, $arc->getRx(), 0.1, 'skew changes the radii');
        $this->assertNotEqualsWithDelta(0.0, $arc->getXAxisRotation(), 0.1, 'skew turns the ellipse');
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
