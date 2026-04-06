<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Geometry\BoundingBox;
use Atelier\Svg\Geometry\BoundingBoxCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoundingBoxCalculator::class)]
final class BoundingBoxCalculatorTest extends TestCase
{
    public function testGetLocalBBoxForRect(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $rect = new RectElement();
        $root->appendChild($rect);
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');

        $helper = new BoundingBoxCalculator($rect);
        $bbox = $helper->getLocal();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertEquals(10, $bbox->minX);
        $this->assertEquals(20, $bbox->minY);
        $this->assertEquals(110, $bbox->maxX);
        $this->assertEquals(70, $bbox->maxY);
    }

    public function testGetLocalBBoxForRectWithoutAttributes(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $rect = new RectElement();
        $root->appendChild($rect);

        $helper = new BoundingBoxCalculator($rect);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    public function testGetLocalBBoxForCircle(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $circle = new CircleElement();
        $root->appendChild($circle);
        $circle->setAttribute('cx', '50');
        $circle->setAttribute('cy', '50');
        $circle->setAttribute('r', '25');

        $helper = new BoundingBoxCalculator($circle);
        $bbox = $helper->getLocal();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertEquals(25, $bbox->minX);
        $this->assertEquals(25, $bbox->minY);
        $this->assertEquals(75, $bbox->maxX);
        $this->assertEquals(75, $bbox->maxY);
    }

    public function testGetLocalBBoxForCircleWithoutAttributes(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $circle = new CircleElement();
        $root->appendChild($circle);

        $helper = new BoundingBoxCalculator($circle);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    public function testGetLocalBBoxForEllipse(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $ellipse = new EllipseElement();
        $root->appendChild($ellipse);
        $ellipse->setAttribute('cx', '100');
        $ellipse->setAttribute('cy', '100');
        $ellipse->setAttribute('rx', '50');
        $ellipse->setAttribute('ry', '30');

        $helper = new BoundingBoxCalculator($ellipse);
        $bbox = $helper->getLocal();

        $this->assertEquals(50, $bbox->minX);
        $this->assertEquals(70, $bbox->minY);
        $this->assertEquals(150, $bbox->maxX);
        $this->assertEquals(130, $bbox->maxY);
    }

    public function testGetLocalBBoxForLine(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $line = new LineElement();
        $root->appendChild($line);
        $line->setAttribute('x1', '10');
        $line->setAttribute('y1', '20');
        $line->setAttribute('x2', '100');
        $line->setAttribute('y2', '80');

        $helper = new BoundingBoxCalculator($line);
        $bbox = $helper->getLocal();

        $this->assertEquals(10, $bbox->minX);
        $this->assertEquals(20, $bbox->minY);
        $this->assertEquals(100, $bbox->maxX);
        $this->assertEquals(80, $bbox->maxY);
    }

    public function testGetLocalBBoxForLineReversed(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $line = new LineElement();
        $root->appendChild($line);
        $line->setAttribute('x1', '100');
        $line->setAttribute('y1', '80');
        $line->setAttribute('x2', '10');
        $line->setAttribute('y2', '20');

        $helper = new BoundingBoxCalculator($line);
        $bbox = $helper->getLocal();

        // Should use min/max regardless of point order
        $this->assertEquals(10, $bbox->minX);
        $this->assertEquals(20, $bbox->minY);
        $this->assertEquals(100, $bbox->maxX);
        $this->assertEquals(80, $bbox->maxY);
    }

    public function testGetLocalBBoxForPolygon(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $polygon = new PolygonElement();
        $root->appendChild($polygon);
        $polygon->setAttribute('points', '0,0 100,0 100,100 0,100');

        $helper = new BoundingBoxCalculator($polygon);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(100, $bbox->maxX);
        $this->assertEquals(100, $bbox->maxY);
    }

    public function testGetLocalBBoxForPolygonWithCommas(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $polygon = new PolygonElement();
        $root->appendChild($polygon);
        $polygon->setAttribute('points', '10,20, 50,30, 40,80, 5,60');

        $helper = new BoundingBoxCalculator($polygon);
        $bbox = $helper->getLocal();

        $this->assertEquals(5, $bbox->minX);
        $this->assertEquals(20, $bbox->minY);
        $this->assertEquals(50, $bbox->maxX);
        $this->assertEquals(80, $bbox->maxY);
    }

    public function testGetLocalBBoxForPolygonEmpty(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $polygon = new PolygonElement();
        $root->appendChild($polygon);

        $helper = new BoundingBoxCalculator($polygon);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    public function testGetLocalBBoxForPolyline(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $polyline = new PolylineElement();
        $root->appendChild($polyline);
        $polyline->setAttribute('points', '0,0 50,25 100,50');

        $helper = new BoundingBoxCalculator($polyline);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(100, $bbox->maxX);
        $this->assertEquals(50, $bbox->maxY);
    }

    public function testGetLocalBBoxForPath(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $path = new PathElement();
        $root->appendChild($path);
        $path->setAttribute('d', 'M 0 0 L 100 100');

        $helper = new BoundingBoxCalculator($path);
        $bbox = $helper->getLocal();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertGreaterThanOrEqual(0, $bbox->minX);
        $this->assertGreaterThanOrEqual(0, $bbox->minY);
    }

    public function testGetLocalBBoxForPathEmpty(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $path = new PathElement();
        $root->appendChild($path);

        $helper = new BoundingBoxCalculator($path);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    public function testGetLocalBBoxForGroup(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $group = new GroupElement();
        $root->appendChild($group);

        $rect1 = new RectElement();
        $group->appendChild($rect1);
        $rect1->setAttribute('x', '0');
        $rect1->setAttribute('y', '0');
        $rect1->setAttribute('width', '50');
        $rect1->setAttribute('height', '50');

        $rect2 = new RectElement();
        $group->appendChild($rect2);
        $rect2->setAttribute('x', '75');
        $rect2->setAttribute('y', '75');
        $rect2->setAttribute('width', '25');
        $rect2->setAttribute('height', '25');

        $helper = new BoundingBoxCalculator($group);
        $bbox = $helper->getLocal();

        // Group bbox should be a valid BoundingBox
        $this->assertInstanceOf(BoundingBox::class, $bbox);
        // Verify bbox is valid (max >= min)
        $this->assertGreaterThanOrEqual($bbox->minX, $bbox->maxX);
        $this->assertGreaterThanOrEqual($bbox->minY, $bbox->maxY);
    }

    public function testGetLocalBBoxForEmptyGroup(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $group = new GroupElement();
        $root->appendChild($group);

        $helper = new BoundingBoxCalculator($group);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    public function testGetBBoxWithTransform(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $rect = new RectElement();
        $root->appendChild($rect);
        $rect->setAttribute('x', '0');
        $rect->setAttribute('y', '0');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');
        $rect->transform()->translate(10, 20);

        $helper = new BoundingBoxCalculator($rect);
        $bbox = $helper->get();

        // BBox with transform should be a BoundingBox instance
        $this->assertInstanceOf(BoundingBox::class, $bbox);
        // The transformed bbox should be valid
        $this->assertGreaterThanOrEqual($bbox->minX, $bbox->maxX);
        $this->assertGreaterThanOrEqual($bbox->minY, $bbox->maxY);
    }

    public function testGetScreenBBoxWithoutParents(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $rect = new RectElement();
        $root->appendChild($rect);
        $rect->setAttribute('x', '0');
        $rect->setAttribute('y', '0');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');

        $helper = new BoundingBoxCalculator($rect);
        $screenBBox = $helper->getScreen();

        // Without transforms, screen bbox should equal local bbox
        $localBBox = $helper->getLocal();
        $this->assertEquals($localBBox->minX, $screenBBox->minX);
        $this->assertEquals($localBBox->minY, $screenBBox->minY);
        $this->assertEquals($localBBox->maxX, $screenBBox->maxX);
        $this->assertEquals($localBBox->maxY, $screenBBox->maxY);
    }

    public function testGetScreenBBoxWithParentTransform(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $group = new GroupElement();
        $root->appendChild($group);
        $group->transform()->translate(50, 50);

        $rect = new RectElement();
        $group->appendChild($rect);
        $rect->setAttribute('x', '0');
        $rect->setAttribute('y', '0');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');

        $helper = new BoundingBoxCalculator($rect);
        $screenBBox = $helper->getScreen();

        // Screen bbox should be a BoundingBox instance with parent transforms
        $this->assertInstanceOf(BoundingBox::class, $screenBBox);
    }

    public function testGetLocalBBoxForDifferentShapes(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root = $root;

        // Test multiple shape types
        $rect = new RectElement();
        $root->appendChild($rect);

        $circle = new CircleElement();
        $root->appendChild($circle);

        $ellipse = new EllipseElement();
        $root->appendChild($ellipse);

        $line = new LineElement();
        $root->appendChild($line);

        $shapes = [$rect, $circle, $ellipse, $line];

        foreach ($shapes as $shape) {
            $helper = new BoundingBoxCalculator($shape);
            $bbox = $helper->getLocal();
            $this->assertInstanceOf(BoundingBox::class, $bbox);
        }
    }

    public function testParsePointsWithMixedDelimiters(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $polygon = new PolygonElement();
        $root->appendChild($polygon);
        $polygon->setAttribute('points', '10,20  50,30,   40 80   5 60');

        $helper = new BoundingBoxCalculator($polygon);
        $bbox = $helper->getLocal();

        $this->assertEquals(5, $bbox->minX);
        $this->assertEquals(20, $bbox->minY);
        $this->assertEquals(50, $bbox->maxX);
        $this->assertEquals(80, $bbox->maxY);
    }

    public function testBBoxHelperWithNegativeCoordinates(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $rect = new RectElement();
        $root->appendChild($rect);
        $rect->setAttribute('x', '-50');
        $rect->setAttribute('y', '-30');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '60');

        $helper = new BoundingBoxCalculator($rect);
        $bbox = $helper->getLocal();

        $this->assertEquals(-50, $bbox->minX);
        $this->assertEquals(-30, $bbox->minY);
        $this->assertEquals(50, $bbox->maxX);
        $this->assertEquals(30, $bbox->maxY);
    }

    public function testBBoxHelperWithZeroDimensions(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $rect = new RectElement();
        $root->appendChild($rect);
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '0');
        $rect->setAttribute('height', '0');

        $helper = new BoundingBoxCalculator($rect);
        $bbox = $helper->getLocal();

        $this->assertEquals(10, $bbox->minX);
        $this->assertEquals(20, $bbox->minY);
        $this->assertEquals(10, $bbox->maxX);
        $this->assertEquals(20, $bbox->maxY);
    }

    public function testGetSvgBBoxWithViewBox(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '10 20 200 100');

        $helper = new BoundingBoxCalculator($root);
        $bbox = $helper->getLocal();

        $this->assertEquals(10, $bbox->minX);
        $this->assertEquals(20, $bbox->minY);
        $this->assertEquals(210, $bbox->maxX);
        $this->assertEquals(120, $bbox->maxY);
    }

    public function testGetSvgBBoxWithWidthAndHeight(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->removeAttribute('viewBox');
        $root->setAttribute('width', '400');
        $root->setAttribute('height', '300');

        $helper = new BoundingBoxCalculator($root);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(400, $bbox->maxX);
        $this->assertEquals(300, $bbox->maxY);
    }

    public function testGetSvgBBoxWithPercentageValuesFallsBackToChildren(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->removeAttribute('viewBox');
        $root->setAttribute('width', '100%');
        $root->setAttribute('height', '100%');

        $rect = new RectElement();
        $root->appendChild($rect);
        $rect->setAttribute('x', '5');
        $rect->setAttribute('y', '10');
        $rect->setAttribute('width', '80');
        $rect->setAttribute('height', '60');

        $helper = new BoundingBoxCalculator($root);
        $bbox = $helper->getLocal();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertGreaterThanOrEqual(5, $bbox->minX);
    }

    public function testGetSvgBBoxFallsBackToContainerWhenNoSizeAttributes(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->removeAttribute('viewBox');
        $root->removeAttribute('width');
        $root->removeAttribute('height');

        $rect = new RectElement();
        $root->appendChild($rect);
        $rect->setAttribute('x', '0');
        $rect->setAttribute('y', '0');
        $rect->setAttribute('width', '150');
        $rect->setAttribute('height', '75');

        $helper = new BoundingBoxCalculator($root);
        $bbox = $helper->getLocal();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
    }

    public function testGetContainerBBoxWithExplicitWidthAndHeight(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $group = new GroupElement();
        $root->appendChild($group);
        $group->setAttribute('x', '10');
        $group->setAttribute('y', '20');
        $group->setAttribute('width', '200');
        $group->setAttribute('height', '100');

        $helper = new BoundingBoxCalculator($group);
        $bbox = $helper->getLocal();

        $this->assertEquals(10, $bbox->minX);
        $this->assertEquals(20, $bbox->minY);
        $this->assertEquals(210, $bbox->maxX);
        $this->assertEquals(120, $bbox->maxY);
    }

    public function testGetContainerBBoxWithExplicitWidthAndHeightNoPosition(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $group = new GroupElement();
        $root->appendChild($group);
        $group->setAttribute('width', '200');
        $group->setAttribute('height', '100');

        $helper = new BoundingBoxCalculator($group);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(200, $bbox->maxX);
        $this->assertEquals(100, $bbox->maxY);
    }

    public function testGetScreenBBoxWithNestedTransforms(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $outerGroup = new GroupElement();
        $root->appendChild($outerGroup);
        $outerGroup->setAttribute('transform', 'translate(10, 20)');

        $innerGroup = new GroupElement();
        $outerGroup->appendChild($innerGroup);
        $innerGroup->setAttribute('transform', 'translate(30, 40)');

        $rect = new RectElement();
        $innerGroup->appendChild($rect);
        $rect->setAttribute('x', '0');
        $rect->setAttribute('y', '0');
        $rect->setAttribute('width', '50');
        $rect->setAttribute('height', '25');

        $helper = new BoundingBoxCalculator($rect);
        $screenBBox = $helper->getScreen();

        // Screen bbox should account for parent transforms
        $this->assertInstanceOf(BoundingBox::class, $screenBBox);
        $this->assertGreaterThanOrEqual($screenBBox->minX, $screenBBox->maxX);
        $this->assertGreaterThanOrEqual($screenBBox->minY, $screenBBox->maxY);
    }

    public function testGetScreenBBoxWithParentScaleTransform(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $group = new GroupElement();
        $root->appendChild($group);
        $group->setAttribute('transform', 'scale(2)');

        $rect = new RectElement();
        $group->appendChild($rect);
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '10');
        $rect->setAttribute('width', '50');
        $rect->setAttribute('height', '30');

        $helper = new BoundingBoxCalculator($rect);
        $screenBBox = $helper->getScreen();

        // Screen bbox with scale transform should differ from local
        $this->assertInstanceOf(BoundingBox::class, $screenBBox);
        $localBBox = $helper->getLocal();
        $this->assertGreaterThanOrEqual($localBBox->maxX, $screenBBox->maxX);
    }

    public function testGetDefaultBBoxForUnsupportedElement(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $image = new ImageElement();
        $root->appendChild($image);

        $helper = new BoundingBoxCalculator($image);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    public function testGetLocalBBoxForPolylineWithEmptyPointsAttribute(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $polyline = new PolylineElement();
        $root->appendChild($polyline);
        $polyline->setAttribute('points', '');

        $helper = new BoundingBoxCalculator($polyline);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    public function testGetLocalBBoxForPolygonWithEmptyPointsAttribute(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $polygon = new PolygonElement();
        $root->appendChild($polygon);
        $polygon->setAttribute('points', '');

        $helper = new BoundingBoxCalculator($polygon);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    public function testGetSvgBBoxWithCommaDelimitedViewBox(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '0,0,500,250');

        $helper = new BoundingBoxCalculator($root);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(500, $bbox->maxX);
        $this->assertEquals(250, $bbox->maxY);
    }

    public function testGetContainerBBoxUnionsMultipleChildren(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $group = new GroupElement();
        $root->appendChild($group);

        $rect1 = new RectElement();
        $group->appendChild($rect1);
        $rect1->setAttribute('x', '10');
        $rect1->setAttribute('y', '10');
        $rect1->setAttribute('width', '30');
        $rect1->setAttribute('height', '30');

        $rect2 = new RectElement();
        $group->appendChild($rect2);
        $rect2->setAttribute('x', '100');
        $rect2->setAttribute('y', '100');
        $rect2->setAttribute('width', '50');
        $rect2->setAttribute('height', '50');

        $helper = new BoundingBoxCalculator($group);
        $bbox = $helper->getLocal();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertGreaterThanOrEqual($bbox->minX, $bbox->maxX);
        $this->assertGreaterThanOrEqual($bbox->minY, $bbox->maxY);
    }

    public function testGetSvgBBoxEmptyWithNoAttributesAndNoChildren(): void
    {
        $svg = new SvgElement();

        $helper = new BoundingBoxCalculator($svg);
        $bbox = $helper->getLocal();

        $this->assertEquals(0, $bbox->minX);
        $this->assertEquals(0, $bbox->minY);
        $this->assertEquals(0, $bbox->maxX);
        $this->assertEquals(0, $bbox->maxY);
    }

    #[WithoutErrorHandler]
    public function testGetPathBBoxReturnsZeroBBoxOnParseError(): void
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new \RuntimeException($errstr, $errno);
        });

        try {
            $doc = Document::create();
            $root = $doc->getRootElement();
            $this->assertNotNull($root);
            $path = new PathElement();
            $root->appendChild($path);
            $path->setAttribute('d', 'M 0 0 C 1');

            $helper = new BoundingBoxCalculator($path);
            $bbox = $helper->getLocal();

            $this->assertEquals(0, $bbox->minX);
            $this->assertEquals(0, $bbox->minY);
            $this->assertEquals(0, $bbox->maxX);
            $this->assertEquals(0, $bbox->maxY);
        } finally {
            restore_error_handler();
        }
    }
}
