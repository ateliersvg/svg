<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Parser;

use Atelier\Svg\Parser\ParseProfile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParseProfile::class)]
final class ParseProfileTest extends TestCase
{
    public function testStrictProfileValue(): void
    {
        $this->assertSame('strict', ParseProfile::STRICT->value);
    }

    public function testLenientProfileValue(): void
    {
        $this->assertSame('lenient', ParseProfile::LENIENT->value);
    }

    public function testStrictShouldThrowOnError(): void
    {
        $this->assertTrue(ParseProfile::STRICT->shouldThrowOnError());
    }

    public function testLenientShouldNotThrowOnError(): void
    {
        $this->assertFalse(ParseProfile::LENIENT->shouldThrowOnError());
    }

    public function testStrictShouldNotPreserveUnknown(): void
    {
        $this->assertFalse(ParseProfile::STRICT->shouldPreserveUnknown());
    }

    public function testLenientShouldPreserveUnknown(): void
    {
        $this->assertTrue(ParseProfile::LENIENT->shouldPreserveUnknown());
    }

    public function testStrictShouldWarnOnDeprecated(): void
    {
        $this->assertTrue(ParseProfile::STRICT->shouldWarnOnDeprecated());
    }

    public function testLenientShouldWarnOnDeprecated(): void
    {
        $this->assertTrue(ParseProfile::LENIENT->shouldWarnOnDeprecated());
    }

    public function testCanBeCreatedFromString(): void
    {
        $strict = ParseProfile::from('strict');
        $lenient = ParseProfile::from('lenient');

        $this->assertSame(ParseProfile::STRICT, $strict);
        $this->assertSame(ParseProfile::LENIENT, $lenient);
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(ParseProfile::tryFrom('invalid'));
    }

    public function testAllCasesAreCovered(): void
    {
        $cases = ParseProfile::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(ParseProfile::STRICT, $cases);
        $this->assertContains(ParseProfile::LENIENT, $cases);
    }
}
