<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Morphing;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Morphing\PathMatcher;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\PathBuilder;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathMatcher::class)]
final class PathMatcherTest extends TestCase
{
    private PathMatcher $matcher;

    protected function setUp(): void
    {
        $this->matcher = new PathMatcher();
    }

    public function testMatchPathsWithEqualSegmentCount(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertInstanceOf(Data::class, $matched1);
        $this->assertInstanceOf(Data::class, $matched2);
        $this->assertCount(2, $matched1->getSegments());
        $this->assertCount(2, $matched2->getSegments());
    }

    public function testMatchPathsWithDifferentSegmentCount(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
            new CurveTo('C', new Point(140, 140), new Point(150, 150), new Point(160, 160)),
            new CurveTo('C', new Point(170, 170), new Point(180, 180), new Point(190, 190)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertInstanceOf(Data::class, $matched1);
        $this->assertInstanceOf(Data::class, $matched2);

        // Both should have the same number of segments after matching
        $this->assertEquals(
            count($matched1->getSegments()),
            count($matched2->getSegments())
        );
    }

    public function testMatchIncreasesSegmentCountOfShorterPath(): void
    {
        $shortPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $longPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
            new CurveTo('C', new Point(140, 140), new Point(150, 150), new Point(160, 160)),
            new CurveTo('C', new Point(170, 170), new Point(180, 180), new Point(190, 190)),
            new CurveTo('C', new Point(200, 200), new Point(210, 210), new Point(220, 220)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($shortPath, $longPath);

        $count1 = count($matched1->getSegments());
        $count2 = count($matched2->getSegments());

        $this->assertEquals($count1, $count2);
        $this->assertGreaterThan(2, $count1); // Should be more than original 2 segments
    }

    public function testMatchEmptyPaths(): void
    {
        $path1 = new Data([]);
        $path2 = new Data([]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertInstanceOf(Data::class, $matched1);
        $this->assertInstanceOf(Data::class, $matched2);
        $this->assertCount(0, $matched1->getSegments());
        $this->assertCount(0, $matched2->getSegments());
    }

    public function testMatchPathsWithClosePathSegment(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new ClosePath('Z'),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
            new ClosePath('Z'),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertInstanceOf(Data::class, $matched1);
        $this->assertInstanceOf(Data::class, $matched2);
        $this->assertCount(3, $matched1->getSegments());
        $this->assertCount(3, $matched2->getSegments());
    }

    public function testMatchPreservesSegmentTypes(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $segments1 = $matched1->getSegments();
        $segments2 = $matched2->getSegments();

        for ($i = 0; $i < count($segments1); ++$i) {
            $this->assertEquals(
                $segments1[$i]::class,
                $segments2[$i]::class,
                "Segments at index $i should have the same type"
            );
        }
    }

    public function testMatchWithOnlyMoveTo(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertCount(1, $matched1->getSegments());
        $this->assertCount(1, $matched2->getSegments());
    }

    public function testMatchComplexPaths(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new CurveTo('C', new Point(40, 40), new Point(50, 50), new Point(60, 60)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
            new CurveTo('C', new Point(140, 140), new Point(150, 150), new Point(160, 160)),
            new CurveTo('C', new Point(170, 170), new Point(180, 180), new Point(190, 190)),
            new CurveTo('C', new Point(200, 200), new Point(210, 210), new Point(220, 220)),
            new CurveTo('C', new Point(230, 230), new Point(240, 240), new Point(250, 250)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertEquals(
            count($matched1->getSegments()),
            count($matched2->getSegments())
        );
        $this->assertGreaterThan(3, count($matched1->getSegments()));
    }

    public function testMatchReturnsDataObjects(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
            new CurveTo('C', new Point(140, 140), new Point(150, 150), new Point(160, 160)),
        ]);

        $result = $this->matcher->match($path1, $path2);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Data::class, $result[0]);
        $this->assertInstanceOf(Data::class, $result[1]);
    }

    public function testMatchHandlesLargeSegmentDifference(): void
    {
        $simplePath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $complexPath = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        for ($i = 0; $i < 50; ++$i) {
            $x = $i * 10;
            $complexPath = new Data(array_merge(
                $complexPath->getSegments(),
                [new CurveTo('C', new Point($x, $x), new Point($x + 5, $x + 5), new Point($x + 10, $x + 10))]
            ));
        }

        [$matched1, $matched2] = $this->matcher->match($simplePath, $complexPath);

        $this->assertEquals(
            count($matched1->getSegments()),
            count($matched2->getSegments())
        );
    }

    public function testMatchMaintainsPathGeometry(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        // When paths have the same segment count, they should remain unchanged
        $this->assertEquals(
            count($path1->getSegments()),
            count($matched1->getSegments())
        );
        $this->assertEquals(
            count($path2->getSegments()),
            count($matched2->getSegments())
        );
    }

    public function testMatchPathsWithMultipleMoveTos(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(200, 200)),
            new CurveTo('C', new Point(210, 210), new Point(220, 220), new Point(230, 230)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertInstanceOf(Data::class, $matched1);
        $this->assertInstanceOf(Data::class, $matched2);
    }

    public function testMatchEqualSegmentCountReturnsAsIs(): void
    {
        $path1 = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(10, 20, 30, 40, 50, 50)
            ->curveTo(60, 70, 80, 90, 100, 100)
            ->toData();

        $path2 = PathBuilder::new()
            ->moveTo(5, 5)
            ->curveTo(15, 25, 35, 45, 55, 55)
            ->curveTo(65, 75, 85, 95, 105, 105)
            ->toData();

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertSame($path1, $matched1, 'Start path should be returned unchanged');
        $this->assertSame($path2, $matched2, 'End path should be returned unchanged');
    }

    public function testMatchDifferentSegmentCountSubdivides(): void
    {
        $path1 = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(10, 20, 30, 40, 50, 50)
            ->toData();

        $path2 = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(10, 20, 30, 40, 50, 50)
            ->curveTo(60, 70, 80, 90, 100, 100)
            ->curveTo(110, 120, 130, 140, 150, 150)
            ->toData();

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertCount(count($matched2->getSegments()), $matched1->getSegments());

        // The shorter path (path1) should have been subdivided: original had 2 segments
        $this->assertCount(4, $matched1->getSegments());

        // First segment should still be MoveTo
        $this->assertInstanceOf(MoveTo::class, $matched1->getSegments()[0]);

        // All remaining segments of subdivided path should be CurveTo
        foreach (array_slice($matched1->getSegments(), 1) as $segment) {
            $this->assertInstanceOf(CurveTo::class, $segment);
        }
    }

    public function testMatchWithMoreStartSegments(): void
    {
        $path1 = PathBuilder::new()
            ->moveTo(0, 0)
            ->curveTo(10, 20, 30, 40, 50, 50)
            ->curveTo(60, 70, 80, 90, 100, 100)
            ->curveTo(110, 120, 130, 140, 150, 150)
            ->curveTo(160, 170, 180, 190, 200, 200)
            ->toData();

        $path2 = PathBuilder::new()
            ->moveTo(5, 5)
            ->curveTo(15, 25, 35, 45, 55, 55)
            ->toData();

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $count1 = count($matched1->getSegments());
        $count2 = count($matched2->getSegments());

        $this->assertSame($count1, $count2, 'Both paths must have same segment count');
        // The end path (path2) should have been subdivided to match path1
        $this->assertSame(5, $count2);
    }

    public function testMatchWithNoCurvesUsesDuplicate(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(50, 50)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(20, 20)),
            new LineTo('L', new Point(40, 40)),
            new LineTo('L', new Point(60, 60)),
            new LineTo('L', new Point(80, 80)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $count1 = count($matched1->getSegments());
        $count2 = count($matched2->getSegments());

        $this->assertSame($count1, $count2, 'Both paths must have same segment count after duplication');
        $this->assertSame(5, $count1, 'Shorter path should be padded to target count');
    }

    public function testSubdividedCurvesPreserveEndpoints(): void
    {
        $startPoint = new Point(0, 0);
        $endPoint = new Point(100, 100);

        $path1 = new Data([
            new MoveTo('M', $startPoint),
            new CurveTo('C', new Point(25, 75), new Point(75, 25), $endPoint),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new CurveTo('C', new Point(40, 40), new Point(50, 50), new Point(60, 60)),
            new CurveTo('C', new Point(70, 70), new Point(80, 80), new Point(90, 90)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $segments = $matched1->getSegments();

        // First segment is MoveTo at the original start
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertEqualsWithDelta($startPoint->x, $segments[0]->getTargetPoint()->x, 0.01);
        $this->assertEqualsWithDelta($startPoint->y, $segments[0]->getTargetPoint()->y, 0.01);

        // Last CurveTo should end at original endpoint
        $lastSegment = $segments[count($segments) - 1];
        $this->assertInstanceOf(CurveTo::class, $lastSegment);
        $this->assertEqualsWithDelta($endPoint->x, $lastSegment->getTargetPoint()->x, 0.01);
        $this->assertEqualsWithDelta($endPoint->y, $lastSegment->getTargetPoint()->y, 0.01);
    }

    public function testSubdivisionWithRemainderDistributed(): void
    {
        // 2 curves need 5 extra segments: 2 extra each + 1 remainder to first
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 20), new Point(30, 40), new Point(50, 50)),
            new CurveTo('C', new Point(60, 70), new Point(80, 90), new Point(100, 100)),
        ]);

        $path2Segments = [new MoveTo('M', new Point(0, 0))];
        for ($i = 0; $i < 7; ++$i) {
            $x = $i * 15;
            $path2Segments[] = new CurveTo('C', new Point($x, $x), new Point($x + 5, $x + 5), new Point($x + 10, $x + 10));
        }
        $path2 = new Data($path2Segments);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertSame(
            count($matched1->getSegments()),
            count($matched2->getSegments()),
        );
        $this->assertSame(8, count($matched1->getSegments()));
    }

    public function testMatchWithClosePathAndDifferentCounts(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 20), new Point(30, 40), new Point(50, 50)),
            new ClosePath('Z'),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new CurveTo('C', new Point(40, 40), new Point(50, 50), new Point(60, 60)),
            new CurveTo('C', new Point(70, 70), new Point(80, 80), new Point(90, 90)),
            new ClosePath('Z'),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertSame(
            count($matched1->getSegments()),
            count($matched2->getSegments()),
        );

        // ClosePath should be preserved
        $segments = $matched1->getSegments();
        $this->assertInstanceOf(ClosePath::class, end($segments));
    }

    public function testSubdivideAlreadyAtTargetCount(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new CurveTo('C', new Point(40, 40), new Point(50, 50), new Point(60, 60)),
        ]);

        // Same count paths should be returned as-is
        $path2 = new Data([
            new MoveTo('M', new Point(5, 5)),
            new CurveTo('C', new Point(15, 15), new Point(25, 25), new Point(35, 35)),
            new CurveTo('C', new Point(45, 45), new Point(55, 55), new Point(65, 65)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertSame($path1, $matched1);
        $this->assertSame($path2, $matched2);
    }

    public function testSubdivideCurveWithCountOne(): void
    {
        // Test the subdivideCurve path where count <= 1 (line 138-139)
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        // Path with exactly one more segment - so each curve is "subdivided" once
        $path2 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new CurveTo('C', new Point(40, 40), new Point(50, 50), new Point(60, 60)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertCount(
            count($matched2->getSegments()),
            $matched1->getSegments()
        );
    }

    public function testExtractSubcurveNearEndOfRange(): void
    {
        // Test with a curve being subdivided into many parts to exercise extractSubcurve
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(50, 0), new Point(100, 50), new Point(100, 100)),
        ]);

        // Create a much longer path to force significant subdivision
        $segments = [new MoveTo('M', new Point(0, 0))];
        for ($i = 0; $i < 10; ++$i) {
            $segments[] = new CurveTo(
                'C',
                new Point($i * 10, $i * 10 + 5),
                new Point($i * 10 + 5, $i * 10 + 10),
                new Point(($i + 1) * 10, ($i + 1) * 10)
            );
        }
        $path2 = new Data($segments);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertSame(
            count($matched1->getSegments()),
            count($matched2->getSegments())
        );
    }

    public function testDuplicateSegmentsNeedsPadding(): void
    {
        // 3 non-curve segments need to reach target of 4
        // ratio = 4/3 = 1.33, round = 1, total = 3, needs padding to 4
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
            new LineTo('L', new Point(20, 20)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
            new LineTo('L', new Point(20, 20)),
            new LineTo('L', new Point(30, 30)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertSame(
            count($matched1->getSegments()),
            count($matched2->getSegments())
        );
        $this->assertSame(4, count($matched1->getSegments()));
    }

    public function testSubdivideCurveWithCountOneWhenMoreCurvesThanSegmentsToAdd(): void
    {
        // Path with 3 curves (4 segments) needs to reach target 5
        // segmentsToAdd=1, curvesCount=3, subdivisionsPerCurve=0, remainder=1
        // First curve: count=0+1=1 (no split), curves 2,3: count=0+1=1 (subdivideCurve returns [$curve])
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new CurveTo('C', new Point(40, 40), new Point(50, 50), new Point(60, 60)),
            new CurveTo('C', new Point(70, 70), new Point(80, 80), new Point(90, 90)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new CurveTo('C', new Point(40, 40), new Point(50, 50), new Point(60, 60)),
            new CurveTo('C', new Point(70, 70), new Point(80, 80), new Point(90, 90)),
            new CurveTo('C', new Point(100, 100), new Point(110, 110), new Point(120, 120)),
        ]);

        [$matched1, $matched2] = $this->matcher->match($path1, $path2);

        $this->assertSame(
            count($matched1->getSegments()),
            count($matched2->getSegments())
        );
    }
}
