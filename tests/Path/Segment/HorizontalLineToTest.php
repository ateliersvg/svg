<?php

namespace Atelier\Svg\Tests\Path\Segment;

use Atelier\Svg\Path\Segment\HorizontalLineTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HorizontalLineTo::class)]
final class HorizontalLineToTest extends TestCase
{
    public function testConstructWithValidCommand(): void
    {
        $horizontalLineTo = new HorizontalLineTo('H', 100.0);
        $this->assertSame(100.0, $horizontalLineTo->getX());

        $horizontalLineTo = new HorizontalLineTo('h', 50.5);
        $this->assertSame(50.5, $horizontalLineTo->getX());
    }

    public function testConstructWithInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new HorizontalLineTo('L', 100.0);
    }

    public function testGetX(): void
    {
        $horizontalLineTo = new HorizontalLineTo('H', 123.45);
        $this->assertSame(123.45, $horizontalLineTo->getX());
    }

    public function testCommandArgumentsToString(): void
    {
        $horizontalLineTo = new HorizontalLineTo('H', 100.0);
        $this->assertSame('100', $horizontalLineTo->commandArgumentsToString());

        $horizontalLineTo = new HorizontalLineTo('h', -50.5);
        $this->assertSame('-50.5', $horizontalLineTo->commandArgumentsToString());
    }

    public function testIsRelative(): void
    {
        $horizontalLineTo = new HorizontalLineTo('H', 100.0);
        $this->assertFalse($horizontalLineTo->isRelative());

        $horizontalLineTo = new HorizontalLineTo('h', 100.0);
        $this->assertTrue($horizontalLineTo->isRelative());
    }

    public function testGetTargetPoint(): void
    {
        $horizontalLineTo = new HorizontalLineTo('H', 100.0);
        $this->assertNull($horizontalLineTo->getTargetPoint());
    }
}
