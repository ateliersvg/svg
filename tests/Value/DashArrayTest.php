<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Value\DashArray;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DashArray::class)]
final class DashArrayTest extends TestCase
{
    public function testParseNone(): void
    {
        $dashArray = DashArray::parse('none');
        $this->assertTrue($dashArray->isNone());
        $this->assertSame([], $dashArray->getValues());
        $this->assertSame('none', $dashArray->getRawValue());
        $this->assertSame('none', (string) $dashArray);

        $dashArray = DashArray::parse('');
        $this->assertTrue($dashArray->isNone());
        $this->assertSame([], $dashArray->getValues());
        $this->assertSame('', $dashArray->getRawValue());
        $this->assertSame('', (string) $dashArray);

        $dashArray = DashArray::parse('   ');
        $this->assertTrue($dashArray->isNone());
        $this->assertSame([], $dashArray->getValues());
        $this->assertSame('', $dashArray->getRawValue());
        $this->assertSame('', (string) $dashArray);
    }

    public function testParseValid(): void
    {
        $dashArray = DashArray::parse('5 10');
        $this->assertFalse($dashArray->isNone());
        $this->assertSame([5.0, 10.0], $dashArray->getValues());
        $this->assertSame('5 10', $dashArray->getRawValue());
        $this->assertSame('5 10', (string) $dashArray);

        $dashArray = DashArray::parse('5,10');
        $this->assertFalse($dashArray->isNone());
        $this->assertSame([5.0, 10.0], $dashArray->getValues());
        $this->assertSame('5,10', $dashArray->getRawValue());
        $this->assertSame('5,10', (string) $dashArray);

        $dashArray = DashArray::parse('5, 10');
        $this->assertFalse($dashArray->isNone());
        $this->assertSame([5.0, 10.0], $dashArray->getValues());
        $this->assertSame('5, 10', $dashArray->getRawValue());
        $this->assertSame('5, 10', (string) $dashArray);

        $dashArray = DashArray::parse('5,3,2');
        $this->assertFalse($dashArray->isNone());
        $this->assertSame([5.0, 3.0, 2.0, 5.0, 3.0, 2.0], $dashArray->getValues());
        $this->assertSame('5,3,2', $dashArray->getRawValue());
        $this->assertSame('5,3,2', (string) $dashArray);

        $dashArray = DashArray::parse('1 2 3 4');
        $this->assertFalse($dashArray->isNone());
        $this->assertSame([1.0, 2.0, 3.0, 4.0], $dashArray->getValues());
        $this->assertSame('1 2 3 4', $dashArray->getRawValue());
        $this->assertSame('1 2 3 4', (string) $dashArray);

        $dashArray = DashArray::parse('  1   2  3   4  ');
        $this->assertFalse($dashArray->isNone());
        $this->assertSame([1.0, 2.0, 3.0, 4.0], $dashArray->getValues());
        // Raw value preserves original spacing after trim
        $this->assertMatchesRegularExpression('/1\s+2\s+3\s+4/', $dashArray->getRawValue());
        // toString() normalizes spacing
        $this->assertMatchesRegularExpression('/1\s+2\s+3\s+4/', (string) $dashArray);
    }

    public function testParseInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DashArray::parse('a');
    }

    public function testParseNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DashArray::parse('-1');
    }

    public function testFromArrayValid(): void
    {
        $dashArray = DashArray::fromArray([5, 10]);
        $this->assertFalse($dashArray->isNone());
        $this->assertSame([5.0, 10.0], $dashArray->getValues());
        $this->assertSame('5,10', $dashArray->getRawValue());
        $this->assertSame('5,10', (string) $dashArray);

        $dashArray = DashArray::fromArray([5, 3, 2]);
        $this->assertFalse($dashArray->isNone());
        $this->assertSame([5.0, 3.0, 2.0, 5.0, 3.0, 2.0], $dashArray->getValues());
        $this->assertSame('5,3,2', $dashArray->getRawValue());
        $this->assertSame('5,3,2', (string) $dashArray);

        $dashArray = DashArray::fromArray([1.5, 2.5, 3.5, 4.5]);
        $this->assertFalse($dashArray->isNone());
        $this->assertSame([1.5, 2.5, 3.5, 4.5], $dashArray->getValues());
        $this->assertSame('1.5,2.5,3.5,4.5', $dashArray->getRawValue());
        $this->assertSame('1.5,2.5,3.5,4.5', (string) $dashArray);
    }

    public function testFromArrayInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DashArray::fromArray(['a']);
    }

    public function testFromArrayNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DashArray::fromArray([-1]);
    }

    public function testFromArrayInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DashArray::fromArray([new \stdClass()]);
    }

    public function testNone(): void
    {
        $dashArray = DashArray::none();
        $this->assertTrue($dashArray->isNone());
        $this->assertSame([], $dashArray->getValues());
        $this->assertSame('none', $dashArray->getRawValue());
        $this->assertSame('none', (string) $dashArray);
    }

    public function testGetValues(): void
    {
        $dashArray = DashArray::parse('1 2 3 4');
        $this->assertSame([1.0, 2.0, 3.0, 4.0], $dashArray->getValues());
    }

    public function testIsNone(): void
    {
        $dashArray = DashArray::parse('none');
        $this->assertTrue($dashArray->isNone());

        $dashArray = DashArray::parse('1 2');
        $this->assertFalse($dashArray->isNone());
    }

    public function testGetRawValue(): void
    {
        $dashArray = DashArray::parse('1 2 3');
        $this->assertSame('1 2 3', $dashArray->getRawValue());
    }

    public function testToString(): void
    {
        $dashArray = DashArray::parse('1 2 3');
        $this->assertSame('1 2 3', $dashArray->toString());
    }

    public function testToStringMagicMethod(): void
    {
        $dashArray = DashArray::parse('1 2 3');
        $this->assertSame('1 2 3', (string) $dashArray);
    }
}
