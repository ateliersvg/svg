<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Shape;

use Atelier\Svg\Element\Shape\PolylineElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PolylineElement::class)]
final class PolylineElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $polyline = new PolylineElement();

        $this->assertSame('polyline', $polyline->getTagName());
    }

    public function testSetAndGetPoints(): void
    {
        $polyline = new PolylineElement();
        $pointsString = '0,0 100,0 100,100';
        $result = $polyline->setPoints($pointsString);

        $this->assertSame($polyline, $result, 'setPoints should return self for chaining');
        $this->assertSame($pointsString, $polyline->getAttribute('points'));
        $this->assertSame($pointsString, $polyline->getPoints());
    }

    public function testGetPointsReturnsNullWhenNotSet(): void
    {
        $polyline = new PolylineElement();

        $this->assertNull($polyline->getPoints());
    }

    public function testSetPointsFromArray(): void
    {
        $polyline = new PolylineElement();
        $pointsArray = [[0, 0], [100, 0], [100, 100]];
        $result = $polyline->setPointsFromArray($pointsArray);

        $this->assertSame($polyline, $result, 'setPointsFromArray should return self for chaining');
        $this->assertSame('0,0 100,0 100,100', $polyline->getPoints());
    }

    public function testSetPointsFromArrayWithFloats(): void
    {
        $polyline = new PolylineElement();
        $pointsArray = [[10.5, 20.5], [100.25, 50.75], [80.125, 90.875]];
        $polyline->setPointsFromArray($pointsArray);

        $this->assertSame('10.5,20.5 100.25,50.75 80.125,90.875', $polyline->getPoints());
    }

    public function testGetPointsAsArray(): void
    {
        $polyline = new PolylineElement();
        $polyline->setPoints('0,0 100,0 100,100');

        $pointsArray = $polyline->getPointsAsArray();

        $this->assertIsArray($pointsArray);
        $this->assertCount(3, $pointsArray);
        $this->assertSame([0.0, 0.0], $pointsArray[0]);
        $this->assertSame([100.0, 0.0], $pointsArray[1]);
        $this->assertSame([100.0, 100.0], $pointsArray[2]);
    }

    public function testGetPointsAsArrayWithFloats(): void
    {
        $polyline = new PolylineElement();
        $polyline->setPoints('10.5,20.5 100.25,50.75 80.125,90.875');

        $pointsArray = $polyline->getPointsAsArray();

        $this->assertIsArray($pointsArray);
        $this->assertCount(3, $pointsArray);
        $this->assertSame([10.5, 20.5], $pointsArray[0]);
        $this->assertSame([100.25, 50.75], $pointsArray[1]);
        $this->assertSame([80.125, 90.875], $pointsArray[2]);
    }

    public function testGetPointsAsArrayReturnsNullWhenNotSet(): void
    {
        $polyline = new PolylineElement();

        $this->assertNull($polyline->getPointsAsArray());
    }

    public function testGetPointsAsArrayReturnsNullWhenEmpty(): void
    {
        $polyline = new PolylineElement();
        $polyline->setPoints('');

        $this->assertNull($polyline->getPointsAsArray());
    }

    public function testGetPointsAsArrayReturnsNullWhenOnlyWhitespace(): void
    {
        $polyline = new PolylineElement();
        $polyline->setPoints('   ');

        $this->assertNull($polyline->getPointsAsArray());
    }

    public function testPointsWithCommaAndSpaceSeparators(): void
    {
        $polyline = new PolylineElement();
        $polyline->setPoints('0,0, 100,0, 100,100');

        $pointsArray = $polyline->getPointsAsArray();

        $this->assertIsArray($pointsArray);
        $this->assertCount(3, $pointsArray);
        $this->assertSame([0.0, 0.0], $pointsArray[0]);
        $this->assertSame([100.0, 0.0], $pointsArray[1]);
        $this->assertSame([100.0, 100.0], $pointsArray[2]);
    }

    public function testPointsWithMultipleSpaces(): void
    {
        $polyline = new PolylineElement();
        $polyline->setPoints('0,0   100,0   100,100');

        $pointsArray = $polyline->getPointsAsArray();

        $this->assertIsArray($pointsArray);
        $this->assertCount(3, $pointsArray);
        $this->assertSame([0.0, 0.0], $pointsArray[0]);
        $this->assertSame([100.0, 0.0], $pointsArray[1]);
    }

    public function testPointsRoundTrip(): void
    {
        $polyline = new PolylineElement();
        $originalArray = [[10, 20], [30, 40], [50, 60]];

        $polyline->setPointsFromArray($originalArray);
        $retrievedArray = $polyline->getPointsAsArray();

        $this->assertSame(
            [[10.0, 20.0], [30.0, 40.0], [50.0, 60.0]],
            $retrievedArray
        );
    }

    public function testSingleLinePolyline(): void
    {
        $polyline = new PolylineElement();
        $polyline->setPointsFromArray([[0, 0], [100, 100]]);

        $this->assertSame('0,0 100,100', $polyline->getPoints());

        $pointsArray = $polyline->getPointsAsArray();
        $this->assertCount(2, $pointsArray);
        $this->assertSame([0.0, 0.0], $pointsArray[0]);
        $this->assertSame([100.0, 100.0], $pointsArray[1]);
    }

    public function testZigZagPolyline(): void
    {
        $polyline = new PolylineElement();
        $zigzagPoints = [
            [0, 50],
            [25, 25],
            [50, 50],
            [75, 25],
            [100, 50],
        ];
        $polyline->setPointsFromArray($zigzagPoints);

        $retrievedPoints = $polyline->getPointsAsArray();
        $this->assertCount(5, $retrievedPoints);
        $this->assertSame([0.0, 50.0], $retrievedPoints[0]);
        $this->assertSame([100.0, 50.0], $retrievedPoints[4]);
    }

    public function testComplexPathPolyline(): void
    {
        $polyline = new PolylineElement();
        $complexPath = [
            [10, 10],
            [50, 25],
            [80, 10],
            [90, 40],
            [80, 70],
            [50, 85],
            [20, 70],
        ];
        $polyline->setPointsFromArray($complexPath);

        $retrievedPoints = $polyline->getPointsAsArray();
        $this->assertCount(7, $retrievedPoints);
        $this->assertSame([10.0, 10.0], $retrievedPoints[0]);
        $this->assertSame([20.0, 70.0], $retrievedPoints[6]);
    }
}
