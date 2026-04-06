<?php

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Value\Angle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Angle::class)]
final class AngleTest extends TestCase
{
    public function testParseWithDegrees(): void
    {
        $angle = Angle::parse('45deg');
        $this->assertSame(45.0, $angle->toDegrees());
        $this->assertSame(45.0, $angle->getOriginalValue());
        $this->assertSame('deg', $angle->getOriginalUnit());
    }

    public function testParseWithRadians(): void
    {
        $angle = Angle::parse('1.57rad');
        $this->assertEqualsWithDelta(1.57, $angle->toRadians(), 0.000001);
        $this->assertSame(1.57, $angle->getOriginalValue());
        $this->assertSame('rad', $angle->getOriginalUnit());
    }

    public function testParseWithGradians(): void
    {
        $angle = Angle::parse('100grad');
        $this->assertSame(90.0, $angle->toDegrees());
        $this->assertSame(100.0, $angle->getOriginalValue());
        $this->assertSame('grad', $angle->getOriginalUnit());
    }

    public function testParseWithUnitless(): void
    {
        $angle = Angle::parse('90');
        $this->assertSame(90.0, $angle->toDegrees());
        $this->assertSame(90.0, $angle->getOriginalValue());
        $this->assertNull($angle->getOriginalUnit());
    }

    public function testParseWithFloat(): void
    {
        $angle = Angle::parse(90.5);
        $this->assertSame(90.5, $angle->toDegrees());
        $this->assertSame(90.5, $angle->getOriginalValue());
        $this->assertNull($angle->getOriginalUnit());
    }

    public function testParseWithInt(): void
    {
        $angle = Angle::parse(90);
        $this->assertSame(90.0, $angle->toDegrees());
        $this->assertSame(90.0, $angle->getOriginalValue());
        $this->assertNull($angle->getOriginalUnit());
    }

    public function testParseWithNegativeDegrees(): void
    {
        $angle = Angle::parse('-45deg');
        $this->assertSame(-45.0, $angle->toDegrees());
        $this->assertSame(-45.0, $angle->getOriginalValue());
        $this->assertSame('deg', $angle->getOriginalUnit());
    }

    public function testParseWithScientificNotation(): void
    {
        $angle = Angle::parse('1.2e2deg');
        $this->assertSame(120.0, $angle->toDegrees());
        $this->assertSame(120.0, $angle->getOriginalValue());
        $this->assertSame('deg', $angle->getOriginalUnit());
    }

    #[DataProvider('invalidUnitProvider')]
    public function testParseWithInvalidUnit(string $invalidUnit): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/Invalid angle unit: '$invalidUnit'/");
        Angle::parse('45'.$invalidUnit);
    }

    public static function invalidUnitProvider(): array
    {
        return [
            ['foo'],
            ['bar'],
            ['invalid'],
        ];
    }

    public function testParseWithInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid angle format: 'invalid'");
        Angle::parse('invalid');
    }

    public function testParseWithEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot parse empty string as Angle.');
        Angle::parse('');
    }

    public function testFromDegrees(): void
    {
        $angle = Angle::fromDegrees(180.0);
        $this->assertSame(180.0, $angle->toDegrees());
        $this->assertSame(180.0, $angle->getOriginalValue());
        $this->assertSame('deg', $angle->getOriginalUnit());
    }

    public function testFromRadians(): void
    {
        $angle = Angle::fromRadians(M_PI);
        $this->assertEqualsWithDelta(M_PI, $angle->toRadians(), 0.000001);
        $this->assertSame(M_PI, $angle->getOriginalValue());
        $this->assertSame('rad', $angle->getOriginalUnit());
    }

    public function testFromGradians(): void
    {
        $angle = Angle::fromGradians(200.0);
        $this->assertSame(180.0, $angle->toDegrees());
        $this->assertSame(200.0, $angle->getOriginalValue());
        $this->assertSame('grad', $angle->getOriginalUnit());
    }

    public function testToRadians(): void
    {
        $angle = Angle::fromDegrees(90.0);
        $this->assertEqualsWithDelta(M_PI_2, $angle->toRadians(), 0.000001);
    }

    public function testToDegrees(): void
    {
        $angle = Angle::fromRadians(M_PI);
        $this->assertSame(180.0, $angle->toDegrees());
    }

    public function testToGradians(): void
    {
        $angle = Angle::fromDegrees(180.0);
        $this->assertSame(200.0, $angle->toGradians());
    }

    public function testGetOriginalValue(): void
    {
        $angle = Angle::parse('123.45rad');
        $this->assertSame(123.45, $angle->getOriginalValue());
    }

    public function testGetOriginalUnit(): void
    {
        $angle = Angle::parse('123.45grad');
        $this->assertSame('grad', $angle->getOriginalUnit());
    }

    public function testToStringWithDegrees(): void
    {
        $angle = Angle::parse('45deg');
        $this->assertSame('45', $angle->toString());
    }

    public function testToStringWithRadians(): void
    {
        $angle = Angle::parse('1.57rad');
        $this->assertSame('1.57rad', $angle->toString());
    }

    public function testToStringWithGradians(): void
    {
        $angle = Angle::parse('100grad');
        $this->assertSame('100grad', $angle->toString());
    }

    public function testToStringWithUnitless(): void
    {
        $angle = Angle::parse('90');
        $this->assertSame('90', $angle->toString());
    }

    public function testToStringWithNegativeZero(): void
    {
        $angle = Angle::parse('-0');
        $this->assertSame('0', $angle->toString());
    }

    public function testToStringWithScientificNotation(): void
    {
        $angle = Angle::parse('1.2e-5deg');
        $this->assertSame('0.000012', $angle->toString());
    }

    public function testToStringWithTrailingZeros(): void
    {
        $angle = Angle::parse('1.200000deg');
        $this->assertSame('1.2', $angle->toString());
    }

    public function testToStringWithTrailingZerosAndDecimal(): void
    {
        $angle = Angle::parse('1.000000deg');
        $this->assertSame('1', $angle->toString());
    }

    public function testMagicToString(): void
    {
        $angle = Angle::parse('45deg');
        $this->assertSame('45', (string) $angle);
    }
}
