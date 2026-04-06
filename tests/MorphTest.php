<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Morphing\Morph;
use Atelier\Svg\Morphing\MorphingBuilder;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Morph::class)]
final class MorphTest extends TestCase
{
    private Data $startPath;
    private Data $endPath;

    protected function setUp(): void
    {
        // Create simple test paths for morphing
        $this->startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);

        $this->endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);
    }

    public function testClassIsNotInstantiable(): void
    {
        $reflection = new \ReflectionClass(Morph::class);
        $this->assertFalse($reflection->isInstantiable());

        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());
        $instance = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke($instance);

        $this->assertInstanceOf(Morph::class, $instance);
    }

    public function testBetweenWithDefaultEasing(): void
    {
        $result = Morph::between($this->startPath, $this->endPath, 0.5);

        $this->assertInstanceOf(Data::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }

    public function testBetweenWithCustomEasing(): void
    {
        $result = Morph::between($this->startPath, $this->endPath, 0.5, 'ease-in-out');

        $this->assertInstanceOf(Data::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }

    public function testBetweenWithCallableEasing(): void
    {
        $easing = static fn (float $t): float => $t * $t;
        $result = Morph::between($this->startPath, $this->endPath, 0.5, $easing);

        $this->assertInstanceOf(Data::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }

    public function testBetweenAtZero(): void
    {
        $result = Morph::between($this->startPath, $this->endPath, 0.0);

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testBetweenAtOne(): void
    {
        $result = Morph::between($this->startPath, $this->endPath, 1.0);

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testBetweenWithVariousEasings(): void
    {
        $easings = ['linear', 'ease-in', 'ease-out', 'ease-in-out'];

        foreach ($easings as $easing) {
            $result = Morph::between($this->startPath, $this->endPath, 0.5, $easing);

            $this->assertInstanceOf(Data::class, $result, "Failed for easing: {$easing}");
        }
    }

    public function testFramesWithDefaultEasing(): void
    {
        $frames = Morph::frames($this->startPath, $this->endPath, 10);

        $this->assertIsArray($frames);
        $this->assertCount(10, $frames);

        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testFramesWithCustomEasing(): void
    {
        $frames = Morph::frames($this->startPath, $this->endPath, 5, 'ease-in-out');

        $this->assertIsArray($frames);
        $this->assertCount(5, $frames);

        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testFramesWithCallableEasing(): void
    {
        $easing = static fn (float $t): float => $t * $t;
        $frames = Morph::frames($this->startPath, $this->endPath, 5, $easing);

        $this->assertIsArray($frames);
        $this->assertCount(5, $frames);

        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testFramesWithSingleFrame(): void
    {
        $frames = Morph::frames($this->startPath, $this->endPath, 1);

        $this->assertIsArray($frames);
        $this->assertCount(1, $frames);
        $this->assertInstanceOf(Data::class, $frames[0]);
    }

    public function testFramesWithMultipleFrames(): void
    {
        $frames = Morph::frames($this->startPath, $this->endPath, 60);

        $this->assertIsArray($frames);
        $this->assertCount(60, $frames);

        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testFramesWithVariousEasings(): void
    {
        $easings = ['linear', 'ease-in', 'ease-out', 'ease-in-out'];

        foreach ($easings as $easing) {
            $frames = Morph::frames($this->startPath, $this->endPath, 3, $easing);

            $this->assertCount(3, $frames, "Failed for easing: {$easing}");
            foreach ($frames as $frame) {
                $this->assertInstanceOf(Data::class, $frame);
            }
        }
    }

    public function testCreate(): void
    {
        $builder = Morph::create();

        $this->assertInstanceOf(MorphingBuilder::class, $builder);
    }

    public function testCreateReturnsNewInstance(): void
    {
        $builder1 = Morph::create();
        $builder2 = Morph::create();

        $this->assertInstanceOf(MorphingBuilder::class, $builder1);
        $this->assertInstanceOf(MorphingBuilder::class, $builder2);
        $this->assertNotSame($builder1, $builder2);
    }

    public function testCreateCanBeUsedForMorphing(): void
    {
        $result = Morph::create()
            ->from($this->startPath)
            ->to($this->endPath)
            ->at(0.5);

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testCreateCanBeUsedForGeneratingFrames(): void
    {
        $frames = Morph::create()
            ->from($this->startPath)
            ->to($this->endPath)
            ->withFrames(5)
            ->generate();

        $this->assertIsArray($frames);
        $this->assertCount(5, $frames);
    }
}
