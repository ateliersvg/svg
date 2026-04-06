<?php

namespace Atelier\Svg\Tests\Path\Segment;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\SmoothQuadraticCurveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SmoothQuadraticCurveTo::class)]
final class SmoothQuadraticCurveToTest extends TestCase
{
    public function testConstructWithValidCommand(): void
    {
        $point = new Point(30, 40);
        $smoothQuadraticCurveTo = new SmoothQuadraticCurveTo('T', $point);
        $this->assertSame($point, $smoothQuadraticCurveTo->getTargetPoint());

        $smoothQuadraticCurveTo = new SmoothQuadraticCurveTo('t', $point);
        $this->assertSame($point, $smoothQuadraticCurveTo->getTargetPoint());
    }

    public function testConstructWithInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $point = new Point(30, 40);
        new SmoothQuadraticCurveTo('Q', $point);
    }

    public function testGetTargetPoint(): void
    {
        $point = new Point(30, 40);
        $smoothQuadraticCurveTo = new SmoothQuadraticCurveTo('T', $point);
        $this->assertSame($point, $smoothQuadraticCurveTo->getTargetPoint());
    }

    public function testCommandArgumentsToString(): void
    {
        $point = new Point(30, 40);
        $smoothQuadraticCurveTo = new SmoothQuadraticCurveTo('T', $point);
        $this->assertSame('30,40', $smoothQuadraticCurveTo->commandArgumentsToString());

        $point = new Point(-25.5, 35.75);
        $smoothQuadraticCurveTo = new SmoothQuadraticCurveTo('t', $point);
        $this->assertSame('-25.5,35.75', $smoothQuadraticCurveTo->commandArgumentsToString());
    }

    public function testIsRelative(): void
    {
        $point = new Point(30, 40);
        $smoothQuadraticCurveTo = new SmoothQuadraticCurveTo('T', $point);
        $this->assertFalse($smoothQuadraticCurveTo->isRelative());

        $smoothQuadraticCurveTo = new SmoothQuadraticCurveTo('t', $point);
        $this->assertTrue($smoothQuadraticCurveTo->isRelative());
    }
}
