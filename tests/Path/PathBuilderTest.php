<?php

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Path\PathBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathBuilder::class)]
final class PathBuilderTest extends TestCase
{
    public function testNew(): void
    {
        $builder = PathBuilder::new();
        $this->assertInstanceOf(PathBuilder::class, $builder);
    }

    public function testStartAt(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $this->assertEquals('M10,20', $builder->getPathData());
    }

    public function testMoveTo(): void
    {
        $builder = PathBuilder::new();
        $builder->moveTo(10, 20);
        $this->assertEquals('M10,20', $builder->getPathData());

        $builder = PathBuilder::new();
        $builder->moveTo(10, 20, true);
        $this->assertEquals('m10,20', $builder->getPathData());
    }

    public function testLineTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->lineTo(30, 40);
        $this->assertEquals('M10,20L30,40', $builder->getPathData());

        $builder = PathBuilder::startAt(10, 20);
        $builder->lineTo(30, 40, true);
        $this->assertEquals('M10,20l30,40', $builder->getPathData());
    }

    public function testLineToWithoutStartingPoint(): void
    {
        $this->expectException(\LogicException::class);
        $builder = PathBuilder::new();
        $builder->lineTo(30, 40);
    }

    public function testClosePath(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->closePath();
        $this->assertEquals('M10,20Z', $builder->getPathData());
    }

    public function testGetPathData(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->lineTo(30, 40);
        $builder->closePath();
        $this->assertEquals('M10,20L30,40Z', $builder->getPathData());
    }

    public function testChaining(): void
    {
        $builder = PathBuilder::startAt(10, 20)
            ->lineTo(30, 40)
            ->closePath();
        $this->assertEquals('M10,20L30,40Z', $builder->getPathData());
    }

    public function testCurveTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->curveTo(20, 30, 40, 50, 60, 70);
        $this->assertEquals('M10,20C20,30 40,50 60,70', $builder->getPathData());
    }

    public function testCurveToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->curveTo(20, 30, 40, 50, 60, 70, true);
        $this->assertEquals('M10,20c20,30 40,50 60,70', $builder->getPathData());
    }

    public function testCurveToWithoutStartingPoint(): void
    {
        $this->expectException(\LogicException::class);
        $builder = PathBuilder::new();
        $builder->curveTo(20, 30, 40, 50, 60, 70);
    }

    public function testQuadraticCurveTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->quadraticCurveTo(30, 40, 50, 60);
        $this->assertEquals('M10,20Q30,40 50,60', $builder->getPathData());
    }

    public function testQuadraticCurveToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->quadraticCurveTo(30, 40, 50, 60, true);
        $this->assertEquals('M10,20q30,40 50,60', $builder->getPathData());
    }

    public function testQuadraticCurveToWithoutStartingPoint(): void
    {
        $this->expectException(\LogicException::class);
        $builder = PathBuilder::new();
        $builder->quadraticCurveTo(30, 40, 50, 60);
    }

    public function testArcTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->arcTo(25, 25, 0, false, true, 50, 60);
        $this->assertEquals('M10,20A25,25 0 0,1 50,60', $builder->getPathData());
    }

    public function testArcToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->arcTo(25, 25, 45, true, false, 50, 60, true);
        $this->assertEquals('M10,20a25,25 45 1,0 50,60', $builder->getPathData());
    }

    public function testArcToWithoutStartingPoint(): void
    {
        $this->expectException(\LogicException::class);
        $builder = PathBuilder::new();
        $builder->arcTo(25, 25, 0, false, true, 50, 60);
    }

    public function testHorizontalLineTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->horizontalLineTo(50);
        $this->assertEquals('M10,20H50', $builder->getPathData());
    }

    public function testHorizontalLineToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->horizontalLineTo(30, true);
        $this->assertEquals('M10,20h30', $builder->getPathData());
    }

    public function testHorizontalLineToWithoutStartingPoint(): void
    {
        $this->expectException(\LogicException::class);
        $builder = PathBuilder::new();
        $builder->horizontalLineTo(50);
    }

    public function testVerticalLineTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->verticalLineTo(50);
        $this->assertEquals('M10,20V50', $builder->getPathData());
    }

    public function testVerticalLineToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->verticalLineTo(30, true);
        $this->assertEquals('M10,20v30', $builder->getPathData());
    }

    public function testVerticalLineToWithoutStartingPoint(): void
    {
        $this->expectException(\LogicException::class);
        $builder = PathBuilder::new();
        $builder->verticalLineTo(50);
    }

    public function testSmoothCurveTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->smoothCurveTo(40, 50, 60, 70);
        $this->assertEquals('M10,20S40,50 60,70', $builder->getPathData());
    }

    public function testSmoothCurveToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->smoothCurveTo(40, 50, 60, 70, true);
        $this->assertEquals('M10,20s40,50 60,70', $builder->getPathData());
    }

    public function testSmoothCurveToWithoutStartingPoint(): void
    {
        $this->expectException(\LogicException::class);
        $builder = PathBuilder::new();
        $builder->smoothCurveTo(40, 50, 60, 70);
    }

    public function testSmoothQuadraticCurveTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->smoothQuadraticCurveTo(50, 60);
        $this->assertEquals('M10,20T50,60', $builder->getPathData());
    }

    public function testSmoothQuadraticCurveToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->smoothQuadraticCurveTo(50, 60, true);
        $this->assertEquals('M10,20t50,60', $builder->getPathData());
    }

    public function testSmoothQuadraticCurveToWithoutStartingPoint(): void
    {
        $this->expectException(\LogicException::class);
        $builder = PathBuilder::new();
        $builder->smoothQuadraticCurveTo(50, 60);
    }

    public function testComplexPath(): void
    {
        $builder = PathBuilder::startAt(10, 20)
            ->lineTo(30, 40)
            ->curveTo(40, 50, 60, 70, 80, 90)
            ->quadraticCurveTo(100, 110, 120, 130)
            ->arcTo(25, 25, 0, false, true, 150, 160)
            ->horizontalLineTo(200)
            ->verticalLineTo(250)
            ->smoothCurveTo(220, 260, 240, 270)
            ->smoothQuadraticCurveTo(280, 290)
            ->closePath();

        $expected = 'M10,20L30,40C40,50 60,70 80,90Q100,110 120,130A25,25 0 0,1 150,160H200V250S220,260 240,270T280,290Z';
        $this->assertEquals($expected, $builder->getPathData());
    }

    public function testRelativeCommands(): void
    {
        $builder = PathBuilder::startAt(10, 20, true)
            ->lineTo(30, 40, true)
            ->curveTo(10, 10, 20, 20, 30, 30, true)
            ->quadraticCurveTo(15, 15, 25, 25, true)
            ->arcTo(20, 20, 0, false, true, 30, 30, true)
            ->horizontalLineTo(50, true)
            ->verticalLineTo(50, true)
            ->smoothCurveTo(20, 20, 30, 30, true)
            ->smoothQuadraticCurveTo(25, 25, true);

        $expected = 'm10,20l30,40c10,10 20,20 30,30q15,15 25,25a20,20 0 0,1 30,30h50v50s20,20 30,30t25,25';
        $this->assertEquals($expected, $builder->getPathData());
    }

    public function testCloseAlias(): void
    {
        $builder = PathBuilder::startAt(0, 0)
            ->lineTo(100, 0)
            ->lineTo(100, 100)
            ->close();

        $this->assertStringEndsWith('Z', $builder->getPathData());
    }

    public function testToData(): void
    {
        $builder = PathBuilder::startAt(10, 20)
            ->lineTo(30, 40);

        $data = $builder->toData();
        $this->assertInstanceOf(\Atelier\Svg\Path\Data::class, $data);
        $this->assertSame('M10,20L30,40', $data->toString());
    }

    public function testToPathData(): void
    {
        $builder = PathBuilder::startAt(10, 20);

        $data = $builder->toPathData();
        $this->assertInstanceOf(\Atelier\Svg\Path\Data::class, $data);
        $this->assertSame('M10,20', $data->toString());
    }

    public function testToPath(): void
    {
        $builder = PathBuilder::startAt(0, 0)
            ->lineTo(100, 100);

        $path = $builder->toPath();
        $this->assertInstanceOf(\Atelier\Svg\Path\Path::class, $path);
    }

    public function testAnalyze(): void
    {
        $builder = PathBuilder::startAt(0, 0)
            ->lineTo(100, 0);

        $analyzer = $builder->analyze();
        $this->assertInstanceOf(\Atelier\Svg\Path\PathAnalyzer::class, $analyzer);
    }

    public function testGetLength(): void
    {
        $builder = PathBuilder::startAt(0, 0)
            ->lineTo(100, 0);

        $length = $builder->getLength();
        $this->assertEqualsWithDelta(100.0, $length, 0.1);
    }

    public function testGetBoundingBox(): void
    {
        $builder = PathBuilder::startAt(0, 0)
            ->lineTo(100, 50);

        $bbox = $builder->getBoundingBox();
        $this->assertInstanceOf(\Atelier\Svg\Geometry\BoundingBox::class, $bbox);
    }

    public function testGetPointAtLength(): void
    {
        $builder = PathBuilder::startAt(0, 0)
            ->lineTo(100, 0);

        $point = $builder->getPointAtLength(50);
        $this->assertNotNull($point);
    }

    public function testAddSegment(): void
    {
        $builder = PathBuilder::new();

        $segment = new \Atelier\Svg\Path\Segment\MoveTo('M', new \Atelier\Svg\Geometry\Point(10, 20));
        $result = $builder->addSegment($segment);

        $this->assertSame($builder, $result);
        $this->assertSame('M10,20', $builder->getPathData());
    }

    public function testAddSegmentWithNullTargetPoint(): void
    {
        $builder = PathBuilder::startAt(0, 0)
            ->lineTo(100, 100);

        $closePath = new \Atelier\Svg\Path\Segment\ClosePath('Z');
        $builder->addSegment($closePath);

        $this->assertStringEndsWith('Z', $builder->getPathData());
    }
}
