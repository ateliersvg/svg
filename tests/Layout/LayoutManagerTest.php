<?php

namespace Atelier\Svg\Tests\Layout;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Layout\LayoutManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LayoutManager::class)]
final class LayoutManagerTest extends TestCase
{
    public function testMakeResponsive(): void
    {
        $doc = Document::create(800, 600);
        LayoutManager::makeResponsive($doc);

        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $this->assertNull($root->getAttribute('width'));
        $this->assertNull($root->getAttribute('height'));
        $this->assertEquals('0 0 800 600', $root->getAttribute('viewBox'));
    }

    public function testMakeResponsivePreservesExistingViewBox(): void
    {
        $doc = Document::create(800, 600);
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '0 0 100 100');

        LayoutManager::makeResponsive($doc);

        $this->assertNull($root->getAttribute('width'));
        $this->assertNull($root->getAttribute('height'));
        $this->assertEquals('0 0 100 100', $root->getAttribute('viewBox'));
    }

    public function testSetIntrinsicSize(): void
    {
        $doc = Document::create();
        LayoutManager::setIntrinsicSize($doc, 800, 600);

        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $this->assertEquals('800', $root->getAttribute('width'));
        $this->assertEquals('600', $root->getAttribute('height'));
        $this->assertEquals('0 0 800 600', $root->getAttribute('viewBox'));
    }

    public function testGetViewBox(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '10 20 100 50');

        $viewBox = LayoutManager::getViewBox($doc);

        $this->assertEquals([10.0, 20.0, 100.0, 50.0], $viewBox);
    }

    public function testGetViewBoxReturnsNullWhenNotSet(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->removeAttribute('viewBox');

        $viewBox = LayoutManager::getViewBox($doc);

        $this->assertNull($viewBox);
    }

    public function testSetViewBox(): void
    {
        $doc = Document::create();
        LayoutManager::setViewBox($doc, 10, 20, 100, 50);

        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $this->assertEquals('10 20 100 50', $root->getAttribute('viewBox'));
    }

    public function testSetPreserveAspectRatio(): void
    {
        $doc = Document::create();
        LayoutManager::setPreserveAspectRatio($doc, 'xMidYMid meet');

        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $this->assertEquals('xMidYMid meet', $root->getAttribute('preserveAspectRatio'));
    }

    public function testGetContentBoundsWithRect(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');
        $root->appendChild($rect);

        $bounds = LayoutManager::getContentBounds($doc);

        $this->assertNotNull($bounds);
        $this->assertEquals(10.0, $bounds['minX']);
        $this->assertEquals(20.0, $bounds['minY']);
        $this->assertEquals(110.0, $bounds['maxX']);
        $this->assertEquals(70.0, $bounds['maxY']);
    }

    public function testGetContentBoundsWithCircle(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $circle = new CircleElement();
        $circle->setAttribute('cx', '50');
        $circle->setAttribute('cy', '50');
        $circle->setAttribute('r', '25');
        $root->appendChild($circle);

        $bounds = LayoutManager::getContentBounds($doc);

        $this->assertNotNull($bounds);
        $this->assertEquals(25.0, $bounds['minX']);
        $this->assertEquals(25.0, $bounds['minY']);
        $this->assertEquals(75.0, $bounds['maxX']);
        $this->assertEquals(75.0, $bounds['maxY']);
    }

    public function testGetContentBoundsWithEllipse(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $ellipse = new EllipseElement();
        $ellipse->setAttribute('cx', '50');
        $ellipse->setAttribute('cy', '50');
        $ellipse->setAttribute('rx', '30');
        $ellipse->setAttribute('ry', '20');
        $root->appendChild($ellipse);

        $bounds = LayoutManager::getContentBounds($doc);

        $this->assertNotNull($bounds);
        $this->assertEquals(20.0, $bounds['minX']);
        $this->assertEquals(30.0, $bounds['minY']);
        $this->assertEquals(80.0, $bounds['maxX']);
        $this->assertEquals(70.0, $bounds['maxY']);
    }

    public function testGetContentBoundsWithLine(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $line = new LineElement();
        $line->setAttribute('x1', '10');
        $line->setAttribute('y1', '20');
        $line->setAttribute('x2', '100');
        $line->setAttribute('y2', '80');
        $root->appendChild($line);

        $bounds = LayoutManager::getContentBounds($doc);

        $this->assertNotNull($bounds);
        $this->assertEquals(10.0, $bounds['minX']);
        $this->assertEquals(20.0, $bounds['minY']);
        $this->assertEquals(100.0, $bounds['maxX']);
        $this->assertEquals(80.0, $bounds['maxY']);
    }

    public function testGetContentBoundsWithMultipleElements(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '50');
        $rect->setAttribute('height', '30');
        $root->appendChild($rect);

        $circle = new CircleElement();
        $circle->setAttribute('cx', '100');
        $circle->setAttribute('cy', '100');
        $circle->setAttribute('r', '20');
        $root->appendChild($circle);

        $bounds = LayoutManager::getContentBounds($doc);

        $this->assertNotNull($bounds);
        $this->assertEquals(10.0, $bounds['minX']);
        $this->assertEquals(20.0, $bounds['minY']);
        $this->assertEquals(120.0, $bounds['maxX']);
        $this->assertEquals(120.0, $bounds['maxY']);
    }

    public function testGetContentBoundsReturnsNullForEmptyDocument(): void
    {
        $doc = Document::create();

        $bounds = LayoutManager::getContentBounds($doc);

        $this->assertNull($bounds);
    }

    public function testFitViewBoxToContent(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');
        $root->appendChild($rect);

        LayoutManager::fitViewBoxToContent($doc);

        $this->assertEquals('10 20 100 50', $root->getAttribute('viewBox'));
    }

    public function testFitViewBoxToContentWithPadding(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');
        $root->appendChild($rect);

        LayoutManager::fitViewBoxToContent($doc, padding: 5);

        $this->assertEquals('5 15 110 60', $root->getAttribute('viewBox'));
    }

    public function testCropToContent(): void
    {
        $doc = Document::create(800, 600);
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');
        $root->appendChild($rect);

        LayoutManager::cropToContent($doc);

        $this->assertNull($root->getAttribute('width'));
        $this->assertNull($root->getAttribute('height'));
        $this->assertEquals('10 20 100 50', $root->getAttribute('viewBox'));
    }

    public function testCropToContentWithPadding(): void
    {
        $doc = Document::create(800, 600);
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');
        $root->appendChild($rect);

        LayoutManager::cropToContent($doc, padding: 10);

        $this->assertNull($root->getAttribute('width'));
        $this->assertNull($root->getAttribute('height'));
        $this->assertEquals('0 10 120 70', $root->getAttribute('viewBox'));
    }

    public function testSetAspectRatioWithExistingViewBox(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '0 0 100 100');

        LayoutManager::setAspectRatio($doc, 16 / 9);

        $viewBox = LayoutManager::getViewBox($doc);
        $this->assertNotNull($viewBox);
        $ratio = $viewBox[2] / $viewBox[3];
        $this->assertEqualsWithDelta(16 / 9, $ratio, 0.01);
    }

    public function testSetAspectRatioWithoutViewBox(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->removeAttribute('viewBox');

        LayoutManager::setAspectRatio($doc, 16 / 9);

        $viewBox = LayoutManager::getViewBox($doc);
        $this->assertNotNull($viewBox);
        $ratio = $viewBox[2] / $viewBox[3];
        $this->assertEqualsWithDelta(16 / 9, $ratio, 0.01);
    }

    public function testMethodChainingReturnsDocument(): void
    {
        $doc = Document::create();

        $result = LayoutManager::makeResponsive($doc);
        $this->assertSame($doc, $result);

        $result = LayoutManager::setIntrinsicSize($doc, 100, 100);
        $this->assertSame($doc, $result);

        $result = LayoutManager::setViewBox($doc, 0, 0, 100, 100);
        $this->assertSame($doc, $result);

        $result = LayoutManager::setPreserveAspectRatio($doc, 'none');
        $this->assertSame($doc, $result);

        $result = LayoutManager::fitViewBoxToContent($doc);
        $this->assertSame($doc, $result);

        $result = LayoutManager::cropToContent($doc);
        $this->assertSame($doc, $result);

        $result = LayoutManager::setAspectRatio($doc, 1.0);
        $this->assertSame($doc, $result);
    }

    public function testMakeResponsiveWithNoRootReturnsDocument(): void
    {
        $doc = new Document();
        $result = LayoutManager::makeResponsive($doc);
        $this->assertSame($doc, $result);
    }

    public function testSetIntrinsicSizeWithNoRootReturnsDocument(): void
    {
        $doc = new Document();
        $result = LayoutManager::setIntrinsicSize($doc, 100, 100);
        $this->assertSame($doc, $result);
    }

    public function testGetViewBoxWithNoRootReturnsNull(): void
    {
        $doc = new Document();
        $result = LayoutManager::getViewBox($doc);
        $this->assertNull($result);
    }

    public function testGetViewBoxWithInvalidFormatReturnsNull(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '0 0 100');

        $result = LayoutManager::getViewBox($doc);
        $this->assertNull($result);
    }

    public function testSetViewBoxWithNoRootReturnsDocument(): void
    {
        $doc = new Document();
        $result = LayoutManager::setViewBox($doc, 0, 0, 100, 100);
        $this->assertSame($doc, $result);
    }

    public function testSetPreserveAspectRatioWithNoRootReturnsDocument(): void
    {
        $doc = new Document();
        $result = LayoutManager::setPreserveAspectRatio($doc, 'none');
        $this->assertSame($doc, $result);
    }

    public function testGetContentBoundsWithNoRootReturnsNull(): void
    {
        $doc = new Document();
        $result = LayoutManager::getContentBounds($doc);
        $this->assertNull($result);
    }

    public function testSetAspectRatioNarrowRatioIncreasesHeight(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '0 0 100 100');

        // ratio < currentRatio (1.0), so height should increase
        LayoutManager::setAspectRatio($doc, 0.5);

        $viewBox = LayoutManager::getViewBox($doc);
        $this->assertNotNull($viewBox);
        $ratio = $viewBox[2] / $viewBox[3];
        $this->assertEqualsWithDelta(0.5, $ratio, 0.01);
    }

    public function testMakeResponsiveWithoutDimensionsUsesDefaults(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->removeAttribute('width');
        $root->removeAttribute('height');
        $root->removeAttribute('viewBox');

        LayoutManager::makeResponsive($doc);

        $viewBox = LayoutManager::getViewBox($doc);
        $this->assertNotNull($viewBox);
        $this->assertEquals(300.0, $viewBox[2]);
        $this->assertEquals(150.0, $viewBox[3]);
    }

    public function testGetContentBoundsWithRectMissingDimensions(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        // Missing width and height
        $root->appendChild($rect);

        $bounds = LayoutManager::getContentBounds($doc);
        $this->assertNull($bounds);
    }

    public function testGetContentBoundsWithCircleMissingRadius(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $circle = new CircleElement();
        $circle->setAttribute('cx', '50');
        $circle->setAttribute('cy', '50');
        // Missing r attribute
        $root->appendChild($circle);

        $bounds = LayoutManager::getContentBounds($doc);
        $this->assertNull($bounds);
    }

    public function testGetContentBoundsWithEllipseMissingRadii(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $ellipse = new EllipseElement();
        $ellipse->setAttribute('cx', '50');
        $ellipse->setAttribute('cy', '50');
        // Missing rx and ry
        $root->appendChild($ellipse);

        $bounds = LayoutManager::getContentBounds($doc);
        $this->assertNull($bounds);
    }
}
