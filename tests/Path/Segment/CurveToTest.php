<?php

namespace Atelier\Svg\Tests\Path\Segment;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\CurveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CurveTo::class)]
final class CurveToTest extends TestCase
{
    public function testConstructWithValidCommand(): void
    {
        $cp1 = new Point(10, 20);
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $curveTo = new CurveTo('C', $cp1, $cp2, $point);
        $this->assertSame($cp1, $curveTo->getControlPoint1());
        $this->assertSame($cp2, $curveTo->getControlPoint2());
        $this->assertSame($point, $curveTo->getTargetPoint());

        $curveTo = new CurveTo('c', $cp1, $cp2, $point);
        $this->assertSame($cp1, $curveTo->getControlPoint1());
        $this->assertSame($cp2, $curveTo->getControlPoint2());
        $this->assertSame($point, $curveTo->getTargetPoint());
    }

    public function testConstructWithInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $cp1 = new Point(10, 20);
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        new CurveTo('L', $cp1, $cp2, $point);
    }

    public function testGetTargetPoint(): void
    {
        $cp1 = new Point(10, 20);
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $curveTo = new CurveTo('C', $cp1, $cp2, $point);
        $this->assertSame($point, $curveTo->getTargetPoint());
    }

    public function testGetControlPoints(): void
    {
        $cp1 = new Point(10, 20);
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $curveTo = new CurveTo('C', $cp1, $cp2, $point);
        $this->assertSame($cp1, $curveTo->getControlPoint1());
        $this->assertSame($cp2, $curveTo->getControlPoint2());
    }

    public function testCommandArgumentsToString(): void
    {
        $cp1 = new Point(10, 20);
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $curveTo = new CurveTo('C', $cp1, $cp2, $point);
        $this->assertSame('10,20 30,40 50,60', $curveTo->commandArgumentsToString());

        $cp1 = new Point(-5, 15.5);
        $cp2 = new Point(25.5, -35);
        $point = new Point(45.25, 55.75);
        $curveTo = new CurveTo('c', $cp1, $cp2, $point);
        $this->assertSame('-5,15.5 25.5,-35 45.25,55.75', $curveTo->commandArgumentsToString());
    }

    public function testIsRelative(): void
    {
        $cp1 = new Point(10, 20);
        $cp2 = new Point(30, 40);
        $point = new Point(50, 60);
        $curveTo = new CurveTo('C', $cp1, $cp2, $point);
        $this->assertFalse($curveTo->isRelative());

        $curveTo = new CurveTo('c', $cp1, $cp2, $point);
        $this->assertTrue($curveTo->isRelative());
    }
}
