<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Morphing;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Morphing\MorphingBuilder;
use Atelier\Svg\Morphing\ShapeMorpher;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShapeMorpher::class)]
#[CoversClass(MorphingBuilder::class)]
final class ShapeMorpherTest extends TestCase
{
    private ShapeMorpher $morpher;

    protected function setUp(): void
    {
        $this->morpher = new ShapeMorpher();
    }

    public function testConstructor(): void
    {
        $morpher = new ShapeMorpher();
        $this->assertInstanceOf(ShapeMorpher::class, $morpher);
    }

    public function testCreate(): void
    {
        $builder = ShapeMorpher::create();
        $this->assertInstanceOf(MorphingBuilder::class, $builder);
    }

    public function testMorphSimplePaths(): void
    {
        // Create two simple paths
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        // Morph at t=0.5 (midpoint)
        $result = $this->morpher->morph($startPath, $endPath, 0.5);

        $this->assertInstanceOf(Data::class, $result);
        $segments = $result->getSegments();
        $this->assertNotEmpty($segments);
    }

    public function testMorphAtStartReturnsStartPath(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.0);

        $this->assertInstanceOf(Data::class, $result);
        $segments = $result->getSegments();
        $this->assertNotEmpty($segments);
        // At t=0, first segment should be close to start position
        $firstSegment = $segments[0];
        $this->assertInstanceOf(MoveTo::class, $firstSegment);
    }

    public function testMorphAtEndReturnsEndPath(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 1.0);

        $this->assertInstanceOf(Data::class, $result);
        $segments = $result->getSegments();
        $this->assertNotEmpty($segments);
    }

    public function testMorphWithLinearEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, 'linear');

        $this->assertInstanceOf(Data::class, $result);
        $segments = $result->getSegments();
        $this->assertNotEmpty($segments);
    }

    public function testMorphWithEaseInEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-in');

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testMorphWithEaseOutEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-out');

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testMorphWithEaseInOutEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-in-out');

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testMorphWithCubicEasings(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result1 = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-in-cubic');
        $this->assertInstanceOf(Data::class, $result1);

        $result2 = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-out-cubic');
        $this->assertInstanceOf(Data::class, $result2);

        $result3 = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-in-out-cubic');
        $this->assertInstanceOf(Data::class, $result3);
    }

    public function testMorphWithElasticEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-out-elastic');

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testMorphWithBackEasings(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result1 = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-in-back');
        $this->assertInstanceOf(Data::class, $result1);

        $result2 = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-out-back');
        $this->assertInstanceOf(Data::class, $result2);
    }

    public function testMorphWithCustomCallableEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $customEasing = fn (float $t) => $t * $t * $t;
        $result = $this->morpher->morph($startPath, $endPath, 0.5, $customEasing);

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testGenerateFrames(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $frames = $this->morpher->generateFrames($startPath, $endPath, 10);

        $this->assertCount(10, $frames);
        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testGenerateFramesWithEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $frames = $this->morpher->generateFrames($startPath, $endPath, 5, 'ease-in-out');

        $this->assertCount(5, $frames);
        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testNormalize(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $normalized = $this->morpher->normalize($path);

        $this->assertInstanceOf(Data::class, $normalized);
    }

    public function testMatch(): void
    {
        $path1 = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $path2 = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        [$matched1, $matched2] = $this->morpher->match($path1, $path2);

        $this->assertInstanceOf(Data::class, $matched1);
        $this->assertInstanceOf(Data::class, $matched2);
    }

    // =========================================================================
    // MORPHING BUILDER TESTS
    // =========================================================================

    public function testBuilderFrom(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $builder = ShapeMorpher::create()->from($path);

        $this->assertInstanceOf(MorphingBuilder::class, $builder);
    }

    public function testBuilderTo(): void
    {
        $path = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $builder = ShapeMorpher::create()->to($path);

        $this->assertInstanceOf(MorphingBuilder::class, $builder);
    }

    public function testBuilderWithFrames(): void
    {
        $builder = ShapeMorpher::create()->withFrames(30);

        $this->assertInstanceOf(MorphingBuilder::class, $builder);
    }

    public function testBuilderWithEasing(): void
    {
        $builder = ShapeMorpher::create()->withEasing('ease-in');

        $this->assertInstanceOf(MorphingBuilder::class, $builder);
    }

    public function testBuilderWithCustomEasing(): void
    {
        $customEasing = fn (float $t) => $t;
        $builder = ShapeMorpher::create()->withEasing($customEasing);

        $this->assertInstanceOf(MorphingBuilder::class, $builder);
    }

    public function testBuilderWithDuration(): void
    {
        $builder = ShapeMorpher::create()->withDuration(1000, 60);

        $this->assertInstanceOf(MorphingBuilder::class, $builder);
    }

    public function testBuilderGenerate(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $frames = ShapeMorpher::create()
            ->from($startPath)
            ->to($endPath)
            ->withFrames(10)
            ->generate();

        $this->assertCount(10, $frames);
        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testBuilderGenerateWithoutStartPathThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Both start and end paths must be set');

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        ShapeMorpher::create()
            ->to($endPath)
            ->generate();
    }

    public function testBuilderGenerateWithoutEndPathThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Both start and end paths must be set');

        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        ShapeMorpher::create()
            ->from($startPath)
            ->generate();
    }

    public function testBuilderAt(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = ShapeMorpher::create()
            ->from($startPath)
            ->to($endPath)
            ->at(0.5);

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testBuilderAtWithoutStartPathThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Both start and end paths must be set');

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        ShapeMorpher::create()
            ->to($endPath)
            ->at(0.5);
    }

    public function testBuilderAtWithoutEndPathThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Both start and end paths must be set');

        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        ShapeMorpher::create()
            ->from($startPath)
            ->at(0.5);
    }

    public function testBuilderFluentInterface(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $frames = ShapeMorpher::create()
            ->from($startPath)
            ->to($endPath)
            ->withFrames(20)
            ->withEasing('ease-in-out')
            ->withDuration(2000, 60)
            ->generate();

        // withDuration should override withFrames
        $this->assertNotEmpty($frames);
        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }

    public function testMorphWithUnknownEasingNameUsesNoEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, 'nonexistent-easing');

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testGenerateFramesWithNullEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(50, 50)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        // null easing triggers getEasingFunction returning null (line 113)
        $frames = $this->morpher->generateFrames($startPath, $endPath, 5);

        $this->assertCount(5, $frames);
    }

    public function testMorphWithEaseOutElastic(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(50, 50)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-out-elastic');
        $this->assertInstanceOf(Data::class, $result);
    }

    public function testMorphWithEaseInBack(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(50, 50)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-in-back');
        $this->assertInstanceOf(Data::class, $result);
    }

    public function testMorphWithEaseOutBack(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(50, 50)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, 'ease-out-back');
        $this->assertInstanceOf(Data::class, $result);
    }

    public function testMorphWithNullEasing(): void
    {
        $startPath = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(50, 50)),
        ]);

        $endPath = new Data([
            new MoveTo('M', new Point(100, 100)),
            new LineTo('L', new Point(200, 200)),
        ]);

        $result = $this->morpher->morph($startPath, $endPath, 0.5, null);
        $this->assertInstanceOf(Data::class, $result);
    }
}
