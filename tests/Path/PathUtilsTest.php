<?php

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Geometry\Transformation;
use Atelier\Svg\Path\PathBuilder;
use Atelier\Svg\Path\PathParser;
use Atelier\Svg\Path\PathUtils;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
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

    public function testToRelativePreservesNonLineSegments(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 C 10 10 20 20 30 30');

        $relative = PathUtils::toRelative($data);
        $segments = $relative->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        // CurveTo is kept as-is (not converted)
        $this->assertEquals('C', $segments[1]->getCommand());
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
}
