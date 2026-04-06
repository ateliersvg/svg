<?php

namespace Atelier\Svg\Path\Segment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClosePath::class)]
final class ClosePathTest extends TestCase
{
    public function testConstructorWithValidCommand(): void
    {
        $segment = new ClosePath('Z');
        $this->assertSame('Z', $segment->getCommand());

        $segment = new ClosePath('z');
        $this->assertSame('z', $segment->getCommand());
    }

    public function testConstructorWithInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ClosePath segment must use Z or z command.');
        new ClosePath('M');
    }

    public function testConstructorWithDefaultCommand(): void
    {
        $segment = new ClosePath();
        $this->assertSame('Z', $segment->getCommand());
    }
}
