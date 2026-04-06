<?php

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Geometry\BoundingBox;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\PathAnalyzer;
use Atelier\Svg\Path\PathBuilder;
use Atelier\Svg\Path\PathParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathAnalyzer::class)]
final class PathAnalyzerTest extends TestCase
{
    public function testGetLengthSimplePath(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(10, 0)
            ->lineTo(10, 10)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        $this->assertEquals(20, $length);
    }

    public function testGetPointAtLength(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(10, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $point = $analyzer->getPointAtLength(5);

        $this->assertNotNull($point);
        $this->assertEquals(5, $point->x);
        $this->assertEquals(0, $point->y);
    }

    public function testGetPointAtLengthBeyondPath(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(10, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $point = $analyzer->getPointAtLength(100);

        $this->assertNull($point);
    }

    public function testGetBoundingBox(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(100, 0)
            ->lineTo(100, 50)
            ->lineTo(0, 50)
            ->closePath()
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $bbox = $analyzer->getBoundingBox();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(100, $bbox->maxX);
        $this->assertEquals(50, $bbox->maxY);
    }

    public function testGetVertices(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(10, 0)
            ->lineTo(10, 10)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $vertices = $analyzer->getVertices();

        $this->assertCount(3, $vertices);
        $this->assertInstanceOf(Point::class, $vertices[0]);
    }

    public function testGetCenter(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(100, 0)
            ->lineTo(100, 100)
            ->lineTo(0, 100)
            ->closePath()
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $center = $analyzer->getCenter();

        $this->assertEquals(50, $center->x);
        $this->assertEquals(50, $center->y);
    }

    public function testContainsPoint(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(100, 0)
            ->lineTo(100, 100)
            ->lineTo(0, 100)
            ->closePath()
            ->toData();

        $analyzer = new PathAnalyzer($path);

        $inside = new Point(50, 50);
        $this->assertTrue($analyzer->containsPoint($inside));

        $outside = new Point(150, 150);
        $this->assertFalse($analyzer->containsPoint($outside));
    }

    // --- getLength() tests for various segment types ---

    public function testGetLengthWithCubicBezier(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(10, 20, 30, 40, 50, 50)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        $this->assertGreaterThan(0, $length);
        // Cubic Bezier length should be longer than straight-line distance
        $straightLine = sqrt(50 * 50 + 50 * 50);
        $this->assertGreaterThan($straightLine, $length);
    }

    public function testGetLengthWithQuadraticBezier(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->quadraticCurveTo(50, 100, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        $this->assertGreaterThan(0, $length);
        $straightLine = 100.0;
        $this->assertGreaterThan($straightLine, $length);
    }

    public function testGetLengthWithSmoothCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 C 10 20 30 40 50 50 S 90 80 100 100');

        $analyzer = new PathAnalyzer($data);
        $length = $analyzer->getLength();

        $this->assertGreaterThan(0, $length);
    }

    public function testGetLengthWithSmoothQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 Q 50 100 100 0 T 200 0');

        $analyzer = new PathAnalyzer($data);
        $length = $analyzer->getLength();

        $this->assertGreaterThan(0, $length);
    }

    public function testGetLengthWithArc(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->arcTo(50, 50, 0, false, true, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        $this->assertGreaterThan(0, $length);
    }

    public function testGetLengthWithHorizontalAndVerticalLines(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->horizontalLineTo(100)
            ->verticalLineTo(50)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        $this->assertEqualsWithDelta(150, $length, 0.001);
    }

    // --- getPointAtLength() tests for various segment types ---

    public function testGetPointAtLengthWithCubicBezier(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(10, 20, 30, 40, 50, 50)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        $pointStart = $analyzer->getPointAtLength(0);
        $this->assertNotNull($pointStart);
        $this->assertEqualsWithDelta(0, $pointStart->x, 0.5);
        $this->assertEqualsWithDelta(0, $pointStart->y, 0.5);

        $pointMid = $analyzer->getPointAtLength($length / 2);
        $this->assertNotNull($pointMid);

        $pointEnd = $analyzer->getPointAtLength($length - 0.001);
        $this->assertNotNull($pointEnd);
        $this->assertEqualsWithDelta(50, $pointEnd->x, 1.0);
        $this->assertEqualsWithDelta(50, $pointEnd->y, 1.0);
    }

    public function testGetPointAtLengthWithArc(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->arcTo(50, 50, 0, false, true, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        $point = $analyzer->getPointAtLength($length / 2);
        $this->assertNotNull($point);
        $this->assertInstanceOf(Point::class, $point);
    }

    public function testGetPointAtLengthWithHorizontalLine(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->horizontalLineTo(100)
            ->toData();

        $analyzer = new PathAnalyzer($path);

        $point = $analyzer->getPointAtLength(50);
        $this->assertNotNull($point);
        $this->assertEqualsWithDelta(50, $point->x, 0.001);
        $this->assertEqualsWithDelta(0, $point->y, 0.001);
    }

    public function testGetPointAtLengthWithVerticalLine(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->verticalLineTo(100)
            ->toData();

        $analyzer = new PathAnalyzer($path);

        $point = $analyzer->getPointAtLength(75);
        $this->assertNotNull($point);
        $this->assertEqualsWithDelta(0, $point->x, 0.001);
        $this->assertEqualsWithDelta(75, $point->y, 0.001);
    }

    // --- getBoundingBox() tests ---

    public function testGetBoundingBoxWithCurves(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(0, 100, 100, 100, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $bbox = $analyzer->getBoundingBox();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertEqualsWithDelta(0, $bbox->minX, 1.0);
        $this->assertEqualsWithDelta(100, $bbox->maxX, 1.0);
        $this->assertGreaterThan(0, $bbox->maxY);
    }

    public function testGetBoundingBoxEmpty(): void
    {
        $data = new Data([]);

        $analyzer = new PathAnalyzer($data);
        $bbox = $analyzer->getBoundingBox();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    public function testGetBoundingBoxWithArc(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->arcTo(50, 30, 0, false, true, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $bbox = $analyzer->getBoundingBox();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertLessThanOrEqual(0, $bbox->minX);
        $this->assertGreaterThanOrEqual(100, $bbox->maxX);
    }

    public function testGetBoundingBoxWithSmoothCurves(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 C 10 20 30 40 50 50 S 90 80 100 100');

        $analyzer = new PathAnalyzer($data);
        $bbox = $analyzer->getBoundingBox();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertLessThanOrEqual(0, $bbox->minX);
        $this->assertGreaterThanOrEqual(100, $bbox->maxX);
        $this->assertLessThanOrEqual(0, $bbox->minY);
        $this->assertGreaterThanOrEqual(100, $bbox->maxY);
    }

    // --- getVertices() tests ---

    public function testGetVerticesWithCurves(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(10, 20, 30, 40, 50, 50)
            ->quadraticCurveTo(75, 100, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $vertices = $analyzer->getVertices();

        $this->assertNotEmpty($vertices);
        // MoveTo (1) + cubic samples (5) + quadratic samples (5) = 11
        $this->assertGreaterThan(3, count($vertices));
        foreach ($vertices as $vertex) {
            $this->assertInstanceOf(Point::class, $vertex);
        }
    }

    public function testGetVerticesWithArc(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->arcTo(50, 50, 0, false, true, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $vertices = $analyzer->getVertices();

        $this->assertNotEmpty($vertices);
        $this->assertGreaterThan(1, count($vertices));
        foreach ($vertices as $vertex) {
            $this->assertInstanceOf(Point::class, $vertex);
        }
    }

    public function testGetVerticesWithHorizontalAndVerticalLines(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->horizontalLineTo(100)
            ->verticalLineTo(50)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $vertices = $analyzer->getVertices();

        $this->assertCount(3, $vertices);
        $this->assertEqualsWithDelta(0, $vertices[0]->x, 0.001);
        $this->assertEqualsWithDelta(0, $vertices[0]->y, 0.001);
        $this->assertEqualsWithDelta(100, $vertices[1]->x, 0.001);
        $this->assertEqualsWithDelta(0, $vertices[1]->y, 0.001);
        $this->assertEqualsWithDelta(100, $vertices[2]->x, 0.001);
        $this->assertEqualsWithDelta(50, $vertices[2]->y, 0.001);
    }

    // --- getPointAtLength() for quadratic and smooth curves ---

    public function testGetPointAtLengthWithQuadraticBezier(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->quadraticCurveTo(50, 100, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $length = $analyzer->getLength();

        $point = $analyzer->getPointAtLength($length / 2);
        $this->assertNotNull($point);
        $this->assertInstanceOf(Point::class, $point);
        $this->assertGreaterThan(20, $point->x);
        $this->assertLessThan(80, $point->x);
    }

    public function testGetPointAtLengthWithSmoothCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 C 10 20 30 40 50 50 S 90 80 100 100');

        $analyzer = new PathAnalyzer($data);
        $length = $analyzer->getLength();

        $point = $analyzer->getPointAtLength($length - 1);
        $this->assertNotNull($point);
        $this->assertInstanceOf(Point::class, $point);
    }

    public function testGetPointAtLengthWithSmoothQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 Q 50 100 100 0 T 200 0');

        $analyzer = new PathAnalyzer($data);
        $length = $analyzer->getLength();

        $point = $analyzer->getPointAtLength($length - 1);
        $this->assertNotNull($point);
        $this->assertInstanceOf(Point::class, $point);
    }

    // --- getBoundingBox() for additional segment types ---

    public function testGetBoundingBoxWithQuadraticCurves(): void
    {
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->quadraticCurveTo(50, 100, 100, 0)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $bbox = $analyzer->getBoundingBox();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertEqualsWithDelta(0, $bbox->minX, 1.0);
        $this->assertEqualsWithDelta(100, $bbox->maxX, 1.0);
        $this->assertGreaterThan(0, $bbox->maxY);
    }

    public function testGetBoundingBoxWithSmoothQuadraticCurves(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 Q 50 100 100 0 T 200 0');

        $analyzer = new PathAnalyzer($data);
        $bbox = $analyzer->getBoundingBox();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertGreaterThanOrEqual(200, $bbox->maxX);
    }

    public function testGetBoundingBoxWithHorizontalAndVerticalLines(): void
    {
        $path = PathBuilder::new()
            ->moveTo(10, 10)
            ->horizontalLineTo(100)
            ->verticalLineTo(80)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $bbox = $analyzer->getBoundingBox();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertEqualsWithDelta(10, $bbox->minX, 0.001);
        $this->assertEqualsWithDelta(100, $bbox->maxX, 0.001);
        $this->assertEqualsWithDelta(10, $bbox->minY, 0.001);
        $this->assertEqualsWithDelta(80, $bbox->maxY, 0.001);
    }

    // --- getVertices() for smooth curves (else branch) ---

    public function testGetVerticesWithSmoothCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 C 10 20 30 40 50 50 S 90 80 100 100');

        $analyzer = new PathAnalyzer($data);
        $vertices = $analyzer->getVertices();

        $this->assertNotEmpty($vertices);
        foreach ($vertices as $vertex) {
            $this->assertInstanceOf(Point::class, $vertex);
        }
    }

    public function testGetVerticesWithSmoothQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 Q 50 100 100 0 T 200 0');

        $analyzer = new PathAnalyzer($data);
        $vertices = $analyzer->getVertices();

        $this->assertNotEmpty($vertices);
        foreach ($vertices as $vertex) {
            $this->assertInstanceOf(Point::class, $vertex);
        }
        $lastVertex = end($vertices);
        $this->assertEqualsWithDelta(200, $lastVertex->x, 0.001);
        $this->assertEqualsWithDelta(0, $lastVertex->y, 0.001);
    }

    public function testGetPointAtLengthWithHorizontalLineAccumulation(): void
    {
        // H segment that does NOT contain the target length: accumulates and continues
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->horizontalLineTo(10)
            ->lineTo(10, 50)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        // Request a point beyond the H segment (length 10) but within the L segment
        $point = $analyzer->getPointAtLength(30);
        $this->assertNotNull($point);
        $this->assertEqualsWithDelta(10, $point->x, 0.001);
        $this->assertEqualsWithDelta(20, $point->y, 0.001);
    }

    public function testGetPointAtLengthWithVerticalLineAccumulation(): void
    {
        // V segment that does NOT contain the target length: accumulates and continues
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->verticalLineTo(10)
            ->lineTo(50, 10)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        // Request a point beyond the V segment (length 10) but within the L segment
        $point = $analyzer->getPointAtLength(30);
        $this->assertNotNull($point);
        $this->assertEqualsWithDelta(20, $point->x, 0.001);
        $this->assertEqualsWithDelta(10, $point->y, 0.001);
    }

    public function testGetPointAtLengthWithArcAccumulation(): void
    {
        // Arc segment that does NOT contain the target length: accumulates and continues
        $path = PathBuilder::new()
            ->moveTo(0, 0)
            ->arcTo(10, 10, 0, false, true, 20, 0)
            ->lineTo(20, 100)
            ->toData();

        $analyzer = new PathAnalyzer($path);
        $arcLength = (new PathAnalyzer(
            PathBuilder::new()->moveTo(0, 0)->arcTo(10, 10, 0, false, true, 20, 0)->toData()
        ))->getLength();

        // Request a point beyond the arc but within the line
        $point = $analyzer->getPointAtLength($arcLength + 10);
        $this->assertNotNull($point);
        $this->assertEqualsWithDelta(20, $point->x, 1.0);
    }

    public function testGetPointAtLengthWithSmoothQuadraticCurveToAccumulation(): void
    {
        // SmoothQuadraticCurveTo falls into the "else" branch of getPointAtLength
        $parser = new PathParser();
        $data = $parser->parse('M 0 0 Q 50 100 100 0 T 200 0 L 200 100');

        $analyzer = new PathAnalyzer($data);
        $totalLength = $analyzer->getLength();

        // Request a point near the end (in the final LineTo segment)
        $point = $analyzer->getPointAtLength($totalLength - 10);
        $this->assertNotNull($point);
        $this->assertInstanceOf(Point::class, $point);
    }

    public function testGetBoundingBoxWithCustomSegmentInElseBranch(): void
    {
        // To hit the else branch (lines 325-330) in getBoundingBox, use a segment
        // that doesn't match any instanceof check but has a non-null getTargetPoint.
        $segment = new readonly class('X') implements \Atelier\Svg\Path\Segment\SegmentInterface {
            public function __construct(private string $command)
            {
            }

            public function getCommand(): string
            {
                return $this->command;
            }

            public function isRelative(): bool
            {
                return false;
            }

            public function getTargetPoint(): ?Point
            {
                return new Point(42, 84);
            }

            public function toString(): string
            {
                return 'X42,84';
            }

            public function commandArgumentsToString(): string
            {
                return '42,84';
            }
        };

        $data = new Data([
            new \Atelier\Svg\Path\Segment\MoveTo('M', new Point(0, 0)),
            $segment,
        ]);

        $analyzer = new PathAnalyzer($data);
        $bbox = $analyzer->getBoundingBox();

        $this->assertEqualsWithDelta(42.0, $bbox->maxX, 0.001);
        $this->assertEqualsWithDelta(84.0, $bbox->maxY, 0.001);
    }
}
