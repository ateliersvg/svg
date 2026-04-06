<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Morphing;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Morphing\PathNormalizer;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\ArcTo;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\QuadraticCurveTo;
use Atelier\Svg\Path\Segment\SmoothCurveTo;
use Atelier\Svg\Path\Segment\SmoothQuadraticCurveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathNormalizer::class)]
final class PathNormalizerTest extends TestCase
{
    private PathNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new PathNormalizer();
    }

    public function testNormalizeMoveTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(10, 20)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $this->assertInstanceOf(Data::class, $normalized);
        $segments = $normalized->getSegments();
        $this->assertCount(1, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertEquals(10, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(20, $segments[0]->getTargetPoint()->y);
    }

    public function testNormalizeRelativeMoveTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(10, 10)),
            new MoveTo('m', new Point(5, 5)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $this->assertInstanceOf(Data::class, $normalized);
        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[1]);
        // Second MoveTo should be absolute: 10 + 5, 10 + 5
        $this->assertEquals(15, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(15, $segments[1]->getTargetPoint()->y);
    }

    public function testNormalizeLineToBecomesСubicBezier(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        // LineTo should be converted to CurveTo
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
    }

    public function testNormalizeRelativeLineTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(10, 10)),
            new LineTo('l', new Point(5, 5)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        // End point should be absolute: 10 + 5, 10 + 5
        $this->assertEquals(15, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(15, $segments[1]->getTargetPoint()->y);
    }

    public function testNormalizeCurveTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        $curve = $segments[1];
        $this->assertEquals(10, $curve->getControlPoint1()->x);
        $this->assertEquals(20, $curve->getControlPoint2()->x);
        $this->assertEquals(30, $curve->getTargetPoint()->x);
    }

    public function testNormalizeRelativeCurveTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('c', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        // All points should be converted to absolute
        $curve = $segments[1];
        $this->assertEquals(10, $curve->getControlPoint1()->x);
        $this->assertEquals(20, $curve->getControlPoint2()->x);
        $this->assertEquals(30, $curve->getTargetPoint()->x);
    }

    public function testNormalizeSmoothCurveTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new SmoothCurveTo('S', new Point(50, 50), new Point(60, 60)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(3, $segments);
        // SmoothCurveTo should be expanded to CurveTo
        $this->assertInstanceOf(CurveTo::class, $segments[2]);
    }

    public function testNormalizeSmoothCurveToWithoutPreviousCurve(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new SmoothCurveTo('S', new Point(50, 50), new Point(60, 60)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        // SmoothCurveTo should be expanded to CurveTo
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
    }

    public function testNormalizeQuadraticCurveTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new QuadraticCurveTo('Q', new Point(10, 10), new Point(20, 20)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        // QuadraticCurveTo should be converted to CurveTo
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
    }

    public function testNormalizeRelativeQuadraticCurveTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(10, 10)),
            new QuadraticCurveTo('q', new Point(5, 5), new Point(10, 10)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        // End point should be absolute
        $this->assertEquals(20, $segments[1]->getTargetPoint()->x);
        $this->assertEquals(20, $segments[1]->getTargetPoint()->y);
    }

    public function testNormalizeSmoothQuadraticCurveTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new QuadraticCurveTo('Q', new Point(10, 10), new Point(20, 20)),
            new SmoothQuadraticCurveTo('T', new Point(40, 40)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(3, $segments);
        // SmoothQuadraticCurveTo should be converted to CurveTo
        $this->assertInstanceOf(CurveTo::class, $segments[2]);
    }

    public function testNormalizeArcTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new ArcTo('A', 50, 50, 0, false, true, new Point(100, 100)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        // Arc should be converted to one or more CurveTo segments
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
    }

    public function testNormalizeClosePath(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
            new ClosePath('Z'),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertGreaterThanOrEqual(2, count($segments));
        // Should contain ClosePath
        $lastSegment = $segments[count($segments) - 1];
        $this->assertInstanceOf(ClosePath::class, $lastSegment);
    }

    public function testNormalizeClosePathWhenAlreadyAtStart(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
            new LineTo('L', new Point(0, 0)),
            new ClosePath('Z'),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        // Should contain ClosePath
        $lastSegment = $segments[count($segments) - 1];
        $this->assertInstanceOf(ClosePath::class, $lastSegment);
    }

    public function testNormalizeComplexPath(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
            new CurveTo('C', new Point(20, 20), new Point(30, 30), new Point(40, 40)),
            new QuadraticCurveTo('Q', new Point(50, 50), new Point(60, 60)),
            new ClosePath('Z'),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $this->assertInstanceOf(Data::class, $normalized);
        $segments = $normalized->getSegments();
        $this->assertNotEmpty($segments);

        // All curve segments should be CurveTo (except MoveTo and ClosePath)
        foreach ($segments as $segment) {
            $this->assertTrue(
                $segment instanceof MoveTo
                || $segment instanceof CurveTo
                || $segment instanceof ClosePath,
                'All segments should be normalized to MoveTo, CurveTo, or ClosePath'
            );
        }
    }

    public function testNormalizeEmptyPath(): void
    {
        $path = new Data([]);

        $normalized = $this->normalizer->normalize($path);

        $this->assertInstanceOf(Data::class, $normalized);
        $this->assertCount(0, $normalized->getSegments());
    }

    public function testNormalizeMultiplePaths(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(100, 100)),
            new QuadraticCurveTo('Q', new Point(110, 110), new Point(120, 120)),
        ]);

        $normalized1 = $this->normalizer->normalize($path1);
        $normalized2 = $this->normalizer->normalize($path2);

        $this->assertInstanceOf(Data::class, $normalized1);
        $this->assertInstanceOf(Data::class, $normalized2);
        $this->assertNotEmpty($normalized1->getSegments());
        $this->assertNotEmpty($normalized2->getSegments());
    }

    public function testNormalizePreservesPathStructure(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 0)),
            new LineTo('L', new Point(10, 10)),
            new LineTo('L', new Point(0, 10)),
            new ClosePath('Z'),
        ]);

        $normalized = $this->normalizer->normalize($path);

        // Should have MoveTo + 4 curves (or curves + line back) + ClosePath
        $segments = $normalized->getSegments();
        $this->assertGreaterThanOrEqual(4, count($segments));
    }

    public function testNormalizeArcToWithZeroLength(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new ArcTo('A', 10, 10, 0, false, true, new Point(0, 0)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $this->assertInstanceOf(Data::class, $normalized);
        $segments = $normalized->getSegments();
        $this->assertNotEmpty($segments);
    }

    public function testNormalizeHandlesConsecutiveCurves(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new CurveTo('C', new Point(40, 40), new Point(50, 50), new Point(60, 60)),
            new CurveTo('C', new Point(70, 70), new Point(80, 80), new Point(90, 90)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(4, $segments); // MoveTo + 3 CurveTo
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        $this->assertInstanceOf(CurveTo::class, $segments[2]);
        $this->assertInstanceOf(CurveTo::class, $segments[3]);
    }

    public function testNormalizeRelativeArcTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(50, 50)),
            new ArcTo('a', 30, 20, 0, false, true, new Point(50, 50)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertNotEmpty($segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        // The arc should be converted to CurveTo
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        // Absolute target should be 50+50=100, 50+50=100
        $this->assertEqualsWithDelta(100.0, $segments[1]->getTargetPoint()->x, 0.01);
        $this->assertEqualsWithDelta(100.0, $segments[1]->getTargetPoint()->y, 0.01);
    }

    public function testNormalizeSmoothQuadraticCurveToWithoutPrevious(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new SmoothQuadraticCurveTo('T', new Point(40, 40)),
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
    }

    public function testNormalizeUnknownSegmentTypeReturnsEmpty(): void
    {
        // Line 154: unknown segment type falls through all instanceof checks and returns []
        $unknownSegment = new readonly class('X') implements \Atelier\Svg\Path\Segment\SegmentInterface {
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
                return new Point(10, 10);
            }

            public function toString(): string
            {
                return 'X10,10';
            }

            public function commandArgumentsToString(): string
            {
                return '10,10';
            }
        };

        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            $unknownSegment,
        ]);

        $normalized = $this->normalizer->normalize($path);

        $segments = $normalized->getSegments();
        // Only MoveTo should be in the result, unknown segment returns []
        $this->assertCount(1, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
    }
}
