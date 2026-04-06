<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Value\PreserveAspectRatio;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PreserveAspectRatio::class)]
final class PreserveAspectRatioTest extends TestCase
{
    public function testParseXMidYMidMeet(): void
    {
        $par = PreserveAspectRatio::parse('xMidYMid meet');
        $this->assertSame('xMidYMid', $par->getAlign());
        $this->assertSame('xMid', $par->getXAlign());
        $this->assertSame('YMid', $par->getYAlign());
        $this->assertSame('meet', $par->getMeetOrSlice());
        $this->assertTrue($par->isMeet());
        $this->assertFalse($par->isSlice());
        $this->assertFalse($par->isNone());
    }

    public function testParseXMinYMinSlice(): void
    {
        $par = PreserveAspectRatio::parse('xMinYMin slice');
        $this->assertSame('xMinYMin', $par->getAlign());
        $this->assertSame('xMin', $par->getXAlign());
        $this->assertSame('YMin', $par->getYAlign());
        $this->assertSame('slice', $par->getMeetOrSlice());
        $this->assertFalse($par->isMeet());
        $this->assertTrue($par->isSlice());
    }

    public function testParseXMaxYMax(): void
    {
        $par = PreserveAspectRatio::parse('xMaxYMax meet');
        $this->assertSame('xMaxYMax', $par->getAlign());
        $this->assertSame('xMax', $par->getXAlign());
        $this->assertSame('YMax', $par->getYAlign());
    }

    public function testParseAlignOnlyDefaultsToMeet(): void
    {
        $par = PreserveAspectRatio::parse('xMidYMid');
        $this->assertSame('meet', $par->getMeetOrSlice());
        $this->assertTrue($par->isMeet());
    }

    public function testParseNone(): void
    {
        $par = PreserveAspectRatio::parse('none');
        $this->assertTrue($par->isNone());
        $this->assertSame('none', $par->getAlign());
        $this->assertNull($par->getXAlign());
        $this->assertNull($par->getYAlign());
        $this->assertSame('meet', $par->getMeetOrSlice());
    }

    public function testParseNoneSlice(): void
    {
        $par = PreserveAspectRatio::parse('none slice');
        $this->assertTrue($par->isNone());
        $this->assertTrue($par->isSlice());
    }

    public function testParseNullUsesDefault(): void
    {
        $par = PreserveAspectRatio::parse(null);
        $this->assertSame('xMidYMid', $par->getAlign());
        $this->assertSame('meet', $par->getMeetOrSlice());
    }

    public function testParseEmptyStringUsesDefault(): void
    {
        $par = PreserveAspectRatio::parse('');
        $this->assertSame('xMidYMid', $par->getAlign());
        $this->assertSame('meet', $par->getMeetOrSlice());
    }

    public function testParseThrowsOnInvalidMeetOrSlice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PreserveAspectRatio::parse('xMidYMid invalid');
    }

    public function testParseThrowsOnInvalidAlignFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PreserveAspectRatio::parse('invalidValue');
    }

    public function testParseThrowsOnCaseInsensitiveAlignment(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid alignment value');
        PreserveAspectRatio::parse('xmidymid');
    }

    public function testNoneFactory(): void
    {
        $par = PreserveAspectRatio::none();
        $this->assertTrue($par->isNone());
        $this->assertSame('meet', $par->getMeetOrSlice());
    }

    public function testNoneFactoryWithSlice(): void
    {
        $par = PreserveAspectRatio::none('slice');
        $this->assertTrue($par->isNone());
        $this->assertTrue($par->isSlice());
    }

    public function testFromAlignment(): void
    {
        $par = PreserveAspectRatio::fromAlignment('xMin', 'YMax', 'slice');
        $this->assertSame('xMinYMax', $par->getAlign());
        $this->assertSame('xMin', $par->getXAlign());
        $this->assertSame('YMax', $par->getYAlign());
        $this->assertSame('slice', $par->getMeetOrSlice());
    }

    public function testFromAlignmentDefaultsMeet(): void
    {
        $par = PreserveAspectRatio::fromAlignment('xMid', 'YMid');
        $this->assertSame('meet', $par->getMeetOrSlice());
    }

    public function testFromAlignmentThrowsOnInvalidX(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PreserveAspectRatio::fromAlignment('invalid', 'YMid');
    }

    public function testFromAlignmentThrowsOnInvalidY(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PreserveAspectRatio::fromAlignment('xMid', 'invalid');
    }

    public function testFromAlignmentThrowsOnInvalidMeetOrSlice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PreserveAspectRatio::fromAlignment('xMid', 'YMid', 'invalid');
    }

    public function testDefaultFactory(): void
    {
        $par = PreserveAspectRatio::default();
        $this->assertSame('xMidYMid', $par->getAlign());
        $this->assertSame('meet', $par->getMeetOrSlice());
    }

    public function testToStringOmitsMeet(): void
    {
        $par = PreserveAspectRatio::parse('xMidYMid meet');
        $this->assertSame('xMidYMid', $par->toString());
    }

    public function testToStringIncludesSlice(): void
    {
        $par = PreserveAspectRatio::parse('xMidYMid slice');
        $this->assertSame('xMidYMid slice', $par->toString());
    }

    public function testToStringNone(): void
    {
        $par = PreserveAspectRatio::none();
        $this->assertSame('none', $par->toString());
    }

    public function testToStringNoneSlice(): void
    {
        $par = PreserveAspectRatio::none('slice');
        $this->assertSame('none slice', $par->toString());
    }

    public function testMagicToString(): void
    {
        $par = PreserveAspectRatio::parse('xMinYMin slice');
        $this->assertSame($par->toString(), (string) $par);
    }
}
