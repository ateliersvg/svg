<?php

namespace Atelier\Svg\Tests\Path\Segment;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\SmoothCurveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SmoothCurveTo::class)]
final class SmoothCurveToTest extends TestCase
{
    public function testConstructWithValidCommand(): void
    {
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $smoothCurveTo = new SmoothCurveTo('S', $cp2, $point);
        $this->assertSame($cp2, $smoothCurveTo->getControlPoint2());
        $this->assertSame($point, $smoothCurveTo->getTargetPoint());

        $smoothCurveTo = new SmoothCurveTo('s', $cp2, $point);
        $this->assertSame($cp2, $smoothCurveTo->getControlPoint2());
        $this->assertSame($point, $smoothCurveTo->getTargetPoint());
    }

    public function testConstructWithInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        new SmoothCurveTo('C', $cp2, $point);
    }

    public function testGetTargetPoint(): void
    {
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $smoothCurveTo = new SmoothCurveTo('S', $cp2, $point);
        $this->assertSame($point, $smoothCurveTo->getTargetPoint());
    }

    public function testGetControlPoint2(): void
    {
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $smoothCurveTo = new SmoothCurveTo('S', $cp2, $point);
        $this->assertSame($cp2, $smoothCurveTo->getControlPoint2());
    }

    public function testCommandArgumentsToString(): void
    {
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $smoothCurveTo = new SmoothCurveTo('S', $cp2, $point);
        $this->assertSame('30,40 50,60', $smoothCurveTo->commandArgumentsToString());

        $cp2 = new Point(25.5, -35);
        $point = new Point(45.25, 55.75);
        $smoothCurveTo = new SmoothCurveTo('s', $cp2, $point);
        $this->assertSame('25.5,-35 45.25,55.75', $smoothCurveTo->commandArgumentsToString());
    }

    public function testIsRelative(): void
    {
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $smoothCurveTo = new SmoothCurveTo('S', $cp2, $point);
        $this->assertFalse($smoothCurveTo->isRelative());

        $smoothCurveTo = new SmoothCurveTo('s', $cp2, $point);
        $this->assertTrue($smoothCurveTo->isRelative());
    }
}
