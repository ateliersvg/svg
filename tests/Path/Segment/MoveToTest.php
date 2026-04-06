<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Path\Segment;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\AbstractSegment;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractSegment::class)]
#[CoversClass(MoveTo::class)]
#[CoversClass(ClosePath::class)]
final class MoveToTest extends TestCase
{
    public function testGetCommand(): void
    {
        $segment = new MoveTo('M', new Point(10, 20));

        $this->assertSame('M', $segment->getCommand());
    }

    public function testGetCommandRelative(): void
    {
        $segment = new MoveTo('m', new Point(10, 20));

        $this->assertSame('m', $segment->getCommand());
    }

    public function testIsRelative(): void
    {
        $absolute = new MoveTo('M', new Point(10, 20));
        $relative = new MoveTo('m', new Point(10, 20));

        $this->assertFalse($absolute->isRelative());
        $this->assertTrue($relative->isRelative());
    }

    public function testGetTargetPoint(): void
    {
        $point = new Point(10, 20);
        $segment = new MoveTo('M', $point);

        $this->assertSame($point, $segment->getTargetPoint());
    }

    public function testToString(): void
    {
        $segment = new MoveTo('M', new Point(10, 20));

        $this->assertStringStartsWith('M', $segment->toString());
    }

    public function testClosePathGetTargetPointReturnsNull(): void
    {
        $segment = new ClosePath('Z');

        $this->assertNull($segment->getTargetPoint());
    }

    public function testClosePathToString(): void
    {
        $segment = new ClosePath('Z');

        $this->assertSame('Z', $segment->toString());
    }

    public function testClosePathIsRelative(): void
    {
        $absolute = new ClosePath('Z');
        $relative = new ClosePath('z');

        $this->assertFalse($absolute->isRelative());
        $this->assertTrue($relative->isRelative());
    }

    public function testInvalidCommandThrowsException(): void
    {
        $this->expectException(\Atelier\Svg\Exception\InvalidArgumentException::class);

        new MoveTo('X', new Point(0, 0));
    }

    public function testInvalidCommandLengthThrowsException(): void
    {
        $this->expectException(\Atelier\Svg\Exception\InvalidArgumentException::class);

        new MoveTo('MM', new Point(0, 0));
    }

    public function testCommandArgumentsToString(): void
    {
        $segment = new MoveTo('M', new Point(10, 20));
        $this->assertSame('10,20', $segment->commandArgumentsToString());
    }

    public function testClosePathCommandArgumentsToString(): void
    {
        $segment = new ClosePath('Z');
        $this->assertSame('', $segment->commandArgumentsToString());
    }
}
