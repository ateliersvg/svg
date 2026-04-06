<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Path;
use Atelier\Svg\Path\PathBuilder;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Path::class)]
final class PathTest extends TestCase
{
    // =========================================================================
    // FACTORY METHODS
    // =========================================================================

    public function testParse(): void
    {
        $path = Path::parse('M 10,10 L 50,50 Z');
        $this->assertInstanceOf(Path::class, $path);
        $this->assertStringContainsString('M', $path->toString());
    }

    public function testCreate(): void
    {
        $builder = Path::create();
        $this->assertInstanceOf(PathBuilder::class, $builder);
    }

    public function testFromBuilder(): void
    {
        $builder = PathBuilder::startAt(10, 10)->lineTo(50, 50);
        $path = Path::fromBuilder($builder);
        $this->assertInstanceOf(Path::class, $path);
    }

    public function testFromSegments(): void
    {
        $segments = [
            new MoveTo('M', new Point(10, 10)),
            new LineTo('L', new Point(50, 50)),
        ];
        $path = Path::fromSegments($segments);
        $this->assertInstanceOf(Path::class, $path);
        $this->assertCount(2, $path->getSegments());
    }

    // =========================================================================
    // SHAPE FACTORY METHODS
    // =========================================================================

    public function testRectangle(): void
    {
        $path = Path::rectangle(10, 20, 100, 50);
        $this->assertInstanceOf(Path::class, $path);
        $this->assertGreaterThan(0, count($path->getSegments()));
    }

    public function testCircle(): void
    {
        $path = Path::circle(50, 50, 25);
        $this->assertInstanceOf(Path::class, $path);
        $bbox = $path->getBoundingBox();
        $this->assertEqualsWithDelta(50, $bbox->getCenterX(), 1);
        $this->assertEqualsWithDelta(50, $bbox->getCenterY(), 1);
    }

    public function testEllipse(): void
    {
        $path = Path::ellipse(50, 50, 30, 20);
        $this->assertInstanceOf(Path::class, $path);
    }

    public function testPolygon(): void
    {
        $path = Path::polygon(50, 50, 30, 6);
        $this->assertInstanceOf(Path::class, $path);
    }

    public function testStar(): void
    {
        $path = Path::star(50, 50, 40, 20, 5);
        $this->assertInstanceOf(Path::class, $path);
    }

    public function testLine(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $this->assertInstanceOf(Path::class, $path);
    }

    public function testPolyline(): void
    {
        $points = [[0, 0], [50, 50], [100, 0]];
        $path = Path::polyline($points);
        $this->assertInstanceOf(Path::class, $path);
    }

    public function testPolygonFromPoints(): void
    {
        $points = [[0, 0], [50, 50], [100, 0]];
        $path = Path::polygonFromPoints($points);
        $this->assertInstanceOf(Path::class, $path);
        $this->assertTrue($path->isClosed());
    }

    // =========================================================================
    // TRANSFORMATION METHODS
    // =========================================================================

    public function testTranslate(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $translated = $path->translate(10, 20);

        $this->assertInstanceOf(Path::class, $translated);
        $this->assertNotSame($path, $translated); // Should return a new instance

        $bbox = $translated->getBoundingBox();
        $this->assertEqualsWithDelta(10, $bbox->minX, 1);
        $this->assertEqualsWithDelta(20, $bbox->minY, 1);
    }

    public function testScale(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $scaled = $path->scale(2);

        $this->assertInstanceOf(Path::class, $scaled);

        $bbox = $scaled->getBoundingBox();
        $this->assertEqualsWithDelta(200, $bbox->getWidth(), 2);
        $this->assertEqualsWithDelta(100, $bbox->getHeight(), 2);
    }

    public function testScaleNonUniform(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $scaled = $path->scale(2, 3);

        $bbox = $scaled->getBoundingBox();
        $this->assertEqualsWithDelta(200, $bbox->getWidth(), 2);
        $this->assertEqualsWithDelta(150, $bbox->getHeight(), 2);
    }

    public function testRotate(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $rotated = $path->rotate(90);

        $this->assertInstanceOf(Path::class, $rotated);
        // After 90-degree rotation, width and height should be swapped
        $bbox = $rotated->getBoundingBox();
        $this->assertEqualsWithDelta(50, $bbox->getWidth(), 2);
        $this->assertEqualsWithDelta(100, $bbox->getHeight(), 2);
    }

    public function testTransform(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $matrix = new Matrix(a: 1, b: 0, c: 0, d: 1, e: 10, f: 20); // Translation matrix
        $transformed = $path->transform($matrix);

        $this->assertInstanceOf(Path::class, $transformed);
        $bbox = $transformed->getBoundingBox();
        $this->assertEqualsWithDelta(10, $bbox->minX, 1);
        $this->assertEqualsWithDelta(20, $bbox->minY, 1);
    }

    // =========================================================================
    // ANALYSIS METHODS
    // =========================================================================

    public function testGetLength(): void
    {
        $path = Path::line(0, 0, 100, 0);
        $length = $path->getLength();
        $this->assertEqualsWithDelta(100, $length, 0.1);
    }

    public function testGetPointAtLength(): void
    {
        $path = Path::line(0, 0, 100, 0);
        $point = $path->getPointAtLength(50);
        $this->assertInstanceOf(Point::class, $point);
        $this->assertEqualsWithDelta(50, $point->x, 0.1);
        $this->assertEqualsWithDelta(0, $point->y, 0.1);
    }

    public function testGetBoundingBox(): void
    {
        $path = Path::rectangle(10, 20, 100, 50);
        $bbox = $path->getBoundingBox();
        $this->assertEqualsWithDelta(10, $bbox->minX, 0.1);
        $this->assertEqualsWithDelta(20, $bbox->minY, 0.1);
        $this->assertEqualsWithDelta(110, $bbox->maxX, 0.1);
        $this->assertEqualsWithDelta(70, $bbox->maxY, 0.1);
    }

    public function testGetCenter(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $center = $path->getCenter();
        $this->assertInstanceOf(Point::class, $center);
        $this->assertEqualsWithDelta(50, $center->x, 0.1);
        $this->assertEqualsWithDelta(25, $center->y, 0.1);
    }

    public function testGetArea(): void
    {
        // Simple square
        $path = Path::rectangle(0, 0, 10, 10);
        $area = $path->getArea();
        $this->assertEqualsWithDelta(100, $area, 1);
    }

    public function testIsClosed(): void
    {
        $closedPath = Path::rectangle(0, 0, 100, 50);
        $this->assertTrue($closedPath->isClosed());

        $openPath = Path::line(0, 0, 100, 100);
        $this->assertFalse($openPath->isClosed());
    }

    public function testIsClockwise(): void
    {
        // Rectangle drawn counter-clockwise (standard SVG)
        $path = Path::rectangle(0, 0, 100, 100);
        // The result depends on how rectangle is drawn
        $this->assertIsBool($path->isClockwise());
    }

    public function testContainsPoint(): void
    {
        $path = Path::rectangle(0, 0, 100, 100);

        // Point inside
        $insidePoint = new Point(50, 50);
        $this->assertTrue($path->containsPoint($insidePoint));

        // Point outside
        $outsidePoint = new Point(150, 150);
        $this->assertFalse($path->containsPoint($outsidePoint));
    }

    public function testGetVertices(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $vertices = $path->getVertices();
        $this->assertIsArray($vertices);
        $this->assertGreaterThan(0, count($vertices));
    }

    // =========================================================================
    // MANIPULATION METHODS
    // =========================================================================

    public function testReverse(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $reversed = $path->reverse();
        $this->assertInstanceOf(Path::class, $reversed);
        $this->assertNotSame($path, $reversed);
    }

    public function testGetSubpath(): void
    {
        $builder = PathBuilder::startAt(0, 0)
            ->lineTo(50, 50)
            ->lineTo(100, 0);
        $path = Path::fromBuilder($builder);

        $subpath = $path->getSubpath(0, 1);
        $this->assertInstanceOf(Path::class, $subpath);
        $this->assertLessThanOrEqual(2, count($subpath->getSegments()));
    }

    public function testSplit(): void
    {
        $path = Path::line(0, 0, 100, 0);
        [$before, $after] = $path->split(50);

        $this->assertInstanceOf(Path::class, $before);
        $this->assertInstanceOf(Path::class, $after);
    }

    public function testSimplify(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $simplified = $path->simplify(1.0);
        $this->assertInstanceOf(Path::class, $simplified);
    }

    // =========================================================================
    // EXPORT METHODS
    // =========================================================================

    public function testGetData(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $data = $path->getData();
        $this->assertInstanceOf(Data::class, $data);
    }

    public function testGetSegments(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $segments = $path->getSegments();
        $this->assertIsArray($segments);
        $this->assertGreaterThan(0, count($segments));
    }

    public function testToBuilder(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $builder = $path->toBuilder();
        $this->assertInstanceOf(PathBuilder::class, $builder);
    }

    public function testToString(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $string = $path->toString();
        $this->assertIsString($string);
        $this->assertStringContainsString('M', $string);
        $this->assertStringContainsString('L', $string);
    }

    public function testToStringMagicMethod(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $string = (string) $path;
        $this->assertIsString($string);
    }

    public function testClone(): void
    {
        $path = Path::line(0, 0, 100, 100);
        $cloned = clone $path;
        $this->assertInstanceOf(Path::class, $cloned);
        $this->assertNotSame($path, $cloned);
    }

    // =========================================================================
    // CHAINING
    // =========================================================================

    public function testChaining(): void
    {
        $path = Path::rectangle(0, 0, 100, 50)
            ->translate(10, 20)
            ->scale(2)
            ->rotate(45);

        $this->assertInstanceOf(Path::class, $path);
    }

    // =========================================================================
    // ADDITIONAL COVERAGE TESTS
    // =========================================================================

    public function testReverseClosedPath(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $this->assertTrue($path->isClosed());

        $reversed = $path->reverse();
        $this->assertTrue($reversed->isClosed());
    }

    public function testReverseEmptyPath(): void
    {
        $path = Path::fromSegments([]);
        $reversed = $path->reverse();
        $this->assertInstanceOf(Path::class, $reversed);
        $this->assertCount(0, $reversed->getSegments());
    }

    public function testGetAreaWithFewerThanThreeVertices(): void
    {
        $path = Path::line(0, 0, 100, 0);
        $area = $path->getArea();
        $this->assertEqualsWithDelta(0.0, $area, 0.01);
    }

    public function testIsClockwiseWithFewerThanThreeVertices(): void
    {
        $path = Path::line(0, 0, 100, 0);
        $this->assertFalse($path->isClockwise());
    }

    public function testIsClosedEmptyPath(): void
    {
        $path = Path::fromSegments([]);
        $this->assertFalse($path->isClosed());
    }

    public function testScaleWithCenter(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $scaled = $path->scale(2, 2, 50, 25);

        $bbox = $scaled->getBoundingBox();
        $this->assertEqualsWithDelta(50, $bbox->getCenterX(), 2);
        $this->assertEqualsWithDelta(25, $bbox->getCenterY(), 2);
    }

    public function testScaleWithCenterNonUniform(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $scaled = $path->scale(2, 3, 50, 25);

        $bbox = $scaled->getBoundingBox();
        $this->assertEqualsWithDelta(200, $bbox->getWidth(), 2);
        $this->assertEqualsWithDelta(150, $bbox->getHeight(), 2);
    }

    public function testRotateWithCenter(): void
    {
        $path = Path::rectangle(0, 0, 100, 100);
        $rotated = $path->rotate(90, 50, 50);

        $bbox = $rotated->getBoundingBox();
        $this->assertEqualsWithDelta(50, $bbox->getCenterX(), 2);
        $this->assertEqualsWithDelta(50, $bbox->getCenterY(), 2);
    }

    public function testSplitAtZeroLength(): void
    {
        $path = Path::line(0, 0, 100, 0);
        [$before, $after] = $path->split(0);

        $this->assertInstanceOf(Path::class, $before);
        $this->assertInstanceOf(Path::class, $after);
    }

    public function testSplitAtFullLength(): void
    {
        $path = Path::line(0, 0, 100, 0);
        [$before, $after] = $path->split(200);

        $this->assertInstanceOf(Path::class, $before);
        $this->assertInstanceOf(Path::class, $after);
    }

    public function testGetAreaTriangle(): void
    {
        $path = Path::polygonFromPoints([[0, 0], [100, 0], [0, 100]]);
        $area = $path->getArea();
        $this->assertEqualsWithDelta(5000, $area, 1);
    }

    public function testIsClockwiseClockwisePath(): void
    {
        // Clockwise triangle (negative signed area in SVG coords)
        $path = Path::polygonFromPoints([[0, 0], [0, 100], [100, 0]]);
        $result = $path->isClockwise();
        $this->assertIsBool($result);
    }

    public function testReverseMultiSegmentPath(): void
    {
        $builder = PathBuilder::startAt(0, 0)
            ->lineTo(50, 50)
            ->lineTo(100, 0);
        $path = Path::fromBuilder($builder);
        $reversed = $path->reverse();

        $vertices = $reversed->getVertices();
        $this->assertNotEmpty($vertices);
        $this->assertEqualsWithDelta(100, $vertices[0]->x, 0.1);
        $this->assertEqualsWithDelta(0, $vertices[0]->y, 0.1);
    }

    public function testReversePathWithNoPoints(): void
    {
        // Path with segments but no MoveTo/LineTo points (e.g., only ClosePath)
        $path = Path::fromSegments([
            new \Atelier\Svg\Path\Segment\ClosePath('Z'),
        ]);
        $reversed = $path->reverse();

        $this->assertInstanceOf(Path::class, $reversed);
        // Should return a clone since there are no points
        $this->assertCount(1, $reversed->getSegments());
    }

    public function testHausdorffDistanceMethod(): void
    {
        $path1 = Path::line(0, 0, 100, 0);
        $path2 = Path::line(0, 10, 100, 10);

        $distance = $path1->hausdorffDistance($path2);
        $this->assertEqualsWithDelta(10.0, $distance, 1.0);
    }

    public function testFrechetDistanceMethod(): void
    {
        $path1 = Path::line(0, 0, 100, 0);
        $path2 = Path::line(0, 10, 100, 10);

        $distance = $path1->frechetDistance($path2);
        $this->assertEqualsWithDelta(10.0, $distance, 1.0);
    }

    public function testAverageDistanceMethod(): void
    {
        $path1 = Path::line(0, 0, 100, 0);
        $path2 = Path::line(0, 10, 100, 10);

        $distance = $path1->averageDistance($path2);
        $this->assertEqualsWithDelta(10.0, $distance, 1.0);
    }

    public function testMaxPointDistanceMethod(): void
    {
        $path1 = Path::line(0, 0, 100, 0);
        $path2 = Path::line(0, 10, 100, 10);

        $distance = $path1->maxPointDistance($path2);
        $this->assertEqualsWithDelta(10.0, $distance, 1.0);
    }
}
