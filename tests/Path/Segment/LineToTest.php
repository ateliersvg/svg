<?php

namespace Atelier\Svg\Tests\Path\Segment;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\LineTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LineTo::class)]
final class LineToTest extends TestCase
{
    public function testConstructWithValidCommand(): void
    {
        $point = new Point(10, 20);
        $lineTo = new LineTo('L', $point);
        $this->assertSame($point, $lineTo->getTargetPoint());

        $point = new Point(5, 15);
        $lineTo = new LineTo('l', $point);
        $this->assertSame($point, $lineTo->getTargetPoint());
    }

    public function testConstructWithInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $point = new Point(10, 20);
        new LineTo('M', $point);
    }

    public function testGetTargetPoint(): void
    {
        $point = new Point(10, 20);
        $lineTo = new LineTo('L', $point);
        $this->assertSame($point, $lineTo->getTargetPoint());
    }

    public function testCommandArgumentsToString(): void
    {
        $point = new Point(10, 20);
        $lineTo = new LineTo('L', $point);
        $this->assertSame('10,20', $lineTo->commandArgumentsToString());

        $point = new Point(-5, 15.5);
        $lineTo = new LineTo('l', $point);
        $this->assertSame('-5,15.5', $lineTo->commandArgumentsToString());
    }
}
