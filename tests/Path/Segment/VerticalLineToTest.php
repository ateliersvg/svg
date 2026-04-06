<?php

namespace Atelier\Svg\Tests\Path\Segment;

use Atelier\Svg\Path\Segment\VerticalLineTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerticalLineTo::class)]
final class VerticalLineToTest extends TestCase
{
    public function testConstructWithValidCommand(): void
    {
        $verticalLineTo = new VerticalLineTo('V', 100.0);
        $this->assertSame(100.0, $verticalLineTo->getY());

        $verticalLineTo = new VerticalLineTo('v', 50.5);
        $this->assertSame(50.5, $verticalLineTo->getY());
    }

    public function testConstructWithInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new VerticalLineTo('L', 100.0);
    }

    public function testGetY(): void
    {
        $verticalLineTo = new VerticalLineTo('V', 123.45);
        $this->assertSame(123.45, $verticalLineTo->getY());
    }

    public function testCommandArgumentsToString(): void
    {
        $verticalLineTo = new VerticalLineTo('V', 100.0);
        $this->assertSame('100', $verticalLineTo->commandArgumentsToString());

        $verticalLineTo = new VerticalLineTo('v', -50.5);
        $this->assertSame('-50.5', $verticalLineTo->commandArgumentsToString());
    }

    public function testIsRelative(): void
    {
        $verticalLineTo = new VerticalLineTo('V', 100.0);
        $this->assertFalse($verticalLineTo->isRelative());

        $verticalLineTo = new VerticalLineTo('v', 100.0);
        $this->assertTrue($verticalLineTo->isRelative());
    }

    public function testGetTargetPoint(): void
    {
        $verticalLineTo = new VerticalLineTo('V', 100.0);
        $this->assertNull($verticalLineTo->getTargetPoint());
    }
}
