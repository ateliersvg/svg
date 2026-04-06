<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Geometry\BoundingBox;
use Atelier\Svg\Geometry\Point;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoundingBox::class)]
final class BoundingBoxCoverageTest extends TestCase
{
    public function testGetCenter(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $center = $bbox->getCenter();

        $this->assertInstanceOf(Point::class, $center);
        $this->assertSame(35.0, $center->x);
        $this->assertSame(50.0, $center->y);
    }

    public function testContainsPointInside(): void
    {
        $bbox = new BoundingBox(0, 0, 100, 100);

        $this->assertTrue($bbox->contains(new Point(50, 50)));
    }

    public function testContainsPointOnBoundary(): void
    {
        $bbox = new BoundingBox(0, 0, 100, 100);

        $this->assertTrue($bbox->contains(new Point(0, 0)));
        $this->assertTrue($bbox->contains(new Point(100, 100)));
    }

    public function testContainsPointOutside(): void
    {
        $bbox = new BoundingBox(0, 0, 100, 100);

        $this->assertFalse($bbox->contains(new Point(150, 50)));
        $this->assertFalse($bbox->contains(new Point(50, 150)));
        $this->assertFalse($bbox->contains(new Point(-1, 50)));
        $this->assertFalse($bbox->contains(new Point(50, -1)));
    }

    public function testFromPointsEmpty(): void
    {
        $bbox = BoundingBox::fromPoints();

        $this->assertSame(0.0, $bbox->minX);
        $this->assertSame(0.0, $bbox->minY);
        $this->assertSame(0.0, $bbox->maxX);
        $this->assertSame(0.0, $bbox->maxY);
    }

    public function testFromPointsMultiple(): void
    {
        $bbox = BoundingBox::fromPoints(
            new Point(10, 20),
            new Point(50, 80),
            new Point(30, 40),
        );

        $this->assertSame(10.0, $bbox->minX);
        $this->assertSame(20.0, $bbox->minY);
        $this->assertSame(50.0, $bbox->maxX);
        $this->assertSame(80.0, $bbox->maxY);
    }

    public function testFromPointsSingle(): void
    {
        $bbox = BoundingBox::fromPoints(new Point(5, 10));

        $this->assertSame(5.0, $bbox->minX);
        $this->assertSame(10.0, $bbox->minY);
        $this->assertSame(5.0, $bbox->maxX);
        $this->assertSame(10.0, $bbox->maxY);
    }
}
