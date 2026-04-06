<?php

namespace Atelier\Svg\Tests\Path\Segment;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\QuadraticCurveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QuadraticCurveTo::class)]
final class QuadraticCurveToTest extends TestCase
{
    public function testConstructWithValidCommand(): void
    {
        $cp = new Point(10, 20);
        $point = new Point(30, 40);
        $quadraticCurveTo = new QuadraticCurveTo('Q', $cp, $point);
        $this->assertSame($cp, $quadraticCurveTo->getControlPoint());
        $this->assertSame($point, $quadraticCurveTo->getTargetPoint());

        $quadraticCurveTo = new QuadraticCurveTo('q', $cp, $point);
        $this->assertSame($cp, $quadraticCurveTo->getControlPoint());
        $this->assertSame($point, $quadraticCurveTo->getTargetPoint());
    }

    public function testConstructWithInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $cp = new Point(10, 20);
        $point = new Point(30, 40);
        new QuadraticCurveTo('C', $cp, $point);
    }

    public function testGetTargetPoint(): void
    {
        $cp = new Point(10, 20);
        $point = new Point(30, 40);
        $quadraticCurveTo = new QuadraticCurveTo('Q', $cp, $point);
        $this->assertSame($point, $quadraticCurveTo->getTargetPoint());
    }

    public function testGetControlPoint(): void
    {
        $cp = new Point(10, 20);
        $point = new Point(30, 40);
        $quadraticCurveTo = new QuadraticCurveTo('Q', $cp, $point);
        $this->assertSame($cp, $quadraticCurveTo->getControlPoint());
    }

    public function testCommandArgumentsToString(): void
    {
        $cp = new Point(10, 20);
        $point = new Point(30, 40);
        $quadraticCurveTo = new QuadraticCurveTo('Q', $cp, $point);
        $this->assertSame('10,20 30,40', $quadraticCurveTo->commandArgumentsToString());

        $cp = new Point(-5, 15.5);
        $point = new Point(25.5, -35);
        $quadraticCurveTo = new QuadraticCurveTo('q', $cp, $point);
        $this->assertSame('-5,15.5 25.5,-35', $quadraticCurveTo->commandArgumentsToString());
    }

    public function testIsRelative(): void
    {
        $cp = new Point(10, 20);
        $point = new Point(30, 40);
        $quadraticCurveTo = new QuadraticCurveTo('Q', $cp, $point);
        $this->assertFalse($quadraticCurveTo->isRelative());

        $quadraticCurveTo = new QuadraticCurveTo('q', $cp, $point);
        $this->assertTrue($quadraticCurveTo->isRelative());
    }
}
