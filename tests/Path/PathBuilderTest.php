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
        $this->assertEquals('M 10,20', $builder->getPathData());
    }

    public function testMoveTo(): void
    {
        $builder = PathBuilder::new();
        $builder->moveTo(10, 20);
        $this->assertEquals('M 10,20', $builder->getPathData());

        $builder = PathBuilder::new();
        $builder->moveTo(10, 20, true);
        $this->assertEquals('m 10,20', $builder->getPathData());
    }

    public function testLineTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->lineTo(30, 40);
        $this->assertEquals('M 10,20 L 30,40', $builder->getPathData());

        $builder = PathBuilder::startAt(10, 20);
        $builder->lineTo(30, 40, true);
        $this->assertEquals('M 10,20 l 30,40', $builder->getPathData());
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
        $this->assertEquals('M 10,20 Z', $builder->getPathData());
    }

    public function testGetPathData(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->lineTo(30, 40);
        $builder->closePath();
        $this->assertEquals('M 10,20 L 30,40 Z', $builder->getPathData());
    }

    public function testChaining(): void
    {
        $builder = PathBuilder::startAt(10, 20)
            ->lineTo(30, 40)
            ->closePath();
        $this->assertEquals('M 10,20 L 30,40 Z', $builder->getPathData());
    }

    public function testCurveTo(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->curveTo(20, 30, 40, 50, 60, 70);
        $this->assertEquals('M 10,20 C 20,30 40,50 60,70', $builder->getPathData());
    }

    public function testCurveToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->curveTo(20, 30, 40, 50, 60, 70, true);
        $this->assertEquals('M 10,20 c 20,30 40,50 60,70', $builder->getPathData());
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
        $this->assertEquals('M 10,20 Q 30,40 50,60', $builder->getPathData());
    }

    public function testQuadraticCurveToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->quadraticCurveTo(30, 40, 50, 60, true);
        $this->assertEquals('M 10,20 q 30,40 50,60', $builder->getPathData());
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
        $this->assertEquals('M 10,20 A 25,25 0 0,1 50,60', $builder->getPathData());
    }

    public function testArcToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->arcTo(25, 25, 45, true, false, 50, 60, true);
        $this->assertEquals('M 10,20 a 25,25 45 1,0 50,60', $builder->getPathData());
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
        $this->assertEquals('M 10,20 H 50', $builder->getPathData());
    }

    public function testHorizontalLineToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->horizontalLineTo(30, true);
        $this->assertEquals('M 10,20 h 30', $builder->getPathData());
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
        $this->assertEquals('M 10,20 V 50', $builder->getPathData());
    }

    public function testVerticalLineToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->verticalLineTo(30, true);
        $this->assertEquals('M 10,20 v 30', $builder->getPathData());
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
        $this->assertEquals('M 10,20 S 40,50 60,70', $builder->getPathData());
    }

    public function testSmoothCurveToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->smoothCurveTo(40, 50, 60, 70, true);
        $this->assertEquals('M 10,20 s 40,50 60,70', $builder->getPathData());
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
        $this->assertEquals('M 10,20 T 50,60', $builder->getPathData());
    }

    public function testSmoothQuadraticCurveToRelative(): void
    {
        $builder = PathBuilder::startAt(10, 20);
        $builder->smoothQuadraticCurveTo(50, 60, true);
        $this->assertEquals('M 10,20 t 50,60', $builder->getPathData());
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

        $expected = 'M 10,20 L 30,40 C 40,50 60,70 80,90 Q 100,110 120,130 A 25,25 0 0,1 150,160 H 200 V 250 S 220,260 240,270 T 280,290 Z';
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

        $expected = 'm 10,20 l 30,40 c 10,10 20,20 30,30 q 15,15 25,25 a 20,20 0 0,1 30,30 h 50 v 50 s 20,20 30,30 t 25,25';
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
        $this->assertSame('M 10,20 L 30,40', $data->toString());
    }

    public function testToPathData(): void
    {
        $builder = PathBuilder::startAt(10, 20);

        $data = $builder->toPathData();
        $this->assertInstanceOf(\Atelier\Svg\Path\Data::class, $data);
        $this->assertSame('M 10,20', $data->toString());
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
        $this->assertSame('M 10,20', $builder->getPathData());
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
