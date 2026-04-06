<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Length::class)]
final class LengthTest extends TestCase
{
    public function testParsePixelValue(): void
    {
        $length = Length::parse('10px');

        $this->assertSame(10.0, $length->getValue());
        $this->assertSame('px', $length->getUnit());
    }

    public function testParsePercentageValue(): void
    {
        $length = Length::parse('50%');

        $this->assertSame(50.0, $length->getValue());
        $this->assertSame('%', $length->getUnit());
        $this->assertTrue($length->isPercentage());
    }

    public function testParseEmValue(): void
    {
        $length = Length::parse('2.5em');

        $this->assertSame(2.5, $length->getValue());
        $this->assertSame('em', $length->getUnit());
    }

    public function testParseUnitlessValue(): void
    {
        $length = Length::parse('100');

        $this->assertSame(100.0, $length->getValue());
        $this->assertNull($length->getUnit());
        $this->assertTrue($length->isUnitless());
    }

    public function testParseIntegerInput(): void
    {
        $length = Length::parse(42);

        $this->assertSame(42.0, $length->getValue());
        $this->assertNull($length->getUnit());
        $this->assertTrue($length->isUnitless());
    }

    public function testParseFloatInput(): void
    {
        $length = Length::parse(3.14);

        $this->assertSame(3.14, $length->getValue());
        $this->assertNull($length->getUnit());
    }

    public function testParseNegativeValue(): void
    {
        $length = Length::parse('-5px');

        $this->assertSame(-5.0, $length->getValue());
        $this->assertSame('px', $length->getUnit());
    }

    public function testParsePositiveSign(): void
    {
        $length = Length::parse('+10mm');

        $this->assertSame(10.0, $length->getValue());
        $this->assertSame('mm', $length->getUnit());
    }

    public function testParseDecimalValue(): void
    {
        $length = Length::parse('.5em');

        $this->assertSame(0.5, $length->getValue());
        $this->assertSame('em', $length->getUnit());
    }

    public function testParseAllValidUnits(): void
    {
        $units = ['em', 'ex', 'px', 'pt', 'pc', 'cm', 'mm', 'in', '%'];

        foreach ($units as $unit) {
            $length = Length::parse('10'.$unit);
            $this->assertSame($unit, $length->getUnit(), "Failed for unit: $unit");
        }
    }

    public function testParseUppercaseUnitNormalizesToLowercase(): void
    {
        $length = Length::parse('10PX');

        $this->assertSame('px', $length->getUnit());
    }

    public function testParseScientificNotation(): void
    {
        $length = Length::parse('1e2px');

        $this->assertSame(100.0, $length->getValue());
        $this->assertSame('px', $length->getUnit());
    }

    public function testParseEmptyStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('empty string');

        Length::parse('');
    }

    public function testParseInvalidFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid length format');

        Length::parse('abc');
    }

    public function testIsUnitlessReturnsFalseWithUnit(): void
    {
        $length = Length::parse('10px');

        $this->assertFalse($length->isUnitless());
    }

    public function testIsPercentageReturnsFalseForNonPercentage(): void
    {
        $length = Length::parse('10px');

        $this->assertFalse($length->isPercentage());
    }

    public function testToStringPixelValue(): void
    {
        $length = Length::parse('10px');

        $this->assertSame('10px', $length->toString());
    }

    public function testToStringUnitlessValue(): void
    {
        $length = Length::parse('100');

        $this->assertSame('100', $length->toString());
    }

    public function testToStringPercentage(): void
    {
        $length = Length::parse('50%');

        $this->assertSame('50%', $length->toString());
    }

    public function testToStringRemovesTrailingZeros(): void
    {
        $length = Length::parse('1.200px');

        $this->assertSame('1.2px', $length->toString());
    }

    public function testToStringNegativeZero(): void
    {
        $length = Length::parse('-0px');

        $this->assertSame('0px', $length->toString());
    }

    public function testToStringWholeNumber(): void
    {
        $length = Length::parse('5.000em');

        $this->assertSame('5em', $length->toString());
    }

    public function testMagicToString(): void
    {
        $length = Length::parse('10px');

        $this->assertSame('10px', (string) $length);
    }

    public function testToStringVerySmallNegativeValue(): void
    {
        $length = Length::parse('-0.0000001px');

        $this->assertSame('0px', $length->toString());
    }

    public function testParseValueWithWhitespaceBetweenNumberAndUnit(): void
    {
        $length = Length::parse('10 px');

        $this->assertSame(10.0, $length->getValue());
        $this->assertSame('px', $length->getUnit());
    }

    public function testParseScientificNotationWithNegativeExponent(): void
    {
        $length = Length::parse('1e-5px');

        $this->assertSame(1e-5, $length->getValue());
        $this->assertSame('px', $length->getUnit());
    }

    public function testParseInitializesStaticPattern(): void
    {
        $length = Length::parse('10px');
        $this->assertSame(10.0, $length->getValue());
        $this->assertSame('px', $length->getUnit());
    }
}
