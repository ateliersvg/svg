<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Value\PointList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PointList::class)]
final class PointListTest extends TestCase
{
    public function testParseSimpleCoordinatePairs(): void
    {
        $list = PointList::parse('10,20 30,40');
        $points = $list->getPoints();

        $this->assertCount(2, $points);
        $this->assertSame(10.0, $points[0]->x);
        $this->assertSame(20.0, $points[0]->y);
        $this->assertSame(30.0, $points[1]->x);
        $this->assertSame(40.0, $points[1]->y);
    }

    public function testParseSpaceSeparatedCoordinates(): void
    {
        $list = PointList::parse('10 20 30 40');
        $points = $list->getPoints();

        $this->assertCount(2, $points);
        $this->assertSame(10.0, $points[0]->x);
        $this->assertSame(20.0, $points[0]->y);
    }

    public function testParseMixedSeparators(): void
    {
        $list = PointList::parse('10,20  30 40,50,60');
        $points = $list->getPoints();

        $this->assertCount(3, $points);
        $this->assertSame(50.0, $points[2]->x);
        $this->assertSame(60.0, $points[2]->y);
    }

    public function testParseNullReturnsEmptyList(): void
    {
        $list = PointList::parse(null);

        $this->assertTrue($list->isEmpty());
        $this->assertSame([], $list->getPoints());
    }

    public function testParseEmptyStringReturnsEmptyList(): void
    {
        $list = PointList::parse('');

        $this->assertTrue($list->isEmpty());
    }

    public function testParseWhitespaceOnlyReturnsEmptyList(): void
    {
        $list = PointList::parse('   ');

        $this->assertTrue($list->isEmpty());
    }

    public function testParseCommaOnlyReturnsEmptyList(): void
    {
        $list = PointList::parse(' , ');

        $this->assertTrue($list->isEmpty());
    }

    public function testParseOddNumberOfCoordinatesThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('even number of coordinates');

        PointList::parse('10,20 30');
    }

    public function testParseNonNumericCoordinateThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Non-numeric');

        PointList::parse('10,abc');
    }

    public function testParseFloatCoordinates(): void
    {
        $list = PointList::parse('1.5,2.7 3.14,4.0');
        $points = $list->getPoints();

        $this->assertSame(1.5, $points[0]->x);
        $this->assertSame(2.7, $points[0]->y);
        $this->assertSame(3.14, $points[1]->x);
    }

    public function testParseNegativeCoordinates(): void
    {
        $list = PointList::parse('-10,-20 30,-40');
        $points = $list->getPoints();

        $this->assertSame(-10.0, $points[0]->x);
        $this->assertSame(-20.0, $points[0]->y);
        $this->assertSame(-40.0, $points[1]->y);
    }

    public function testIsEmptyReturnsFalseForNonEmptyList(): void
    {
        $list = PointList::parse('10,20');

        $this->assertFalse($list->isEmpty());
    }

    public function testToStringFormatsCorrectly(): void
    {
        $list = PointList::parse('1,2 3,4');

        $this->assertSame('1,2 3,4', $list->toString());
    }

    public function testToStringEmptyListReturnsEmptyString(): void
    {
        $list = PointList::parse('');

        $this->assertSame('', $list->toString());
    }

    public function testMagicToString(): void
    {
        $list = PointList::parse('1,2 3,4');

        $this->assertSame('1,2 3,4', (string) $list);
    }

    public function testToStringRemovesTrailingZeros(): void
    {
        $list = PointList::parse('1.50,2.00');

        $this->assertSame('1.5,2', $list->toString());
    }

    public function testGetPointsReturnsPointInstances(): void
    {
        $list = PointList::parse('5,10');
        $points = $list->getPoints();

        $this->assertCount(1, $points);
        $this->assertInstanceOf(Point::class, $points[0]);
    }

    public function testFormatCoordinateScientificNotationFallback(): void
    {
        $list = PointList::parse('0.0000001,2');

        $this->assertSame('0,2', $list->toString());
    }

    public function testFormatCoordinateNegativeZero(): void
    {
        $list = PointList::parse('-0.0000001,5');

        $this->assertSame('0,5', $list->toString());
    }
}
