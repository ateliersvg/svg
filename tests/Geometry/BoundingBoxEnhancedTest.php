<?php

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Geometry\BoundingBox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoundingBox::class)]
final class BoundingBoxEnhancedTest extends TestCase
{
    public function testUnion(): void
    {
        $bbox1 = new BoundingBox(0, 0, 50, 50);
        $bbox2 = new BoundingBox(30, 30, 80, 80);

        $union = $bbox1->union($bbox2);

        $this->assertEquals(0, $union->minX);
        $this->assertEquals(0, $union->minY);
        $this->assertEquals(80, $union->maxX);
        $this->assertEquals(80, $union->maxY);
    }

    public function testIntersect(): void
    {
        $bbox1 = new BoundingBox(0, 0, 50, 50);
        $bbox2 = new BoundingBox(30, 30, 80, 80);

        $intersection = $bbox1->intersect($bbox2);

        $this->assertNotNull($intersection);
        $this->assertEquals(30, $intersection->minX);
        $this->assertEquals(30, $intersection->minY);
        $this->assertEquals(50, $intersection->maxX);
        $this->assertEquals(50, $intersection->maxY);
    }

    public function testIntersectNoOverlap(): void
    {
        $bbox1 = new BoundingBox(0, 0, 50, 50);
        $bbox2 = new BoundingBox(100, 100, 150, 150);

        $intersection = $bbox1->intersect($bbox2);

        $this->assertNull($intersection);
    }

    public function testIntersects(): void
    {
        $bbox1 = new BoundingBox(0, 0, 50, 50);
        $bbox2 = new BoundingBox(30, 30, 80, 80);
        $bbox3 = new BoundingBox(100, 100, 150, 150);

        $this->assertTrue($bbox1->intersects($bbox2));
        $this->assertFalse($bbox1->intersects($bbox3));
    }

    public function testExpand(): void
    {
        $bbox = new BoundingBox(10, 10, 50, 50);

        $expanded = $bbox->expand(5);

        $this->assertEquals(5, $expanded->minX);
        $this->assertEquals(5, $expanded->minY);
        $this->assertEquals(55, $expanded->maxX);
        $this->assertEquals(55, $expanded->maxY);
    }

    public function testGetAnchorTopLeft(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $point = $bbox->getAnchor('top-left');

        $this->assertEquals(10, $point->x);
        $this->assertEquals(20, $point->y);
    }

    public function testGetAnchorCenter(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $point = $bbox->getAnchor('center');

        $this->assertEquals(35, $point->x);
        $this->assertEquals(50, $point->y);
    }

    public function testGetAnchorBottomRight(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $point = $bbox->getAnchor('bottom-right');

        $this->assertEquals(60, $point->x);
        $this->assertEquals(80, $point->y);
    }

    public function testGetAnchorAliases(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $tl = $bbox->getAnchor('tl');
        $tc = $bbox->getAnchor('tc');
        $c = $bbox->getAnchor('c');

        $this->assertEquals(10, $tl->x);
        $this->assertEquals(20, $tl->y);
        $this->assertEquals(35, $tc->x);
        $this->assertEquals(20, $tc->y);
        $this->assertEquals(35, $c->x);
        $this->assertEquals(50, $c->y);
    }

    public function testGetAnchorInvalid(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $this->expectException(\InvalidArgumentException::class);
        $bbox->getAnchor('invalid');
    }

    public function testGetXY(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $this->assertEquals(10, $bbox->getX());
        $this->assertEquals(20, $bbox->getY());
    }

    public function testGetWidthHeight(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $this->assertEquals(50, $bbox->getWidth());
        $this->assertEquals(60, $bbox->getHeight());
    }

    public function testGetCenterXY(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $this->assertEquals(35.0, $bbox->getCenterX());
        $this->assertEquals(50.0, $bbox->getCenterY());
    }

    public function testGetArea(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $this->assertEquals(3000.0, $bbox->getArea());
    }

    public function testGetPerimeter(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $this->assertEquals(220.0, $bbox->getPerimeter());
    }

    public function testExpandWithNegativeMargin(): void
    {
        $bbox = new BoundingBox(10, 10, 50, 50);

        $shrunk = $bbox->expand(-5);

        $this->assertEquals(15.0, $shrunk->minX);
        $this->assertEquals(15.0, $shrunk->minY);
        $this->assertEquals(45.0, $shrunk->maxX);
        $this->assertEquals(45.0, $shrunk->maxY);
    }

    public function testGetAnchorAllPositions(): void
    {
        $bbox = new BoundingBox(10, 20, 60, 80);

        $tr = $bbox->getAnchor('top-right');
        $this->assertEquals(60.0, $tr->x);
        $this->assertEquals(20.0, $tr->y);

        $top = $bbox->getAnchor('top');
        $this->assertEquals(35.0, $top->x);
        $this->assertEquals(20.0, $top->y);

        $cl = $bbox->getAnchor('center-left');
        $this->assertEquals(10.0, $cl->x);
        $this->assertEquals(50.0, $cl->y);

        $left = $bbox->getAnchor('left');
        $this->assertEquals(10.0, $left->x);
        $this->assertEquals(50.0, $left->y);

        $cr = $bbox->getAnchor('center-right');
        $this->assertEquals(60.0, $cr->x);
        $this->assertEquals(50.0, $cr->y);

        $right = $bbox->getAnchor('right');
        $this->assertEquals(60.0, $right->x);
        $this->assertEquals(50.0, $right->y);

        $bl = $bbox->getAnchor('bottom-left');
        $this->assertEquals(10.0, $bl->x);
        $this->assertEquals(80.0, $bl->y);

        $bc = $bbox->getAnchor('bottom-center');
        $this->assertEquals(35.0, $bc->x);
        $this->assertEquals(80.0, $bc->y);

        $bottom = $bbox->getAnchor('bottom');
        $this->assertEquals(35.0, $bottom->x);
        $this->assertEquals(80.0, $bottom->y);

        $br2 = $bbox->getAnchor('br');
        $this->assertEquals(60.0, $br2->x);
        $this->assertEquals(80.0, $br2->y);
    }
}
