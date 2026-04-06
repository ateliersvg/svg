<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Geometry\Point;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Point::class)]
final class PointTest extends TestCase
{
    public function testAdd(): void
    {
        $point1 = new Point(10, 20);
        $point2 = new Point(5, 3);
        $result = $point1->add($point2);
        $this->assertEquals(15, $result->x);
        $this->assertEquals(23, $result->y);
    }

    public function testSubtract(): void
    {
        $point1 = new Point(10, 20);
        $point2 = new Point(5, 3);
        $result = $point1->subtract($point2);
        $this->assertEquals(5, $result->x);
        $this->assertEquals(17, $result->y);
    }

    public function testDistanceTo(): void
    {
        $point1 = new Point(0, 0);
        $point2 = new Point(3, 4);
        $distance = $point1->distanceTo($point2);
        $this->assertEquals(5, $distance);
    }

    public function testToString(): void
    {
        $point = new Point(10, 20);
        $this->assertEquals('10,20', (string) $point);
    }

    public function testEqualsReturnsTrueForSamePoint(): void
    {
        $point1 = new Point(10, 20);
        $point2 = new Point(10, 20);
        $this->assertTrue($point1->equals($point2));
    }

    public function testEqualsReturnsFalseForDifferentPoints(): void
    {
        $point1 = new Point(10, 20);
        $point2 = new Point(11, 21);
        $this->assertFalse($point1->equals($point2));
    }

    public function testEqualsWithinEpsilon(): void
    {
        $point1 = new Point(10, 20);
        $point2 = new Point(10.00005, 20.00005);
        $this->assertTrue($point1->equals($point2));
    }

    public function testEqualsOutsideEpsilon(): void
    {
        $point1 = new Point(10, 20);
        $point2 = new Point(10.001, 20);
        $this->assertFalse($point1->equals($point2));
    }

    public function testEqualsWithCustomEpsilon(): void
    {
        $point1 = new Point(10, 20);
        $point2 = new Point(10.5, 20.5);
        $this->assertTrue($point1->equals($point2, 1.0));
    }
}
