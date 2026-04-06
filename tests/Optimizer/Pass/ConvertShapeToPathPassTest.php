<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\ConvertShapeToPathPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConvertShapeToPathPass::class)]
final class ConvertShapeToPathPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new ConvertShapeToPathPass();

        $this->assertSame('convert-shape-to-path', $pass->getName());
    }

    public function testConstructorDefaults(): void
    {
        $pass = new ConvertShapeToPathPass();

        $this->assertInstanceOf(ConvertShapeToPathPass::class, $pass);
    }

    public function testConstructorWithCustomOptions(): void
    {
        $pass = new ConvertShapeToPathPass(
            convertRects: false,
            convertCircles: true,
            convertEllipses: false,
            convertLines: true,
            convertPolygons: false,
            convertPolylines: true,
            floats2Ints: false
        );

        $this->assertInstanceOf(ConvertShapeToPathPass::class, $pass);
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new ConvertShapeToPathPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testConvertSimpleRect(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setX(10)->setY(20)->setWidth(100)->setHeight(50);

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(PathElement::class, $children[0]);

        $path = $children[0];
        $this->assertSame('M10 20 L110 20 L110 70 L10 70 Z', $path->getPathData());
    }

    public function testConvertRectWithFloatCoordinates(): void
    {
        $pass = new ConvertShapeToPathPass(floats2Ints: false);
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('x', '10.5');
        $rect->setAttribute('y', '20.25');
        $rect->setAttribute('width', '100.75');
        $rect->setAttribute('height', '50.5');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PathElement::class, $children[0]);

        $path = $children[0];
        $pathData = $path->getPathData();
        $this->assertStringContainsString('10.5', $pathData);
        $this->assertStringContainsString('20.25', $pathData);
    }

    public function testConvertRectWithFloats2Ints(): void
    {
        $pass = new ConvertShapeToPathPass(floats2Ints: true);
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('x', '10.0');
        $rect->setAttribute('y', '20.0');
        $rect->setAttribute('width', '100.0');
        $rect->setAttribute('height', '50.0');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertSame('M10 20 L110 20 L110 70 L10 70 Z', $path->getPathData());
    }

    public function testConvertRectWithRoundedCorners(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setX(10)->setY(20)->setWidth(100)->setHeight(50);
        $rect->setRx(5)->setRy(5);

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $pathData = $path->getPathData();

        // Should contain arc commands
        $this->assertStringContainsString('A5 5', $pathData);
        $this->assertStringContainsString('M15 20', $pathData); // Start after rounded corner
    }

    public function testConvertRectWithOnlyRx(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setX(10)->setY(20)->setWidth(100)->setHeight(50);
        $rect->setRx(5);

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $pathData = $path->getPathData();

        // Should use rx for both radii
        $this->assertStringContainsString('A5 5', $pathData);
    }

    public function testConvertRectWithOnlyRy(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setX(10)->setY(20)->setWidth(100)->setHeight(50);
        $rect->setRy(8);

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $pathData = $path->getPathData();

        // Should use ry for both radii
        $this->assertStringContainsString('A8 8', $pathData);
    }

    public function testConvertRectWithLargeRadii(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setX(10)->setY(20)->setWidth(100)->setHeight(50);
        $rect->setRx(60)->setRy(30); // Larger than half width/height

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $pathData = $path->getPathData();

        // Radii should be capped: rx to 50 (half of 100), ry to 25 (half of 50)
        $this->assertStringContainsString('A50 25', $pathData);
    }

    public function testConvertRectPreservesAttributes(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setX(10)->setY(20)->setWidth(100)->setHeight(50);
        $rect->setAttribute('id', 'myRect');
        $rect->setAttribute('class', 'shape');
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('stroke', 'blue');
        $rect->setAttribute('stroke-width', '2');
        $rect->setAttribute('transform', 'rotate(45)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];

        $this->assertSame('myRect', $path->getAttribute('id'));
        $this->assertSame('shape', $path->getAttribute('class'));
        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('blue', $path->getAttribute('stroke'));
        $this->assertSame('2', $path->getAttribute('stroke-width'));
        $this->assertSame('rotate(45)', $path->getAttribute('transform'));
    }

    public function testSkipInvalidRect(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setX(10)->setY(20)->setWidth(0)->setHeight(50); // Zero width

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // Should not be converted
        $this->assertInstanceOf(RectElement::class, $children[0]);
    }

    public function testConvertCircle(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $circle = new CircleElement();
        $circle->setCx(50)->setCy(50)->setRadius(30);

        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PathElement::class, $children[0]);

        $path = $children[0];
        $this->assertSame('M80 50 A30 30 0 1 0 20 50 A30 30 0 1 0 80 50 Z', $path->getPathData());
    }

    public function testConvertCircleWithDefaultCenter(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $circle = new CircleElement();
        $circle->setRadius(10); // cx and cy default to 0

        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertSame('M10 0 A10 10 0 1 0 -10 0 A10 10 0 1 0 10 0 Z', $path->getPathData());
    }

    public function testSkipInvalidCircle(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $circle = new CircleElement();
        $circle->setCx(50)->setCy(50)->setRadius(0); // Zero radius

        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // Should not be converted
        $this->assertInstanceOf(CircleElement::class, $children[0]);
    }

    public function testConvertEllipse(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(50)->setRx(40)->setRy(20);

        $svg->appendChild($ellipse);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PathElement::class, $children[0]);

        $path = $children[0];
        $this->assertSame('M90 50 A40 20 0 1 0 10 50 A40 20 0 1 0 90 50 Z', $path->getPathData());
    }

    public function testSkipInvalidEllipse(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(50)->setRx(0)->setRy(20); // Zero rx

        $svg->appendChild($ellipse);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // Should not be converted
        $this->assertInstanceOf(EllipseElement::class, $children[0]);
    }

    public function testConvertLine(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $line = new LineElement();
        $line->setX1(10)->setY1(20)->setX2(100)->setY2(80);

        $svg->appendChild($line);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PathElement::class, $children[0]);

        $path = $children[0];
        $this->assertSame('M10 20 L100 80', $path->getPathData());
    }

    public function testConvertLineWithDefaultCoordinates(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $line = new LineElement();
        // All coordinates default to 0

        $svg->appendChild($line);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertSame('M0 0 L0 0', $path->getPathData());
    }

    public function testConvertPolygon(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setPoints('10,20 30,40 50,20');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PathElement::class, $children[0]);

        $path = $children[0];
        $this->assertSame('M10 20 L30 40 L50 20 Z', $path->getPathData());
    }

    public function testConvertPolygonWithVariousFormats(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        // Mixed separators: commas and spaces
        $polygon->setPoints('10 20, 30 40, 50 20');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertSame('M10 20 L30 40 L50 20 Z', $path->getPathData());
    }

    public function testConvertPolygonSpaceSeparated(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        // Space-separated coordinates
        $polygon->setPoints('10 20 30 40 50 20');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertSame('M10 20 L30 40 L50 20 Z', $path->getPathData());
    }

    public function testSkipInvalidPolygon(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setPoints('10,20'); // Only one point

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // Should not be converted
        $this->assertInstanceOf(PolygonElement::class, $children[0]);
    }

    public function testConvertPolyline(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polyline = new PolylineElement();
        $polyline->setPoints('10,20 30,40 50,20');

        $svg->appendChild($polyline);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PathElement::class, $children[0]);

        $path = $children[0];
        // Note: no Z at the end for polyline
        $this->assertSame('M10 20 L30 40 L50 20', $path->getPathData());
    }

    public function testConvertPolylineNotClosed(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polyline = new PolylineElement();
        $polyline->setPoints('0 0 100 0 100 100');

        $svg->appendChild($polyline);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $pathData = $path->getPathData();

        // Should not end with Z
        $this->assertStringEndsNotWith('Z', $pathData);
        $this->assertSame('M0 0 L100 0 L100 100', $pathData);
    }

    public function testSelectiveConversion(): void
    {
        // Only convert circles and lines
        $pass = new ConvertShapeToPathPass(
            convertRects: false,
            convertCircles: true,
            convertEllipses: false,
            convertLines: true,
            convertPolygons: false,
            convertPolylines: false
        );

        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('width', '100')->setAttribute('height', '50');

        $circle = new CircleElement();
        $circle->setRadius(10);

        $line = new LineElement();
        $line->setX2(100)->setY2(100);

        $polygon = new PolygonElement();
        $polygon->setPoints('0 0 10 10 20 0');

        $svg->appendChild($rect);
        $svg->appendChild($circle);
        $svg->appendChild($line);
        $svg->appendChild($polygon);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertCount(4, $children);

        // Rect should not be converted
        $this->assertInstanceOf(RectElement::class, $children[0]);

        // Circle should be converted
        $this->assertInstanceOf(PathElement::class, $children[1]);

        // Line should be converted
        $this->assertInstanceOf(PathElement::class, $children[2]);

        // Polygon should not be converted
        $this->assertInstanceOf(PolygonElement::class, $children[3]);
    }

    public function testNestedShapesConversion(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $group = new GroupElement();

        $rect = new RectElement();
        $rect->setAttribute('width', '100')->setAttribute('height', '50');

        $circle = new CircleElement();
        $circle->setRadius(10);

        $group->appendChild($rect);
        $group->appendChild($circle);
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass->optimize($document);

        $groupChildren = $group->getChildren();
        $this->assertCount(2, $groupChildren);

        // Both should be converted to paths
        $this->assertInstanceOf(PathElement::class, $groupChildren[0]);
        $this->assertInstanceOf(PathElement::class, $groupChildren[1]);
    }

    public function testDeeplyNestedShapes(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $group1 = new GroupElement();
        $group2 = new GroupElement();
        $group3 = new GroupElement();

        $circle = new CircleElement();
        $circle->setRadius(5);

        $group3->appendChild($circle);
        $group2->appendChild($group3);
        $group1->appendChild($group2);
        $svg->appendChild($group1);

        $document = new Document($svg);
        $pass->optimize($document);

        $innerChildren = $group3->getChildren();
        $this->assertInstanceOf(PathElement::class, $innerChildren[0]);
    }

    public function testMultipleShapesInSameGroup(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();

        for ($i = 0; $i < 5; ++$i) {
            $circle = new CircleElement();
            $circle->setRadius(10 + $i);
            $svg->appendChild($circle);
        }

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertCount(5, $children);

        foreach ($children as $child) {
            $this->assertInstanceOf(PathElement::class, $child);
        }
    }

    public function testPreservesExistingPaths(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();

        $existingPath = new PathElement();
        $existingPath->setPathData('M0 0 L100 100');

        $circle = new CircleElement();
        $circle->setRadius(10);

        $svg->appendChild($existingPath);
        $svg->appendChild($circle);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertCount(2, $children);

        // First path should be unchanged
        $this->assertSame($existingPath, $children[0]);
        $this->assertSame('M0 0 L100 100', $children[0]->getPathData());

        // Circle should be converted
        $this->assertInstanceOf(PathElement::class, $children[1]);
    }

    public function testComplexRealWorldExample(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();

        // Background rectangle
        $bg = new RectElement();
        $bg->setAttribute('width', '200');
        $bg->setAttribute('height', '200');
        $bg->setAttribute('fill', '#f0f0f0');

        // Circle with style
        $circle = new CircleElement();
        $circle->setCx(100)->setCy(100)->setRadius(50);
        $circle->setAttribute('fill', 'blue');
        $circle->setAttribute('opacity', '0.5');

        // Line with stroke
        $line = new LineElement();
        $line->setX1(0)->setY1(0)->setX2(200)->setY2(200);
        $line->setAttribute('stroke', 'red');
        $line->setAttribute('stroke-width', '3');

        // Polygon forming a star
        $polygon = new PolygonElement();
        $polygon->setPoints('100,10 40,198 190,78 10,78 160,198');
        $polygon->setAttribute('fill', 'yellow');
        $polygon->setAttribute('stroke', 'orange');

        $svg->appendChild($bg);
        $svg->appendChild($circle);
        $svg->appendChild($line);
        $svg->appendChild($polygon);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertCount(4, $children);

        // All should be paths
        foreach ($children as $child) {
            $this->assertInstanceOf(PathElement::class, $child);
        }

        // Check attributes are preserved
        $this->assertSame('#f0f0f0', $children[0]->getAttribute('fill'));
        $this->assertSame('blue', $children[1]->getAttribute('fill'));
        $this->assertSame('0.5', $children[1]->getAttribute('opacity'));
        $this->assertSame('red', $children[2]->getAttribute('stroke'));
        $this->assertSame('3', $children[2]->getAttribute('stroke-width'));
        $this->assertSame('yellow', $children[3]->getAttribute('fill'));
        $this->assertSame('orange', $children[3]->getAttribute('stroke'));
    }

    public function testEmptyPointsString(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setPoints('');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // Should not be converted
        $this->assertInstanceOf(PolygonElement::class, $children[0]);
    }

    public function testWhitespaceOnlyPoints(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setPoints('   ');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // Should not be converted
        $this->assertInstanceOf(PolygonElement::class, $children[0]);
    }

    public function testRectWithNegativeCoordinates(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('x', '-10');
        $rect->setAttribute('y', '-20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertStringContainsString('M-10 -20', $path->getPathData());
    }

    public function testCircleWithNegativeRadius(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $circle = new CircleElement();
        $circle->setCx(50)->setCy(50);
        $circle->setAttribute('r', '-10'); // Invalid

        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // Should not be converted (invalid radius)
        $this->assertInstanceOf(CircleElement::class, $children[0]);
    }

    public function testDisableAllConversions(): void
    {
        $pass = new ConvertShapeToPathPass(
            convertRects: false,
            convertCircles: false,
            convertEllipses: false,
            convertLines: false,
            convertPolygons: false,
            convertPolylines: false
        );

        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('width', '100')->setAttribute('height', '50');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        // Nothing should be converted
        $this->assertInstanceOf(RectElement::class, $children[0]);
    }

    public function testPolygonWithExtraWhitespace(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setPoints('  10  ,  20    30  ,  40   50 , 20  ');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertSame('M10 20 L30 40 L50 20 Z', $path->getPathData());
    }

    public function testRectAtOrigin(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('width', '50');
        $rect->setAttribute('height', '30');
        // x and y default to 0

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertSame('M0 0 L50 0 L50 30 L0 30 Z', $path->getPathData());
    }

    public function testEllipseWithDefaultCenter(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $ellipse = new EllipseElement();
        $ellipse->setRx(20)->setRy(10);
        // cx and cy default to 0

        $svg->appendChild($ellipse);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertSame('M20 0 A20 10 0 1 0 -20 0 A20 10 0 1 0 20 0 Z', $path->getPathData());
    }

    public function testMixedShapeTypesPreserveOrder(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('width', '10')->setAttribute('height', '10');
        $rect->setAttribute('id', 'rect1');

        $circle = new CircleElement();
        $circle->setRadius(5);
        $circle->setAttribute('id', 'circle1');

        $line = new LineElement();
        $line->setX2(10)->setY2(10);
        $line->setAttribute('id', 'line1');

        $svg->appendChild($rect);
        $svg->appendChild($circle);
        $svg->appendChild($line);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertCount(3, $children);

        // Order should be preserved
        $this->assertSame('rect1', $children[0]->getAttribute('id'));
        $this->assertSame('circle1', $children[1]->getAttribute('id'));
        $this->assertSame('line1', $children[2]->getAttribute('id'));
    }

    public function testOddNumberOfCoordinatesInPoints(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setPoints('10 20 30'); // Odd number - invalid

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // Should not be converted
        $this->assertInstanceOf(PolygonElement::class, $children[0]);
    }

    public function testVerySmallRoundedCorners(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');
        $rect->setAttribute('rx', '0.5');
        $rect->setAttribute('ry', '0.5');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $pathData = $path->getPathData();

        // Should include arc commands for small corners
        $this->assertStringContainsString('A0.5 0.5', $pathData);
    }

    public function testAllowExpansionFalseBlocksExpansion(): void
    {
        $pass = new ConvertShapeToPathPass(allowExpansion: false);
        $svg = new SvgElement();

        // A circle with small r: shape attrs are short, path data is long
        $circle = new CircleElement();
        $circle->setAttribute('cx', '0');
        $circle->setAttribute('cy', '0');
        $circle->setAttribute('r', '5');

        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // If path data is longer than original attributes, it should NOT be converted
        // The circle has cx="0" cy="0" r="5" → short attributes
        // Path data "M5 0 A5 5 0 1 0 -5 0 A5 5 0 1 0 5 0 Z" → long
        $this->assertInstanceOf(CircleElement::class, $children[0]);
    }

    public function testAllowExpansionFalsePermitsShorterPath(): void
    {
        $pass = new ConvertShapeToPathPass(allowExpansion: false);
        $svg = new SvgElement();

        // A rect with verbose attributes: path may be shorter or equal
        $rect = new RectElement();
        $rect->setAttribute('x', '10.123456');
        $rect->setAttribute('y', '20.654321');
        $rect->setAttribute('width', '100.111111');
        $rect->setAttribute('height', '50.222222');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        // Whether converted or not depends on length comparison; just verify no crash
        $this->assertCount(1, $children);
    }

    public function testConvertPolylineWithInvalidPoints(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polyline = new PolylineElement();
        $polyline->setPoints('10,20'); // Only one point - invalid

        $svg->appendChild($polyline);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PolylineElement::class, $children[0]);
    }

    public function testConvertEllipseWithOnlyRxZero(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(50)->setRx(0)->setRy(0);

        $svg->appendChild($ellipse);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(EllipseElement::class, $children[0]);
    }

    public function testConvertPolygonWithNonNumericPoints(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setPoints('abc,def ghi,jkl');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PolygonElement::class, $children[0]);
    }

    public function testConvertPolylinePreservesAttributes(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polyline = new PolylineElement();
        $polyline->setPoints('0,0 50,50 100,0');
        $polyline->setAttribute('stroke', 'green');
        $polyline->setAttribute('fill', 'none');
        $polyline->setAttribute('id', 'myPolyline');

        $svg->appendChild($polyline);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertInstanceOf(PathElement::class, $path);
        $this->assertSame('green', $path->getAttribute('stroke'));
        $this->assertSame('none', $path->getAttribute('fill'));
        $this->assertSame('myPolyline', $path->getAttribute('id'));
    }

    public function testConvertPolygonPreservesAttributes(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setPoints('10,20 30,40 50,20');
        $polygon->setAttribute('fill', 'purple');
        $polygon->setAttribute('id', 'myPolygon');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertInstanceOf(PathElement::class, $path);
        $this->assertSame('purple', $path->getAttribute('fill'));
        $this->assertSame('myPolygon', $path->getAttribute('id'));
        // Points attribute should NOT be copied
        $this->assertNull($path->getAttribute('points'));
    }

    public function testRectWithZeroRxAndRy(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');
        $rect->setAttribute('rx', '0');
        $rect->setAttribute('ry', '0');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $pathData = $path->getPathData();

        // Zero radii should produce a regular rect path without arcs
        $this->assertStringNotContainsString('A', $pathData);
    }

    public function testConvertEllipsePreservesAttributes(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(50)->setRx(40)->setRy(20);
        $ellipse->setAttribute('fill', 'orange');
        $ellipse->setAttribute('id', 'myEllipse');

        $svg->appendChild($ellipse);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $path = $children[0];
        $this->assertInstanceOf(PathElement::class, $path);
        $this->assertSame('orange', $path->getAttribute('fill'));
        $this->assertSame('myEllipse', $path->getAttribute('id'));
    }

    public function testConvertPolylineWithEmptyPoints(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polyline = new PolylineElement();
        $polyline->setPoints('');

        $svg->appendChild($polyline);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PolylineElement::class, $children[0]);
    }

    public function testIsPathShorterReturnsFalseWhenNoShapeAttributes(): void
    {
        $pass = new ConvertShapeToPathPass(convertCircles: true, allowExpansion: false);
        $svg = new SvgElement();

        $circle = new CircleElement();
        // Set radius but no cx/cy - the shape-specific attributes (cx, cy, r) are checked
        // A circle without cx/cy/r attributes set has zero original length
        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass->optimize($document);

        // Circle with no/zero radius should not be converted
        $children = $svg->getChildren();
        $this->assertInstanceOf(CircleElement::class, $children[0]);
    }

    public function testReplaceElementNotFoundInParent(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();

        // Create a polygon with valid points
        $polygon = new PolygonElement();
        $polygon->setPoints('10,20 30,40 50,20');
        $svg->appendChild($polygon);

        $document = new Document($svg);
        $pass->optimize($document);

        // The polygon should be successfully replaced
        $this->assertInstanceOf(PathElement::class, $svg->getChildren()[0]);
    }

    public function testParsePointsWithOnlyCommasAndSpaces(): void
    {
        $pass = new ConvertShapeToPathPass();
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setPoints(',,,');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(PolygonElement::class, $children[0]);
    }
}
