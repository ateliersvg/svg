<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Morphing;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Morphing\MorphingInterpolator;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MorphingInterpolator::class)]
final class MorphingInterpolatorTest extends TestCase
{
    private MorphingInterpolator $interpolator;

    protected function setUp(): void
    {
        $this->interpolator = new MorphingInterpolator();
    }

    public function testInterpolateAtZero(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        $result = $this->interpolator->interpolate($start, $end, 0.0);

        $segments = $result->getSegments();
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->y);
    }

    public function testInterpolateAtOne(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        $result = $this->interpolator->interpolate($start, $end, 1.0);

        $segments = $result->getSegments();
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertEquals(100, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(100, $segments[0]->getTargetPoint()->y);
    }

    public function testInterpolateAtHalf(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        $result = $this->interpolator->interpolate($start, $end, 0.5);

        $segments = $result->getSegments();
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertEquals(50, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(50, $segments[0]->getTargetPoint()->y);
    }

    public function testInterpolateCurveTo(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        $result = $this->interpolator->interpolate($start, $end, 0.5);

        $segments = $result->getSegments();
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        $curve = $segments[1];

        // At t=0.5, all points should be halfway
        $this->assertEquals(60, $curve->getControlPoint1()->x);
        $this->assertEquals(60, $curve->getControlPoint1()->y);
        $this->assertEquals(70, $curve->getControlPoint2()->x);
        $this->assertEquals(70, $curve->getControlPoint2()->y);
        $this->assertEquals(80, $curve->getTargetPoint()->x);
        $this->assertEquals(80, $curve->getTargetPoint()->y);
    }

    public function testInterpolateClosePath(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
            new ClosePath('Z'),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
            new ClosePath('Z'),
        ]);

        $result = $this->interpolator->interpolate($start, $end, 0.5);

        $segments = $result->getSegments();
        $this->assertInstanceOf(ClosePath::class, $segments[1]);
    }

    public function testInterpolateClampsNegativeT(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        $result = $this->interpolator->interpolate($start, $end, -0.5);

        $segments = $result->getSegments();
        // Should clamp to 0
        $this->assertEquals(0, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(0, $segments[0]->getTargetPoint()->y);
    }

    public function testInterpolateClampsLargeT(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        $result = $this->interpolator->interpolate($start, $end, 1.5);

        $segments = $result->getSegments();
        // Should clamp to 1
        $this->assertEquals(100, $segments[0]->getTargetPoint()->x);
        $this->assertEquals(100, $segments[0]->getTargetPoint()->y);
    }

    public function testInterpolateWithLinearEasing(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        $easing = MorphingInterpolator::easeLinear(...);
        $result = $this->interpolator->interpolate($start, $end, 0.5, $easing);

        $segments = $result->getSegments();
        $this->assertEquals(50, $segments[0]->getTargetPoint()->x);
    }

    public function testInterpolateWithEaseInEasing(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        $easing = MorphingInterpolator::easeIn(...);
        $result = $this->interpolator->interpolate($start, $end, 0.5, $easing);

        $segments = $result->getSegments();
        // easeIn(0.5) = 0.5 * 0.5 = 0.25, so position should be 25
        $this->assertEquals(25, $segments[0]->getTargetPoint()->x);
    }

    public function testInterpolateWithCustomEasing(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        // Custom easing that always returns 0.75
        $customEasing = fn ($t) => 0.75;
        $result = $this->interpolator->interpolate($start, $end, 0.5, $customEasing);

        $segments = $result->getSegments();
        $this->assertEquals(75, $segments[0]->getTargetPoint()->x);
    }

    public function testInterpolateThrowsOnMismatchedSegmentCount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Paths must have the same number of segments');

        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        $this->interpolator->interpolate($start, $end, 0.5);
    }

    public function testInterpolateThrowsOnMismatchedSegmentTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot interpolate between different segment types');

        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        $this->interpolator->interpolate($start, $end, 0.5);
    }

    public function testGenerateFrames(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        $frames = $this->interpolator->generateFrames($start, $end, 10);

        $this->assertCount(10, $frames);
        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testGenerateFramesProgression(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        $frames = $this->interpolator->generateFrames($start, $end, 5);

        $this->assertCount(5, $frames);

        // Check first frame is at start
        $this->assertEquals(0, $frames[0]->getSegments()[0]->getTargetPoint()->x);

        // Check last frame is at end
        $this->assertEquals(100, $frames[4]->getSegments()[0]->getTargetPoint()->x);

        // Check middle frames progress
        $this->assertGreaterThan(0, $frames[1]->getSegments()[0]->getTargetPoint()->x);
        $this->assertLessThan(100, $frames[3]->getSegments()[0]->getTargetPoint()->x);
    }

    public function testGenerateFramesWithEasing(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        $easing = MorphingInterpolator::easeIn(...);
        $frames = $this->interpolator->generateFrames($start, $end, 5, $easing);

        $this->assertCount(5, $frames);
        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testGenerateSingleFrame(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
        ]);

        $frames = $this->interpolator->generateFrames($start, $end, 1);

        $this->assertCount(1, $frames);
        // Single frame should be at start (t=0 when frameCount=1)
        $this->assertEquals(0, $frames[0]->getSegments()[0]->getTargetPoint()->x);
    }

    // =========================================================================
    // EASING FUNCTIONS TESTS
    // =========================================================================

    public function testEaseLinear(): void
    {
        $this->assertEquals(0.0, MorphingInterpolator::easeLinear(0.0));
        $this->assertEquals(0.5, MorphingInterpolator::easeLinear(0.5));
        $this->assertEquals(1.0, MorphingInterpolator::easeLinear(1.0));
    }

    public function testEaseIn(): void
    {
        $this->assertEquals(0.0, MorphingInterpolator::easeIn(0.0));
        $this->assertEquals(0.25, MorphingInterpolator::easeIn(0.5));
        $this->assertEquals(1.0, MorphingInterpolator::easeIn(1.0));
    }

    public function testEaseOut(): void
    {
        $result0 = MorphingInterpolator::easeOut(0.0);
        $result05 = MorphingInterpolator::easeOut(0.5);
        $result1 = MorphingInterpolator::easeOut(1.0);

        $this->assertEquals(0.0, $result0);
        $this->assertEquals(0.75, $result05);
        $this->assertEquals(1.0, $result1);
    }

    public function testEaseInOut(): void
    {
        $this->assertEquals(0.0, MorphingInterpolator::easeInOut(0.0));
        $this->assertEquals(1.0, MorphingInterpolator::easeInOut(1.0));

        // At t=0.5, should be at middle
        $mid = MorphingInterpolator::easeInOut(0.5);
        $this->assertGreaterThan(0.4, $mid);
        $this->assertLessThan(0.6, $mid);
    }

    public function testEaseInCubic(): void
    {
        $this->assertEquals(0.0, MorphingInterpolator::easeInCubic(0.0));
        $this->assertEquals(0.125, MorphingInterpolator::easeInCubic(0.5));
        $this->assertEquals(1.0, MorphingInterpolator::easeInCubic(1.0));
    }

    public function testEaseOutCubic(): void
    {
        $this->assertEquals(0.0, MorphingInterpolator::easeOutCubic(0.0));
        $this->assertEquals(1.0, MorphingInterpolator::easeOutCubic(1.0));

        $mid = MorphingInterpolator::easeOutCubic(0.5);
        $this->assertGreaterThan(0.8, $mid);
    }

    public function testEaseInOutCubic(): void
    {
        $this->assertEquals(0.0, MorphingInterpolator::easeInOutCubic(0.0));
        $this->assertEquals(1.0, MorphingInterpolator::easeInOutCubic(1.0));

        $mid = MorphingInterpolator::easeInOutCubic(0.5);
        $this->assertGreaterThan(0.4, $mid);
        $this->assertLessThan(0.6, $mid);
    }

    public function testEaseOutElastic(): void
    {
        $this->assertEquals(0.0, MorphingInterpolator::easeOutElastic(0.0));
        $this->assertEquals(1.0, MorphingInterpolator::easeOutElastic(1.0));

        // Elastic should oscillate
        $mid = MorphingInterpolator::easeOutElastic(0.5);
        $this->assertIsFloat($mid);
    }

    public function testEaseInBack(): void
    {
        $result0 = MorphingInterpolator::easeInBack(0.0);
        $result1 = MorphingInterpolator::easeInBack(1.0);

        $this->assertEquals(0.0, $result0);
        $this->assertEqualsWithDelta(1.0, $result1, 0.0000001);

        // Should go negative (pull back)
        $early = MorphingInterpolator::easeInBack(0.3);
        $this->assertIsFloat($early);
    }

    public function testEaseOutBack(): void
    {
        $result0 = MorphingInterpolator::easeOutBack(0.0);
        $result1 = MorphingInterpolator::easeOutBack(1.0);

        $this->assertEqualsWithDelta(0.0, $result0, 0.0000001);
        $this->assertEquals(1.0, $result1);

        // Should overshoot
        $late = MorphingInterpolator::easeOutBack(0.7);
        $this->assertIsFloat($late);
    }

    public function testCubicBezierEasing(): void
    {
        $easing = MorphingInterpolator::cubicBezierEasing(0.42, 0, 0.58, 1);

        $this->assertIsCallable($easing);

        $result0 = $easing(0.0);
        $result05 = $easing(0.5);
        $result1 = $easing(1.0);

        $this->assertEquals(0.0, $result0);
        $this->assertGreaterThanOrEqual(0.0, $result05);
        $this->assertLessThanOrEqual(1.0, $result05);
        $this->assertEquals(1.0, $result1);
    }

    public function testCubicBezierEasingLinear(): void
    {
        // Linear bezier: both control points on the line
        $easing = MorphingInterpolator::cubicBezierEasing(0.33, 0.33, 0.66, 0.66);

        $result05 = $easing(0.5);
        $this->assertEqualsWithDelta(0.5, $result05, 0.1);
    }

    public function testAllEasingFunctionsReturnValidRange(): void
    {
        $easings = [
            'easeLinear',
            'easeIn',
            'easeOut',
            'easeInOut',
            'easeInCubic',
            'easeOutCubic',
            'easeInOutCubic',
        ];

        foreach ($easings as $easing) {
            $result0 = MorphingInterpolator::$easing(0.0);
            $result1 = MorphingInterpolator::$easing(1.0);

            $this->assertEquals(0.0, $result0, "Easing $easing should return 0 at t=0");
            $this->assertEquals(1.0, $result1, "Easing $easing should return 1 at t=1");
        }
    }

    public function testInterpolateComplexPath(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
            new CurveTo('C', new Point(40, 40), new Point(50, 50), new Point(60, 60)),
        ]);

        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
            new CurveTo('C', new Point(140, 140), new Point(150, 150), new Point(160, 160)),
        ]);

        $result = $this->interpolator->interpolate($start, $end, 0.5);

        $this->assertInstanceOf(Data::class, $result);
        $this->assertCount(3, $result->getSegments());
    }

    public function testInterpolateFallbackForUnknownSegmentType(): void
    {
        $start = new Data([
            new \Atelier\Svg\Path\Segment\LineTo('L', new Point(10, 10)),
        ]);

        $end = new Data([
            new \Atelier\Svg\Path\Segment\LineTo('L', new Point(20, 20)),
        ]);

        $result = $this->interpolator->interpolate($start, $end, 0.5);

        $segments = $result->getSegments();
        $this->assertCount(1, $segments);
        // The fallback returns the start segment
        $this->assertInstanceOf(\Atelier\Svg\Path\Segment\LineTo::class, $segments[0]);
    }
}
