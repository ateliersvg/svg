<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathElement::class)]
final class PathElementSettersTest extends TestCase
{
    public function testSetPathDataReturnsSelf(): void
    {
        $path = new PathElement();
        $result = $path->setPathData('M 0 0 L 10 10');

        $this->assertSame($path, $result);
    }

    public function testSetDataReturnsSelf(): void
    {
        $path = new PathElement();
        $data = new Data([
            new MoveTo('M', new Point(0, 0)),
            new LineTo('L', new Point(10, 10)),
        ]);

        $result = $path->setData($data);

        $this->assertSame($path, $result);
    }

    public function testSetDSetsTheAttribute(): void
    {
        $path = new PathElement();
        $path->setD('M 10 20 L 30 40');

        $this->assertSame('M 10 20 L 30 40', $path->getAttribute('d'));
    }

    public function testSetDReturnsSelf(): void
    {
        $path = new PathElement();
        $result = $path->setD('M 0 0 L 10 10');

        $this->assertSame($path, $result);
    }

    public function testSetDAndSetPathDataProduceSameResult(): void
    {
        $pathData = 'M 10 20 L 30 40 Z';

        $pathA = new PathElement();
        $pathA->setD($pathData);

        $pathB = new PathElement();
        $pathB->setPathData($pathData);

        $this->assertSame($pathA->getAttribute('d'), $pathB->getAttribute('d'));
    }
}
