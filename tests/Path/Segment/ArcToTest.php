<?php

namespace Atelier\Svg\Tests\Path\Segment;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\ArcTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArcTo::class)]
final class ArcToTest extends TestCase
{
    public function testConstructWithValidCommand(): void
    {
        $point = new Point(100, 50);
        $arcTo = new ArcTo('A', 25.0, 50.0, 0.0, false, true, $point);
        $this->assertSame(25.0, $arcTo->getRx());
        $this->assertSame(50.0, $arcTo->getRy());
        $this->assertSame(0.0, $arcTo->getXAxisRotation());
        $this->assertFalse($arcTo->getLargeArcFlag());
        $this->assertTrue($arcTo->getSweepFlag());
        $this->assertSame($point, $arcTo->getTargetPoint());

        $arcTo = new ArcTo('a', 25.0, 50.0, 0.0, false, true, $point);
        $this->assertSame($point, $arcTo->getTargetPoint());
    }

    public function testConstructWithInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $point = new Point(100, 50);
        new ArcTo('L', 25.0, 50.0, 0.0, false, true, $point);
    }

    public function testGetTargetPoint(): void
    {
        $point = new Point(100, 50);
        $arcTo = new ArcTo('A', 25.0, 50.0, 0.0, false, true, $point);
        $this->assertSame($point, $arcTo->getTargetPoint());
    }

    public function testGetRadii(): void
    {
        $point = new Point(100, 50);
        $arcTo = new ArcTo('A', 25.5, 50.75, 0.0, false, true, $point);
        $this->assertSame(25.5, $arcTo->getRx());
        $this->assertSame(50.75, $arcTo->getRy());
    }

    public function testGetXAxisRotation(): void
    {
        $point = new Point(100, 50);
        $arcTo = new ArcTo('A', 25.0, 50.0, 45.5, false, true, $point);
        $this->assertSame(45.5, $arcTo->getXAxisRotation());
    }

    public function testGetFlags(): void
    {
        $point = new Point(100, 50);
        $arcTo = new ArcTo('A', 25.0, 50.0, 0.0, true, false, $point);
        $this->assertTrue($arcTo->getLargeArcFlag());
        $this->assertFalse($arcTo->getSweepFlag());
    }

    public function testCommandArgumentsToString(): void
    {
        $point = new Point(100, 50);
        $arcTo = new ArcTo('A', 25.0, 50.0, 0.0, false, true, $point);
        $this->assertSame('25,50 0 0,1 100,50', $arcTo->commandArgumentsToString());

        $arcTo = new ArcTo('A', 25.0, 50.0, 45.0, true, false, $point);
        $this->assertSame('25,50 45 1,0 100,50', $arcTo->commandArgumentsToString());

        $point = new Point(-10.5, 20.25);
        $arcTo = new ArcTo('a', 15.5, 30.75, -30.0, false, true, $point);
        $this->assertSame('15.5,30.75 -30 0,1 -10.5,20.25', $arcTo->commandArgumentsToString());
    }

    public function testIsRelative(): void
    {
        $point = new Point(100, 50);
        $arcTo = new ArcTo('A', 25.0, 50.0, 0.0, false, true, $point);
        $this->assertFalse($arcTo->isRelative());

        $arcTo = new ArcTo('a', 25.0, 50.0, 0.0, false, true, $point);
        $this->assertTrue($arcTo->isRelative());
    }
}
