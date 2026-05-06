<?php

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Geometry\Transformation;
use Atelier\Svg\Path\PathBuilder;
use Atelier\Svg\Path\PathParser;
use Atelier\Svg\Path\PathUtils;
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

#[CoversClass(PathUtils::class)]
final class PathUtilsTest extends TestCase
{
    public function testTranslate(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(10, 10)
            ->toData();

        $translated = PathUtils::translate($path, 5, 5);

        $segments = $translated->getSegments();
        $this->assertCount(2, $segments);

        // First segment should be translated
        $firstPoint = $segments[0]->getTargetPoint();
        $this->assertNotNull($firstPoint);
        $this->assertEquals(5, $firstPoint->x);
        $this->assertEquals(5, $firstPoint->y);
    }

    public function testScale(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(10, 10)
            ->toData();

        $scaled = PathUtils::scale($path, 2);

        $segments = $scaled->getSegments();
        $secondPoint = $segments[1]->getTargetPoint();
        $this->assertNotNull($secondPoint);

        $this->assertEquals(20, $secondPoint->x);
        $this->assertEquals(20, $secondPoint->y);
    }

    public function testRotate(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(10, 0)
            ->toData();

        $rotated = PathUtils::rotate($path, 90, 0, 0);

        $segments = $rotated->getSegments();
        $secondPoint = $segments[1]->getTargetPoint();
        $this->assertNotNull($secondPoint);

        // After 90 degree rotation, (10, 0) should be approximately (0, 10)
        $this->assertEquals(0, round($secondPoint->x));
        $this->assertEquals(10, round($secondPoint->y));
    }

    public function testTransform(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(10, 10)
            ->toData();

        $matrix = Transformation::translate(5, 5);
        $transformed = PathUtils::transform($path, $matrix);

        $segments = $transformed->getSegments();
        $firstPoint = $segments[0]->getTargetPoint();
        $this->assertNotNull($firstPoint);

        $this->assertEquals(5, $firstPoint->x);
        $this->assertEquals(5, $firstPoint->y);
    }

    public function testToAbsolute(): void
    {
        $path = PathBuilder::new()
            ->moveTo(10, 10)
            ->lineTo(20, 20, relative: true)
            ->toData();

        $absolute = PathUtils::toAbsolute($path);

        $segments = $absolute->getSegments();

        // Check that commands are now uppercase (absolute)
        $this->assertEquals('L', $segments[1]->getCommand());
    }

    public function testToRelative(): void
    {
        $path = PathBuilder::new()
            ->moveTo(10, 10)
            ->lineTo(30, 30, relative: false)
            ->toData();

        $relative = PathUtils::toRelative($path);

        $segments = $relative->getSegments();

        // Check that commands are now lowercase (relative)
        $this->assertEquals('l', $segments[1]->getCommand());
    }

    public function testToAbsoluteWithRelativeCommands(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('m 10 20 l 30 40');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);

        // Relative moveto 'm 10 20' from origin (0,0) → absolute M 10 20
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertEquals('M', $segments[0]->getCommand());
        $this->assertEquals(10.0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(20.0, $segments[0]->getTargetPoint()->y);

        // Relative lineto 'l 30 40' from (10,20) → absolute L 40 60
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertEquals('L', $segments[1]->getCommand());
        $this->assertEquals(40.0, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(60.0, $segments[1]->getTargetPoint()->y);
    }

    public function testToAbsoluteWithAlreadyAbsoluteCommands(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 10 20 L 40 60');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);

        $this->assertEquals('M', $segments[0]->getCommand());
        $this->assertEquals(10.0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(20.0, $segments[0]->getTargetPoint()->y);

        $this->assertEquals('L', $segments[1]->getCommand());
        $this->assertEquals(40.0, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(60.0, $segments[1]->getTargetPoint()->y);
    }

    public function testToRelativeWithAbsoluteCommands(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 10 20 L 40 60');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);

        // Absolute M 10 20 from origin (0,0) → relative m 10 20
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertEquals('m', $segments[0]->getCommand());
        $this->assertEquals(10.0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(20.0, $segments[0]->getTargetPoint()->y);

        // Absolute L 40 60 from (10,20) → relative l 30 40
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertEquals('l', $segments[1]->getCommand());
        $this->assertEquals(30.0, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(40.0, $segments[1]->getTargetPoint()->y);
    }

    public function testToRelativeWithAlreadyRelativeCommands(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('m 10 20 l 30 40');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);

        $this->assertEquals('m', $segments[0]->getCommand());
        $this->assertEquals(10.0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(20.0, $segments[0]->getTargetPoint()->y);

        $this->assertEquals('l', $segments[1]->getCommand());
        $this->assertEquals(30.0, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(40.0, $segments[1]->getTargetPoint()->y);
    }

    public function testToAbsolutePreservesNonLineSegments(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 C 10 10 20 20 30 30');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        $this->assertEquals('C', $segments[1]->getCommand());
    }

    public function testToRelativeConvertsCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 C 10 10 20 20 30 30');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        // CurveTo is now properly converted to relative
        $this->assertEquals('c', $segments[1]->getCommand());
    }

    public function testToAbsoluteConvertsAllSegmentTypes(): void
    {
        $parser = new PathParser();
        // Mix of relative commands: moveto, lineto, h, v, curveto, arc, close
        $data = $parser->parse('m 10 20 l 5 5 h 10 v 15 c 1 2 3 4 5 6 a 5 5 0 0 1 10 0 z');

        $absolute = PathUtils::toAbsolute($data);
        $result = $absolute->toString();

        // After converting to absolute, all commands should be uppercase (except Z)
        $segments = $absolute->getSegments();
        foreach ($segments as $segment) {
            if ($segment instanceof ClosePath) {
                continue;
            }
            $this->assertFalse($segment->isRelative(), 'Segment '.$segment->getCommand().' should be absolute');
        }
    }

    public function testToRelativeConvertsAllSegmentTypes(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 10 20 L 15 25 H 25 V 40 C 26 42 28 44 30 46 A 5 5 0 0 1 40 46 Z');

        $relative = PathUtils::toRelative($data);

        $segments = $relative->getSegments();
        foreach ($segments as $segment) {
            if ($segment instanceof ClosePath) {
                continue;
            }
            $this->assertTrue($segment->isRelative(), 'Segment '.$segment->getCommand().' should be relative');
        }
    }

    public function testToAbsoluteRoundTrip(): void
    {
        $parser = new PathParser();
        // Start with absolute, convert to relative, convert back to absolute
        $original = $parser->parse('M 10 20 L 30 40 C 35 45 40 50 50 60 Z');

        $relative = PathUtils::toRelative($original);
        $backToAbsolute = PathUtils::toAbsolute($relative);
        $segments = $backToAbsolute->getSegments();

        // The endpoint of the CurveTo should be 50,60
        $curveTo = $segments[2];
        $this->assertInstanceOf(CurveTo::class, $curveTo);
        $this->assertEqualsWithDelta(50.0, $curveTo->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(60.0, $curveTo->getTargetPoint()->y, 0.001);
    }

    public function testSimplify(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(10, 10)
            ->toData();

        $simplified = PathUtils::simplify($path, 1.0);

        // For now, simplify returns the path as-is (TODO in implementation)
        $this->assertInstanceOf(\Atelier\Svg\Path\Data::class, $simplified);
    }

    public function testTransformWithCurveToSegment(): void
    {
        // CurveTo is not MoveTo or LineTo, so it hits the fallback in transformSegment
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 C 10 20 30 40 50 50');

        $matrix = Transformation::translate(5, 5);
        $transformed = PathUtils::transform($data, $matrix);

        $segments = $transformed->getSegments();
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);

        // Verify the curve was actually transformed
        $curveTarget = $segments[1]->getTargetPoint();
        $this->assertEqualsWithDelta(55, $curveTarget->x, 0.001);
        $this->assertEqualsWithDelta(55, $curveTarget->y, 0.001);
    }

    public function testTransformWithArcToSegment(): void
    {
        // ArcTo is not MoveTo or LineTo, so it hits the fallback in transformSegment
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 A 25 25 0 0 1 50 50');

        $matrix = Transformation::translate(10, 10);
        $transformed = PathUtils::transform($data, $matrix);

        $segments = $transformed->getSegments();
        $this->assertCount(2, $segments);
        // The segment should be transformed
        $targetPoint = $segments[1]->getTargetPoint();
        $this->assertNotNull($targetPoint);
    }

    public function testToAbsoluteWithAlreadyAbsoluteHorizontalLineTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 H 10');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(HorizontalLineTo::class, $segments[1]);
        $this->assertSame('H', $segments[1]->getCommand());
    }

    public function testToAbsoluteWithAlreadyAbsoluteVerticalLineTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 V 20');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(VerticalLineTo::class, $segments[1]);
        $this->assertSame('V', $segments[1]->getCommand());
    }

    public function testToAbsoluteWithAlreadyAbsoluteSmoothCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 S 10 10 20 20');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(SmoothCurveTo::class, $segments[1]);
        $this->assertSame('S', $segments[1]->getCommand());
    }

    public function testToAbsoluteWithAlreadyAbsoluteQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 Q 10 10 20 20');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(QuadraticCurveTo::class, $segments[1]);
        $this->assertSame('Q', $segments[1]->getCommand());
    }

    public function testToAbsoluteWithAlreadyAbsoluteSmoothQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 Q 10 10 20 20 T 30 30');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(3, $segments);
        $this->assertInstanceOf(SmoothQuadraticCurveTo::class, $segments[2]);
        $this->assertSame('T', $segments[2]->getCommand());
    }

    public function testToAbsoluteWithAlreadyAbsoluteArcTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 A 25 25 0 0 1 50 50');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(ArcTo::class, $segments[1]);
        $this->assertSame('A', $segments[1]->getCommand());
    }

    public function testToRelativeWithAlreadyRelativeHorizontalLineTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('m 10 20 h 10');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(HorizontalLineTo::class, $segments[1]);
        $this->assertSame('h', $segments[1]->getCommand());
    }

    public function testToRelativeWithAlreadyRelativeVerticalLineTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('m 10 20 v 15');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(VerticalLineTo::class, $segments[1]);
        $this->assertSame('v', $segments[1]->getCommand());
    }

    public function testToRelativeWithAlreadyRelativeSmoothCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('m 10 20 s 5 5 10 10');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(SmoothCurveTo::class, $segments[1]);
        $this->assertSame('s', $segments[1]->getCommand());
    }

    public function testToRelativeWithAlreadyRelativeQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('m 10 20 q 5 5 10 10');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(QuadraticCurveTo::class, $segments[1]);
        $this->assertSame('q', $segments[1]->getCommand());
    }

    public function testToRelativeWithAlreadyRelativeSmoothQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('m 10 20 q 5 5 10 10 t 10 10');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(3, $segments);
        $this->assertInstanceOf(SmoothQuadraticCurveTo::class, $segments[2]);
        $this->assertSame('t', $segments[2]->getCommand());
    }

    public function testToRelativeWithAlreadyRelativeArcTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('m 10 20 a 5 5 0 0 1 10 0');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(ArcTo::class, $segments[1]);
        $this->assertSame('a', $segments[1]->getCommand());
    }

    public function testToRelativeWithAlreadyRelativeCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('m 10 20 c 1 2 3 4 5 6');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        $this->assertSame('c', $segments[1]->getCommand());
    }

    public function testToAbsoluteConvertsRelativeSmoothCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 10 20 s 5 5 10 10');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(SmoothCurveTo::class, $segments[1]);
        $this->assertSame('S', $segments[1]->getCommand());
        $this->assertEqualsWithDelta(15.0, $segments[1]->getControlPoint2()->x, 0.001);
        $this->assertEqualsWithDelta(25.0, $segments[1]->getControlPoint2()->y, 0.001);
        $this->assertEqualsWithDelta(20.0, $segments[1]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(30.0, $segments[1]->getTargetPoint()->y, 0.001);
    }

    public function testToAbsoluteConvertsRelativeQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 10 20 q 5 5 10 10');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(QuadraticCurveTo::class, $segments[1]);
        $this->assertSame('Q', $segments[1]->getCommand());
        $this->assertEqualsWithDelta(15.0, $segments[1]->getControlPoint()->x, 0.001);
        $this->assertEqualsWithDelta(25.0, $segments[1]->getControlPoint()->y, 0.001);
        $this->assertEqualsWithDelta(20.0, $segments[1]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(30.0, $segments[1]->getTargetPoint()->y, 0.001);
    }

    public function testToAbsoluteConvertsRelativeSmoothQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 10 20 t 5 5');

        $absolute = PathUtils::toAbsolute($data);
        $segments = $absolute->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(SmoothQuadraticCurveTo::class, $segments[1]);
        $this->assertSame('T', $segments[1]->getCommand());
        $this->assertEqualsWithDelta(15.0, $segments[1]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(25.0, $segments[1]->getTargetPoint()->y, 0.001);
    }

    public function testToRelativeConvertsAbsoluteSmoothCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 10 20 S 20 30 30 40');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(SmoothCurveTo::class, $segments[1]);
        $this->assertSame('s', $segments[1]->getCommand());
        $this->assertEqualsWithDelta(10.0, $segments[1]->getControlPoint2()->x, 0.001);
        $this->assertEqualsWithDelta(10.0, $segments[1]->getControlPoint2()->y, 0.001);
        $this->assertEqualsWithDelta(20.0, $segments[1]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(20.0, $segments[1]->getTargetPoint()->y, 0.001);
    }

    public function testToRelativeConvertsAbsoluteQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 10 20 Q 20 30 30 40');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(QuadraticCurveTo::class, $segments[1]);
        $this->assertSame('q', $segments[1]->getCommand());
        $this->assertEqualsWithDelta(10.0, $segments[1]->getControlPoint()->x, 0.001);
        $this->assertEqualsWithDelta(10.0, $segments[1]->getControlPoint()->y, 0.001);
        $this->assertEqualsWithDelta(20.0, $segments[1]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(20.0, $segments[1]->getTargetPoint()->y, 0.001);
    }

    public function testToRelativeConvertsAbsoluteSmoothQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 10 20 T 20 30');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(SmoothQuadraticCurveTo::class, $segments[1]);
        $this->assertSame('t', $segments[1]->getCommand());
        $this->assertEqualsWithDelta(10.0, $segments[1]->getTargetPoint()->x, 0.001);
        $this->assertEqualsWithDelta(10.0, $segments[1]->getTargetPoint()->y, 0.001);
    }
}
