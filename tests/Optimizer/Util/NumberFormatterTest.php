<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Util;

use Atelier\Svg\Optimizer\Util\NumberFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumberFormatter::class)]
final class NumberFormatterTest extends TestCase
{
    public function testFormatRoundsToGivenPrecision(): void
    {
        $this->assertSame('10.12', NumberFormatter::format(10.12345, 2));
    }

    public function testFormatStripsTrailingZeros(): void
    {
        $this->assertSame('10.5', NumberFormatter::format(10.500, 2));
    }

    public function testFormatStripsDecimalPointWhenAllZeros(): void
    {
        $this->assertSame('30', NumberFormatter::format(30.000, 2));
    }

    public function testFormatRoundsUp(): void
    {
        $this->assertSame('101', NumberFormatter::format(100.999, 2));
    }

    public function testFormatInteger(): void
    {
        $this->assertSame('42', NumberFormatter::format(42.0, 2));
    }

    public function testFormatNegativeValue(): void
    {
        $this->assertSame('-3.14', NumberFormatter::format(-3.14159, 2));
    }

    public function testFormatZero(): void
    {
        $this->assertSame('0', NumberFormatter::format(0.0, 2));
    }

    public function testFormatWithHighPrecision(): void
    {
        $this->assertSame('3.14159', NumberFormatter::format(3.141592653, 5));
    }

    public function testFormatWithZeroPrecision(): void
    {
        $this->assertSame('11', NumberFormatter::format(10.5, 0));
    }

    public function testFormatRemoveLeadingZero(): void
    {
        $this->assertSame('.5', NumberFormatter::format(0.5, 2, true));
    }

    public function testFormatRemoveLeadingZeroNegative(): void
    {
        $this->assertSame('-.5', NumberFormatter::format(-0.5, 2, true));
    }

    public function testFormatRemoveLeadingZeroDoesNotAffectLargerNumbers(): void
    {
        $this->assertSame('1.5', NumberFormatter::format(1.5, 2, true));
    }

    public function testFormatRemoveLeadingZeroWithWholeZero(): void
    {
        $this->assertSame('0', NumberFormatter::format(0.0, 2, true));
    }

    public function testRoundInAttributeRoundsAllNumbers(): void
    {
        $this->assertSame(
            'translate(10.12, 20.99)',
            NumberFormatter::roundInAttribute('translate(10.12345, 20.98765)', 2),
        );
    }

    public function testRoundInAttributeStripsTrailingZeros(): void
    {
        $this->assertSame(
            'M 10.56 20.44 L 31',
            NumberFormatter::roundInAttribute('M 10.555 20.444 L 30.999', 2),
        );
    }

    public function testRoundInAttributeHandlesNegativeNumbers(): void
    {
        $this->assertSame(
            'translate(-10.5, -20.3)',
            NumberFormatter::roundInAttribute('translate(-10.500, -20.300)', 2),
        );
    }

    public function testRoundInAttributeWithRemoveLeadingZero(): void
    {
        $result = NumberFormatter::roundInAttribute('M 0.5 0.75', 2, true);
        $this->assertSame('M .5 .75', $result);
    }

    public function testRoundInAttributeWithNoNumbers(): void
    {
        $this->assertSame('none', NumberFormatter::roundInAttribute('none', 2));
    }

    public function testRoundInAttributePreservesNonNumericText(): void
    {
        $this->assertSame(
            'rotate(45) scale(2)',
            NumberFormatter::roundInAttribute('rotate(45) scale(2)', 2),
        );
    }
}
