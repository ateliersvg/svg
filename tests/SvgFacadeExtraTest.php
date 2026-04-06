<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests;

use Atelier\Svg\Exception\RuntimeException;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Morphing\ShapeMorpher;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Svg;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Svg::class)]
final class SvgFacadeExtraTest extends TestCase
{
    public function testCreateMorpher(): void
    {
        $morpher = Svg::createMorpher();

        $this->assertInstanceOf(ShapeMorpher::class, $morpher);
    }

    public function testOptimizeWith(): void
    {
        $svg = Svg::fromString('<svg width="100" height="100"><rect x="10" y="10"/></svg>');
        $result = $svg->optimizeWith([]);

        $this->assertInstanceOf(Svg::class, $result);
    }

    public function testGroup(): void
    {
        $svg = Svg::create(400, 300);
        $builder = $svg->group();

        $this->assertNotNull($builder);
    }

    public function testSaveToInvalidPathThrowsException(): void
    {
        $svg = Svg::create(100, 100);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to write SVG to file');

        $svg->save('/nonexistent/directory/that/does/not/exist/file.svg');
    }

    public function testMorph(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);
        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        $result = Svg::morph($start, $end, 0.5);

        $this->assertInstanceOf(Data::class, $result);
    }

    public function testMorphFrames(): void
    {
        $start = new Data([
            new MoveTo('M', new Point(0, 0)),
            new CurveTo('C', new Point(10, 10), new Point(20, 20), new Point(30, 30)),
        ]);
        $end = new Data([
            new MoveTo('M', new Point(100, 100)),
            new CurveTo('C', new Point(110, 110), new Point(120, 120), new Point(130, 130)),
        ]);

        $frames = Svg::morphFrames($start, $end, 3);

        $this->assertCount(3, $frames);
        foreach ($frames as $frame) {
            $this->assertInstanceOf(Data::class, $frame);
        }
    }
}
