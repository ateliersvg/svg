<?php

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Path\PathBuilder;
use Atelier\Svg\Path\ShapeFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShapeFactory::class)]
final class ShapeFactoryTest extends TestCase
{
    public function testRectangle(): void
    {
        $rect = ShapeFactory::rectangle(0, 0, 100, 50);

        $this->assertInstanceOf(PathBuilder::class, $rect);
        $pathData = $rect->getPathData();
        $this->assertStringContainsString('M', $pathData);
        $this->assertStringContainsString('H', $pathData);
        $this->assertStringContainsString('V', $pathData);
        $this->assertStringContainsString('Z', $pathData);
    }

    public function testRectangleWithRoundedCorners(): void
    {
        $rect = ShapeFactory::rectangle(0, 0, 100, 50, 5, 5);

        $pathData = $rect->getPathData();
        $this->assertStringContainsString('A', $pathData); // Arc for rounded corners
    }

    public function testCircle(): void
    {
        $circle = ShapeFactory::circle(50, 50, 25);

        $this->assertInstanceOf(PathBuilder::class, $circle);
        $pathData = $circle->getPathData();
        $this->assertStringContainsString('M', $pathData);
        $this->assertStringContainsString('A', $pathData); // Arcs for circle
        $this->assertStringContainsString('Z', $pathData);
    }

    public function testEllipse(): void
    {
        $ellipse = ShapeFactory::ellipse(50, 50, 30, 20);

        $this->assertInstanceOf(PathBuilder::class, $ellipse);
        $pathData = $ellipse->getPathData();
        $this->assertStringContainsString('A', $pathData);
    }

    public function testPolygon(): void
    {
        $polygon = ShapeFactory::polygon(50, 50, 25, 6);

        $this->assertInstanceOf(PathBuilder::class, $polygon);
        $pathData = $polygon->getPathData();
        $this->assertStringContainsString('M', $pathData);
        $this->assertStringContainsString('L', $pathData);
        $this->assertStringContainsString('Z', $pathData);
    }

    public function testPolygonInvalidSides(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ShapeFactory::polygon(50, 50, 25, 2);
    }

    public function testStar(): void
    {
        $star = ShapeFactory::star(50, 50, 30, 15, 5);

        $this->assertInstanceOf(PathBuilder::class, $star);
        $pathData = $star->getPathData();
        $this->assertStringContainsString('M', $pathData);
        $this->assertStringContainsString('L', $pathData);
        $this->assertStringContainsString('Z', $pathData);
    }

    public function testStarInvalidPoints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ShapeFactory::star(50, 50, 30, 15, 2);
    }

    public function testLine(): void
    {
        $line = ShapeFactory::line(0, 0, 100, 100);

        $this->assertInstanceOf(PathBuilder::class, $line);
        $pathData = $line->getPathData();
        $this->assertStringContainsString('M', $pathData);
        $this->assertStringContainsString('L', $pathData);
    }

    public function testPolyline(): void
    {
        $points = [[0, 0], [50, 25], [100, 50]];
        $polyline = ShapeFactory::polyline($points);

        $this->assertInstanceOf(PathBuilder::class, $polyline);
        $pathData = $polyline->getPathData();
        $this->assertStringContainsString('M', $pathData);
        $this->assertStringContainsString('L', $pathData);
        $this->assertStringNotContainsString('Z', $pathData); // Not closed
    }

    public function testPolylineEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ShapeFactory::polyline([]);
    }

    public function testPolygonFromPoints(): void
    {
        $points = [[0, 0], [100, 0], [100, 100], [0, 100]];
        $polygon = ShapeFactory::polygonFromPoints($points);

        $this->assertInstanceOf(PathBuilder::class, $polygon);
        $pathData = $polygon->getPathData();
        $this->assertStringContainsString('Z', $pathData); // Closed
    }

    public function testPolygonFromPointsTooFew(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ShapeFactory::polygonFromPoints([[0, 0], [100, 0]]);
    }
}
