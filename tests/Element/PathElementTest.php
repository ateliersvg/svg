<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathElement::class)]
final class PathElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $path = new PathElement();

        $this->assertSame('path', $path->getTagName());
    }

    public function testGetTagName(): void
    {
        $path = new PathElement();

        $this->assertSame('path', $path->getTagName());
    }

    public function testSetPathData(): void
    {
        $path = new PathElement();
        $result = $path->setPathData('M 10 20 L 30 40');

        $this->assertSame($path, $result);
        $this->assertSame('M 10 20 L 30 40', $path->getAttribute('d'));
    }

    public function testGetPathData(): void
    {
        $path = new PathElement();
        $path->setPathData('M 0 0 L 100 100');

        $pathData = $path->getPathData();
        $this->assertSame('M 0 0 L 100 100', $pathData);
    }

    public function testGetPathDataWhenNotSet(): void
    {
        $path = new PathElement();

        $this->assertNull($path->getPathData());
    }

    public function testSetDataWithSegments(): void
    {
        $path = new PathElement();
        $data = new Data([
            new MoveTo('M', new Point(10, 20)),
            new LineTo('L', new Point(30, 40)),
        ]);

        $result = $path->setData($data);

        $this->assertSame($path, $result);
        $this->assertSame('M10,20L30,40', $path->getAttribute('d'));
    }

    public function testSetDataWithClosePath(): void
    {
        $path = new PathElement();
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(100, 0)),
            new LineTo('L', new Point(100, 100)),
            new ClosePath('Z'),
        ]);

        $path->setData($data);

        $this->assertSame('M0,0L100,0L100,100Z', $path->getAttribute('d'));
    }

    public function testSetDataWithRelativeCommands(): void
    {
        $path = new PathElement();
        $data = new Data([
            new MoveTo('M', new Point(10, 10)),
            new LineTo('l', new Point(20, 0)),
            new LineTo('l', new Point(0, 20)),
        ]);

        $path->setData($data);

        $this->assertSame('M10,10l20,0l0,20', $path->getAttribute('d'));
    }

    public function testSetDataWithEmptyData(): void
    {
        $path = new PathElement();
        $data = new Data([]);

        $path->setData($data);

        $this->assertSame('', $path->getAttribute('d'));
    }

    public function testGetDataReturnsParsedData(): void
    {
        $path = new PathElement();
        $path->setPathData('M 10 20 L 30 40');

        $data = $path->getData();
        $this->assertInstanceOf(Data::class, $data);
        $this->assertCount(2, $data->getSegments());
    }

    public function testGetDataWhenNoPathDataSet(): void
    {
        $path = new PathElement();

        $this->assertNull($path->getData());
    }

    public function testSetAttribute(): void
    {
        $path = new PathElement();
        $result = $path->setAttribute('id', 'my-path');

        $this->assertSame($path, $result);
        $this->assertSame('my-path', $path->getAttribute('id'));
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $path = new PathElement();

        $this->assertNull($path->getAttribute('id'));
    }

    public function testHasAttribute(): void
    {
        $path = new PathElement();
        $path->setAttribute('stroke', 'black');

        $this->assertTrue($path->hasAttribute('stroke'));
        $this->assertFalse($path->hasAttribute('fill'));
    }

    public function testRemoveAttribute(): void
    {
        $path = new PathElement();
        $path->setAttribute('fill', 'red');
        $this->assertTrue($path->hasAttribute('fill'));

        $result = $path->removeAttribute('fill');
        $this->assertSame($path, $result);
        $this->assertFalse($path->hasAttribute('fill'));
    }

    public function testGetAttributes(): void
    {
        $path = new PathElement();
        $path->setPathData('M 0 0 L 100 100');
        $path->setAttribute('stroke', 'black');
        $path->setAttribute('fill', 'none');

        $attributes = $path->getAttributes();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('d', $attributes);
        $this->assertArrayHasKey('stroke', $attributes);
        $this->assertArrayHasKey('fill', $attributes);
        $this->assertSame('M 0 0 L 100 100', $attributes['d']);
        $this->assertSame('black', $attributes['stroke']);
        $this->assertSame('none', $attributes['fill']);
    }

    public function testParentRelationship(): void
    {
        $path = new PathElement();
        $parent = new \Atelier\Svg\Element\Structural\GroupElement();

        $path->setParent($parent);

        $this->assertSame($parent, $path->getParent());
    }

    public function testParentIsNullByDefault(): void
    {
        $path = new PathElement();

        $this->assertNull($path->getParent());
    }

    public function testComplexPathData(): void
    {
        $path = new PathElement();
        $complexPath = 'M 10,30 A 20,20 0,0,1 50,30 A 20,20 0,0,1 90,30 Q 90,60 50,90 Q 10,60 10,30 z';
        $path->setPathData($complexPath);

        $this->assertSame($complexPath, $path->getPathData());
    }

    public function testPathDataCanBeUpdated(): void
    {
        $path = new PathElement();
        $path->setPathData('M 0 0 L 10 10');
        $this->assertSame('M 0 0 L 10 10', $path->getPathData());

        $path->setPathData('M 20 20 L 30 30');
        $this->assertSame('M 20 20 L 30 30', $path->getPathData());
    }
}
