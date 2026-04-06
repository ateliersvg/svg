<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Value\Viewbox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Viewbox::class)]
final class ViewboxTest extends TestCase
{
    public function testConstruct(): void
    {
        $viewbox = new Viewbox(10, 20, 100, 200);

        $this->assertSame(10.0, $viewbox->getMinX());
        $this->assertSame(20.0, $viewbox->getMinY());
        $this->assertSame(100.0, $viewbox->getWidth());
        $this->assertSame(200.0, $viewbox->getHeight());
    }

    public function testConstructNegativeWidth(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('viewBox width cannot be negative');

        new Viewbox(0, 0, -100, 100);
    }

    public function testConstructNegativeHeight(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('viewBox height cannot be negative');

        new Viewbox(0, 0, 100, -100);
    }

    public function testParseSpaceSeparated(): void
    {
        $viewbox = Viewbox::parse('0 0 100 200');

        $this->assertSame(0.0, $viewbox->getMinX());
        $this->assertSame(0.0, $viewbox->getMinY());
        $this->assertSame(100.0, $viewbox->getWidth());
        $this->assertSame(200.0, $viewbox->getHeight());
    }

    public function testParseCommaSeparated(): void
    {
        $viewbox = Viewbox::parse('10, 20, 100, 200');

        $this->assertSame(10.0, $viewbox->getMinX());
        $this->assertSame(20.0, $viewbox->getMinY());
        $this->assertSame(100.0, $viewbox->getWidth());
        $this->assertSame(200.0, $viewbox->getHeight());
    }

    public function testParseMixedSeparators(): void
    {
        $viewbox = Viewbox::parse('10, 20 100, 200');

        $this->assertSame(10.0, $viewbox->getMinX());
        $this->assertSame(20.0, $viewbox->getMinY());
        $this->assertSame(100.0, $viewbox->getWidth());
        $this->assertSame(200.0, $viewbox->getHeight());
    }

    public function testParseNegativeValues(): void
    {
        $viewbox = Viewbox::parse('-10 -20 100 200');

        $this->assertSame(-10.0, $viewbox->getMinX());
        $this->assertSame(-20.0, $viewbox->getMinY());
    }

    public function testParseFloatValues(): void
    {
        $viewbox = Viewbox::parse('10.5 20.75 100.25 200.5');

        $this->assertSame(10.5, $viewbox->getMinX());
        $this->assertSame(20.75, $viewbox->getMinY());
        $this->assertSame(100.25, $viewbox->getWidth());
        $this->assertSame(200.5, $viewbox->getHeight());
    }

    public function testParseEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot parse an empty string as a viewBox');

        Viewbox::parse('');
    }

    public function testParseTooFewValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('viewBox must have exactly 4 values');

        Viewbox::parse('0 0 100');
    }

    public function testParseTooManyValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('viewBox must have exactly 4 values');

        Viewbox::parse('0 0 100 200 300');
    }

    public function testParseInvalidNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid viewBox');

        Viewbox::parse('0 0 abc 200');
    }

    public function testGetMaxX(): void
    {
        $viewbox = new Viewbox(10, 20, 100, 200);

        $this->assertSame(110.0, $viewbox->getMaxX());
    }

    public function testGetMaxY(): void
    {
        $viewbox = new Viewbox(10, 20, 100, 200);

        $this->assertSame(220.0, $viewbox->getMaxY());
    }

    public function testGetCenterX(): void
    {
        $viewbox = new Viewbox(10, 20, 100, 200);

        $this->assertSame(60.0, $viewbox->getCenterX());
    }

    public function testGetCenterY(): void
    {
        $viewbox = new Viewbox(10, 20, 100, 200);

        $this->assertSame(120.0, $viewbox->getCenterY());
    }

    public function testGetAspectRatio(): void
    {
        $viewbox = new Viewbox(0, 0, 100, 200);

        $this->assertSame(0.5, $viewbox->getAspectRatio());
    }

    public function testGetAspectRatioZeroHeight(): void
    {
        $viewbox = new Viewbox(0, 0, 100, 0);

        $this->assertSame(0.0, $viewbox->getAspectRatio());
    }

    public function testToString(): void
    {
        $viewbox = new Viewbox(10, 20, 100, 200);

        $this->assertSame('10 20 100 200', $viewbox->toString());
    }

    public function testToStringWithFloats(): void
    {
        $viewbox = new Viewbox(10.5, 20.75, 100.25, 200.5);

        $this->assertSame('10.5 20.75 100.25 200.5', $viewbox->toString());
    }

    public function testToStringRemovesTrailingZeros(): void
    {
        $viewbox = new Viewbox(10.0, 20.0, 100.0, 200.0);

        $this->assertSame('10 20 100 200', $viewbox->toString());
    }

    public function testMagicToString(): void
    {
        $viewbox = new Viewbox(10, 20, 100, 200);
        $string = (string) $viewbox;

        $this->assertSame('10 20 100 200', $string);
    }
}
