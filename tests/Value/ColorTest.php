<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Value\Color;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Color::class)]
final class ColorTest extends TestCase
{
    public function testFromRgb(): void
    {
        $color = Color::fromRgb(255, 128, 64);

        $this->assertSame(255, $color->getRed());
        $this->assertSame(128, $color->getGreen());
        $this->assertSame(64, $color->getBlue());
        $this->assertSame(1.0, $color->getAlpha());
        $this->assertTrue($color->isOpaque());
    }

    public function testFromRgbWithAlpha(): void
    {
        $color = Color::fromRgb(255, 128, 64, 0.5);

        $this->assertSame(0.5, $color->getAlpha());
        $this->assertFalse($color->isOpaque());
        $this->assertFalse($color->isTransparent());
    }

    public function testFromRgbInvalidValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::fromRgb(256, 0, 0);
    }

    public function testParseNamedColor(): void
    {
        $color = Color::parse('red');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(0, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
    }

    public function testParseHexShort(): void
    {
        $color = Color::parse('#F80');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(136, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
    }

    public function testParseHexLong(): void
    {
        $color = Color::parse('#FF8800');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(136, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
    }

    public function testParseHexWithAlpha(): void
    {
        $color = Color::parse('#FF880080');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(136, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
        $this->assertEqualsWithDelta(0.5, $color->getAlpha(), 0.01);
    }

    public function testParseRgb(): void
    {
        $color = Color::parse('rgb(255, 128, 64)');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(128, $color->getGreen());
        $this->assertSame(64, $color->getBlue());
    }

    public function testParseRgba(): void
    {
        $color = Color::parse('rgba(255, 128, 64, 0.75)');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(128, $color->getGreen());
        $this->assertSame(64, $color->getBlue());
        $this->assertSame(0.75, $color->getAlpha());
    }

    public function testParseRgbWithPercentages(): void
    {
        $color = Color::parse('rgb(100%, 50%, 25%)');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(128, $color->getGreen());
        $this->assertSame(64, $color->getBlue());
    }

    public function testParseHsl(): void
    {
        $color = Color::parse('hsl(0, 100%, 50%)');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(0, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
    }

    public function testParseTransparent(): void
    {
        $color = Color::parse('transparent');

        $this->assertTrue($color->isTransparent());
        $this->assertSame(0.0, $color->getAlpha());
    }

    public function testParseNone(): void
    {
        $color = Color::parse('none');

        $this->assertTrue($color->isTransparent());
        $this->assertSame('none', $color->toString());
    }

    public function testParseCurrentColor(): void
    {
        $color = Color::parse('currentColor');

        $this->assertSame('currentcolor', $color->toString());
    }

    public function testParseInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::parse('invalid');
    }

    public function testParseEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::parse('');
    }

    public function testToHex(): void
    {
        $color = Color::fromRgb(255, 128, 64);

        $this->assertSame('#ff8040', $color->toHex());
    }

    public function testToHexWithAlpha(): void
    {
        $color = Color::fromRgb(255, 128, 64, 0.5);

        $this->assertSame('#ff804080', $color->toHex());
    }

    public function testToRgb(): void
    {
        $color = Color::fromRgb(255, 128, 64);

        $this->assertSame('rgb(255, 128, 64)', $color->toRgb());
    }

    public function testToRgbWithAlpha(): void
    {
        $color = Color::fromRgb(255, 128, 64, 0.5);

        $this->assertSame('rgba(255, 128, 64, 0.50)', $color->toRgb());
    }

    public function testToStringNamedColor(): void
    {
        $color = Color::parse('red');

        $this->assertSame('red', $color->toString());
    }

    public function testToStringConversion(): void
    {
        $color = Color::fromRgb(255, 128, 64);
        $string = (string) $color;

        $this->assertSame('#ff8040', $string);
    }

    public function testParseHslWithHighHue(): void
    {
        $color = Color::parse('hsl(300, 100%, 50%)');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(0, $color->getGreen());
        $this->assertSame(255, $color->getBlue());
    }

    public function testParseHslWithZeroSaturation(): void
    {
        $color = Color::parse('hsl(0, 0%, 50%)');

        $this->assertSame(128, $color->getRed());
        $this->assertSame(128, $color->getGreen());
        $this->assertSame(128, $color->getBlue());
    }

    public function testFromRgbInvalidAlpha(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::fromRgb(0, 0, 0, 1.5);
    }

    public function testFromRgbNegativeAlpha(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::fromRgb(0, 0, 0, -0.1);
    }

    public function testParseHexShortWithAlpha(): void
    {
        $color = Color::parse('#F808');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(136, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
        $this->assertEqualsWithDelta(0.53, $color->getAlpha(), 0.01);
    }

    public function testParseHexInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::parse('#FF88F');
    }

    public function testParseInvalidRgbFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::parse('rgb(255, 128)');
    }

    public function testParseInvalidRgbaFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::parse('rgba(255, 128, 64)');
    }

    public function testParseHsla(): void
    {
        $color = Color::parse('hsla(0, 100%, 50%, 0.5)');

        $this->assertSame(255, $color->getRed());
        $this->assertSame(0, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
        $this->assertSame(0.5, $color->getAlpha());
    }

    public function testParseInvalidHslFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::parse('hsl(0, 100%)');
    }

    public function testParseInvalidHslaFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Color::parse('hsla(0, 100%, 50%)');
    }

    public function testParseHslAchromatic(): void
    {
        $color = Color::parse('hsl(0, 0%, 50%)');

        $this->assertSame(128, $color->getRed());
        $this->assertSame(128, $color->getGreen());
        $this->assertSame(128, $color->getBlue());
    }
}
