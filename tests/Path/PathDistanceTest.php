<?php

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Path;
use Atelier\Svg\Path\PathBuilder;
use Atelier\Svg\Path\PathDistance;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathDistance::class)]
final class PathDistanceTest extends TestCase
{
    public function testHausdorffDistanceSamePath(): void
    {
        $path = Path::circle(50, 50, 25);
        $distance = $path->hausdorffDistance($path);

        // Distance to itself should be 0
        $this->assertEqualsWithDelta(0, $distance, 0.01);
    }

    public function testHausdorffDistanceOffsetCircles(): void
    {
        $circle1 = Path::circle(50, 50, 25);
        $circle2 = Path::circle(60, 50, 25);

        $distance = $circle1->hausdorffDistance($circle2);

        // Distance should be approximately 10 (the offset)
        $this->assertGreaterThan(5, $distance);
        $this->assertLessThan(15, $distance);
    }

    public function testFrechetDistanceSamePath(): void
    {
        $path = Path::rectangle(0, 0, 100, 50);
        $distance = $path->frechetDistance($path);

        // Distance to itself should be 0
        $this->assertEqualsWithDelta(0, $distance, 0.01);
    }

    public function testFrechetDistanceDifferentPaths(): void
    {
        $rect1 = Path::rectangle(0, 0, 100, 50);
        $rect2 = Path::rectangle(10, 10, 100, 50);

        $distance = $rect1->frechetDistance($rect2);

        // There should be some distance
        $this->assertGreaterThan(0, $distance);
    }

    public function testAverageDistanceSamePath(): void
    {
        $path = Path::star(100, 100, 50, 25, 5);
        $distance = $path->averageDistance($path);

        // Distance to itself should be 0
        $this->assertEqualsWithDelta(0, $distance, 0.01);
    }

    public function testAverageDistanceScaledPath(): void
    {
        $path1 = Path::circle(50, 50, 25);
        $path2 = Path::circle(50, 50, 30);

        $distance = $path1->averageDistance($path2);

        // Distance should be approximately the radius difference
        $this->assertGreaterThan(0, $distance);
        $this->assertLessThan(10, $distance);
    }

    public function testMaxPointDistanceSamePath(): void
    {
        $path = Path::polygon(100, 100, 50, 6);
        $distance = $path->maxPointDistance($path);

        // Distance to itself should be 0
        $this->assertEqualsWithDelta(0, $distance, 0.01);
    }

    public function testMaxPointDistanceOffsetPaths(): void
    {
        $poly1 = Path::polygon(100, 100, 50, 8);
        $poly2 = Path::polygon(120, 100, 50, 8);

        $distance = $poly1->maxPointDistance($poly2);

        // There should be measurable distance
        $this->assertGreaterThan(10, $distance);
    }

    public function testDistanceWithDifferentSampleCounts(): void
    {
        $path1 = Path::circle(50, 50, 25);
        $path2 = Path::circle(55, 50, 25);

        // Test with different sample counts
        $distance1 = $path1->averageDistance($path2, 20);
        $distance2 = $path1->averageDistance($path2, 100);

        // Both should give similar results
        $this->assertEqualsWithDelta($distance1, $distance2, 1.0);
    }

    public function testDistanceBetweenLine(): void
    {
        $line1 = Path::line(0, 0, 100, 0);
        $line2 = Path::line(0, 10, 100, 10);

        $distance = $line1->averageDistance($line2);

        // Distance between parallel lines should be approximately 10
        $this->assertEqualsWithDelta(10, $distance, 0.5);
    }

    public function testHausdorffDistanceSymmetry(): void
    {
        $path1 = Path::rectangle(0, 0, 50, 50);
        $path2 = Path::circle(25, 25, 20);

        $distance1to2 = $path1->hausdorffDistance($path2);
        $distance2to1 = $path2->hausdorffDistance($path1);

        // Hausdorff distance should be symmetric
        // Delta accounts for discrete sampling approximation effects
        $this->assertEqualsWithDelta($distance1to2, $distance2to1, 0.1);
    }

    public function testHausdorffDistanceSamePaths(): void
    {
        $path = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();

        $this->assertEqualsWithDelta(0.0, PathDistance::hausdorff($path, $path), 0.01);
    }

    public function testHausdorffDistanceDifferentPaths(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 50)->lineTo(100, 50)->toData();

        $this->assertGreaterThan(0, PathDistance::hausdorff($path1, $path2));
    }

    public function testDiscreteFrechetSamePaths(): void
    {
        $path = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();

        $this->assertEqualsWithDelta(0.0, PathDistance::discreteFrechet($path, $path), 0.01);
    }

    public function testDiscreteFrechetDifferentPaths(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 50)->lineTo(100, 50)->toData();

        $this->assertGreaterThan(0, PathDistance::discreteFrechet($path1, $path2));
    }

    public function testAverageDistanceSamePaths(): void
    {
        $path = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();

        $this->assertEqualsWithDelta(0.0, PathDistance::averageDistance($path, $path), 0.01);
    }

    public function testAverageDistanceDifferentPaths(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 20)->lineTo(100, 20)->toData();

        $this->assertEqualsWithDelta(20.0, PathDistance::averageDistance($path1, $path2), 0.5);
    }

    public function testMaxPointDistanceSamePaths(): void
    {
        $path = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();

        $this->assertEqualsWithDelta(0.0, PathDistance::maxPointDistance($path, $path), 0.01);
    }

    public function testMaxPointDistanceDifferentPaths(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 30)->lineTo(100, 30)->toData();

        $this->assertEqualsWithDelta(30.0, PathDistance::maxPointDistance($path1, $path2), 0.5);
    }

    public function testHausdorffDistanceSymmetric(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->lineTo(100, 50)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 10)->lineTo(80, 10)->toData();

        $d1 = PathDistance::hausdorff($path1, $path2);
        $d2 = PathDistance::hausdorff($path2, $path1);

        $this->assertEqualsWithDelta($d1, $d2, 0.5);
    }

    public function testHausdorffWithOneSample(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 10)->lineTo(100, 10)->toData();

        $distance = PathDistance::hausdorff($path1, $path2, 1);
        $this->assertGreaterThanOrEqual(0.0, $distance);
    }

    public function testDiscreteFrechetWithFewSamples(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(50, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 5)->lineTo(50, 5)->lineTo(100, 5)->toData();

        $distance = PathDistance::discreteFrechet($path1, $path2, 3);
        $this->assertGreaterThan(0.0, $distance);
        $this->assertLessThan(20.0, $distance);
    }

    public function testAverageDistanceWithZeroLengthPath(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(10, 10)->toData();

        $distance = PathDistance::averageDistance($path1, $path2);
        $this->assertEqualsWithDelta(0.0, $distance, 0.01);
    }

    public function testMaxPointDistanceWithZeroLengthPath(): void
    {
        $path1 = PathBuilder::new()->moveTo(5, 5)->toData();
        $path2 = PathBuilder::new()->moveTo(10, 10)->toData();

        $distance = PathDistance::maxPointDistance($path1, $path2);
        $this->assertEqualsWithDelta(0.0, $distance, 0.01);
    }

    public function testHausdorffWithZeroSamples(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 10)->lineTo(100, 10)->toData();

        $distance = PathDistance::hausdorff($path1, $path2, 0);
        $this->assertEqualsWithDelta(0.0, $distance, 0.01);
    }

    public function testDiscreteFrechetAsymmetricPaths(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 20)->lineTo(50, 70)->lineTo(100, 20)->toData();

        $distance = PathDistance::discreteFrechet($path1, $path2);
        $this->assertGreaterThan(0.0, $distance);
    }

    public function testMaxPointDistanceGreaterOrEqualToAverage(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 10)->lineTo(100, 30)->toData();

        $avg = PathDistance::averageDistance($path1, $path2);
        $max = PathDistance::maxPointDistance($path1, $path2);

        $this->assertGreaterThanOrEqual($avg, $max);
    }

    public function testDiscreteFrechetWithTwoSamples(): void
    {
        $path1 = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();
        $path2 = PathBuilder::new()->moveTo(0, 20)->lineTo(100, 20)->toData();

        $distance = PathDistance::discreteFrechet($path1, $path2, 2);
        $this->assertEqualsWithDelta(20.0, $distance, 1.0);
    }

    public function testDiscreteFrechetWithMultipleSegmentsCoversAllBranches(): void
    {
        // Use paths with 3+ points each to exercise all branches of computeFrechet:
        // i>0 && j==0, i==0 && j>0, i>0 && j>0, and cached values
        $path1 = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(50, 0)
            ->lineTo(100, 0)
            ->lineTo(150, 0)
            ->toData();
        $path2 = PathBuilder::new()
            ->moveTo(0, 10)
            ->lineTo(50, 10)
            ->lineTo(100, 10)
            ->lineTo(150, 10)
            ->toData();

        $distance = PathDistance::discreteFrechet($path1, $path2, 5);
        $this->assertGreaterThan(0.0, $distance);
        $this->assertLessThan(20.0, $distance);
    }

    public function testDiscreteFrechetDivergingPaths(): void
    {
        // Paths that diverge significantly to exercise all branches of computeFrechet
        $path1 = PathBuilder::new()
            ->moveTo(0, 0)
            ->lineTo(25, 0)
            ->lineTo(50, 0)
            ->lineTo(75, 0)
            ->lineTo(100, 0)
            ->toData();
        $path2 = PathBuilder::new()
            ->moveTo(0, 50)
            ->lineTo(25, 100)
            ->lineTo(50, 150)
            ->lineTo(75, 100)
            ->lineTo(100, 50)
            ->toData();

        $distance = PathDistance::discreteFrechet($path1, $path2, 10);
        $this->assertGreaterThan(0.0, $distance);
    }

    public function testDiscreteFrechetWithEmptyPaths(): void
    {
        $emptyPath = new Data([]);
        $distance = PathDistance::discreteFrechet($emptyPath, $emptyPath);
        $this->assertSame(0.0, $distance);
    }

    public function testDiscreteFrechetWithOneEmptyPath(): void
    {
        $emptyPath = new Data([]);
        $nonEmptyPath = PathBuilder::new()->moveTo(0, 0)->lineTo(100, 0)->toData();

        $distance = PathDistance::discreteFrechet($emptyPath, $nonEmptyPath);
        $this->assertSame(0.0, $distance);

        $distance2 = PathDistance::discreteFrechet($nonEmptyPath, $emptyPath);
        $this->assertSame(0.0, $distance2);
    }
}
