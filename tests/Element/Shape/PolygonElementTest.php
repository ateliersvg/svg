<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Shape;

use Atelier\Svg\Element\Shape\PolygonElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PolygonElement::class)]
final class PolygonElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $polygon = new PolygonElement();

        $this->assertSame('polygon', $polygon->getTagName());
    }

    public function testSetAndGetPoints(): void
    {
        $polygon = new PolygonElement();
        $pointsString = '0,0 100,0 100,100 0,100';
        $result = $polygon->setPoints($pointsString);

        $this->assertSame($polygon, $result, 'setPoints should return self for chaining');
        $this->assertSame($pointsString, $polygon->getAttribute('points'));
        $this->assertSame($pointsString, $polygon->getPoints());
    }

    public function testGetPointsReturnsNullWhenNotSet(): void
    {
        $polygon = new PolygonElement();

        $this->assertNull($polygon->getPoints());
    }

    public function testSetPointsFromArray(): void
    {
        $polygon = new PolygonElement();
        $pointsArray = [[0, 0], [100, 0], [100, 100], [0, 100]];
        $result = $polygon->setPointsFromArray($pointsArray);

        $this->assertSame($polygon, $result, 'setPointsFromArray should return self for chaining');
        $this->assertSame('0,0 100,0 100,100 0,100', $polygon->getPoints());
    }

    public function testSetPointsFromArrayWithFloats(): void
    {
        $polygon = new PolygonElement();
        $pointsArray = [[10.5, 20.5], [100.25, 50.75], [80.125, 90.875]];
        $polygon->setPointsFromArray($pointsArray);

        $this->assertSame('10.5,20.5 100.25,50.75 80.125,90.875', $polygon->getPoints());
    }

    public function testGetPointsAsArray(): void
    {
        $polygon = new PolygonElement();
        $polygon->setPoints('0,0 100,0 100,100 0,100');

        $pointsArray = $polygon->getPointsAsArray();

        $this->assertIsArray($pointsArray);
        $this->assertCount(4, $pointsArray);
        $this->assertSame([0.0, 0.0], $pointsArray[0]);
        $this->assertSame([100.0, 0.0], $pointsArray[1]);
        $this->assertSame([100.0, 100.0], $pointsArray[2]);
        $this->assertSame([0.0, 100.0], $pointsArray[3]);
    }

    public function testGetPointsAsArrayWithFloats(): void
    {
        $polygon = new PolygonElement();
        $polygon->setPoints('10.5,20.5 100.25,50.75 80.125,90.875');

        $pointsArray = $polygon->getPointsAsArray();

        $this->assertIsArray($pointsArray);
        $this->assertCount(3, $pointsArray);
        $this->assertSame([10.5, 20.5], $pointsArray[0]);
        $this->assertSame([100.25, 50.75], $pointsArray[1]);
        $this->assertSame([80.125, 90.875], $pointsArray[2]);
    }

    public function testGetPointsAsArrayReturnsNullWhenNotSet(): void
    {
        $polygon = new PolygonElement();

        $this->assertNull($polygon->getPointsAsArray());
    }

    public function testGetPointsAsArrayReturnsNullWhenEmpty(): void
    {
        $polygon = new PolygonElement();
        $polygon->setPoints('');

        $this->assertNull($polygon->getPointsAsArray());
    }

    public function testGetPointsAsArrayReturnsNullWhenOnlyWhitespace(): void
    {
        $polygon = new PolygonElement();
        $polygon->setPoints('   ');

        $this->assertNull($polygon->getPointsAsArray());
    }

    public function testPointsWithCommaAndSpaceSeparators(): void
    {
        $polygon = new PolygonElement();
        $polygon->setPoints('0,0, 100,0, 100,100, 0,100');

        $pointsArray = $polygon->getPointsAsArray();

        $this->assertIsArray($pointsArray);
        $this->assertCount(4, $pointsArray);
        $this->assertSame([0.0, 0.0], $pointsArray[0]);
        $this->assertSame([100.0, 0.0], $pointsArray[1]);
        $this->assertSame([100.0, 100.0], $pointsArray[2]);
        $this->assertSame([0.0, 100.0], $pointsArray[3]);
    }

    public function testPointsWithMultipleSpaces(): void
    {
        $polygon = new PolygonElement();
        $polygon->setPoints('0,0   100,0   100,100   0,100');

        $pointsArray = $polygon->getPointsAsArray();

        $this->assertIsArray($pointsArray);
        $this->assertCount(4, $pointsArray);
        $this->assertSame([0.0, 0.0], $pointsArray[0]);
        $this->assertSame([100.0, 0.0], $pointsArray[1]);
    }

    public function testPointsRoundTrip(): void
    {
        $polygon = new PolygonElement();
        $originalArray = [[10, 20], [30, 40], [50, 60]];

        $polygon->setPointsFromArray($originalArray);
        $retrievedArray = $polygon->getPointsAsArray();

        $this->assertSame(
            [[10.0, 20.0], [30.0, 40.0], [50.0, 60.0]],
            $retrievedArray
        );
    }

    public function testTrianglePolygon(): void
    {
        $polygon = new PolygonElement();
        $polygon->setPointsFromArray([[50, 0], [100, 100], [0, 100]]);

        $this->assertSame('50,0 100,100 0,100', $polygon->getPoints());

        $pointsArray = $polygon->getPointsAsArray();
        $this->assertCount(3, $pointsArray);
        $this->assertSame([50.0, 0.0], $pointsArray[0]);
        $this->assertSame([100.0, 100.0], $pointsArray[1]);
        $this->assertSame([0.0, 100.0], $pointsArray[2]);
    }

    public function testStarPolygon(): void
    {
        $polygon = new PolygonElement();
        $starPoints = [
            [50, 0],
            [61, 35],
            [98, 35],
            [68, 57],
            [79, 91],
            [50, 70],
            [21, 91],
            [32, 57],
            [2, 35],
            [39, 35],
        ];
        $polygon->setPointsFromArray($starPoints);

        $retrievedPoints = $polygon->getPointsAsArray();
        $this->assertCount(10, $retrievedPoints);
        $this->assertSame([50.0, 0.0], $retrievedPoints[0]);
        $this->assertSame([39.0, 35.0], $retrievedPoints[9]);
    }
}
