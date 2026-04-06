<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\RadialGradientElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Element\Text\TextElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Builder::class)]
final class BuilderTest extends TestCase
{
    private Builder $builder;

    protected function setUp(): void
    {
        $this->builder = new Builder();
    }

    public function testEndThrowsWhenNoContainerToClose(): void
    {
        $this->expectException(\Atelier\Svg\Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot end: no container to close');

        $this->builder->end();
    }

    public function testSvgCreatesRootElement(): void
    {
        $builder = $this->builder->svg(800, 600);

        $this->assertInstanceOf(Builder::class, $builder);
        $doc = $builder->getDocument();
        $this->assertInstanceOf(Document::class, $doc);

        $root = $doc->getRootElement();
        $this->assertInstanceOf(SvgElement::class, $root);
        $this->assertEquals('800', $root->getAttribute('width'));
        $this->assertEquals('600', $root->getAttribute('height'));
    }

    public function testGetSvgCreatesDefaultSvg(): void
    {
        $svg = $this->builder->getSvg();

        $this->assertInstanceOf(SvgElement::class, $svg);
        $this->assertEquals('300', $svg->getAttribute('width'));
        $this->assertEquals('150', $svg->getAttribute('height'));
    }

    public function testRectCreation(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 20, 100, 50);

        $root = $this->builder->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(RectElement::class, $children[0]);

        $rect = $children[0];
        $this->assertEquals('10', $rect->getAttribute('x'));
        $this->assertEquals('20', $rect->getAttribute('y'));
        $this->assertEquals('100', $rect->getAttribute('width'));
        $this->assertEquals('50', $rect->getAttribute('height'));
    }

    public function testRectWithRoundedCorners(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 20, 100, 50, 5, 5);

        $root = $this->builder->getDocument()->getRootElement();
        $rect = $root->getChildren()[0];

        $this->assertEquals('5', $rect->getAttribute('rx'));
        $this->assertEquals('5', $rect->getAttribute('ry'));
    }

    public function testCircleCreation(): void
    {
        $this->builder->svg(800, 600)
            ->circle(100, 100, 50);

        $root = $this->builder->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(CircleElement::class, $children[0]);

        $circle = $children[0];
        $this->assertEquals('100', $circle->getAttribute('cx'));
        $this->assertEquals('100', $circle->getAttribute('cy'));
        $this->assertEquals('50', $circle->getAttribute('r'));
    }

    public function testEllipseCreation(): void
    {
        $this->builder->svg(800, 600)
            ->ellipse(100, 100, 50, 30);

        $root = $this->builder->getDocument()->getRootElement();
        $ellipse = $root->getChildren()[0];

        $this->assertInstanceOf(EllipseElement::class, $ellipse);
        $this->assertEquals('100', $ellipse->getAttribute('cx'));
        $this->assertEquals('100', $ellipse->getAttribute('cy'));
        $this->assertEquals('50', $ellipse->getAttribute('rx'));
        $this->assertEquals('30', $ellipse->getAttribute('ry'));
    }

    public function testLineCreation(): void
    {
        $this->builder->svg(800, 600)
            ->line(0, 0, 100, 100);

        $root = $this->builder->getDocument()->getRootElement();
        $line = $root->getChildren()[0];

        $this->assertInstanceOf(LineElement::class, $line);
        $this->assertEquals('0', $line->getAttribute('x1'));
        $this->assertEquals('0', $line->getAttribute('y1'));
        $this->assertEquals('100', $line->getAttribute('x2'));
        $this->assertEquals('100', $line->getAttribute('y2'));
    }

    public function testPolygonCreation(): void
    {
        $this->builder->svg(800, 600)
            ->polygon('0,0 100,0 100,100 0,100');

        $root = $this->builder->getDocument()->getRootElement();
        $polygon = $root->getChildren()[0];

        $this->assertInstanceOf(PolygonElement::class, $polygon);
        $this->assertEquals('0,0 100,0 100,100 0,100', $polygon->getAttribute('points'));
    }

    public function testPolylineCreation(): void
    {
        $this->builder->svg(800, 600)
            ->polyline('0,0 100,0 100,100');

        $root = $this->builder->getDocument()->getRootElement();
        $polyline = $root->getChildren()[0];

        $this->assertInstanceOf(PolylineElement::class, $polyline);
        $this->assertEquals('0,0 100,0 100,100', $polyline->getAttribute('points'));
    }

    public function testPathCreationWithFluentApi(): void
    {
        $this->builder->svg(800, 600)
            ->path()
                ->moveTo(0, 0)
                ->lineTo(100, 0)
                ->lineTo(100, 100)
                ->closePath()
            ->end();

        $root = $this->builder->getDocument()->getRootElement();
        $path = $root->getChildren()[0];

        $this->assertInstanceOf(PathElement::class, $path);
        $pathData = $path->getPathData();
        $this->assertNotNull($pathData);
        $this->assertStringContainsString('M', $pathData);
        $this->assertStringContainsString('L', $pathData);
        $this->assertStringContainsString('Z', $pathData);
    }

    public function testTextCreation(): void
    {
        $this->builder->svg(800, 600)
            ->text(10, 20, 'Hello World');

        $root = $this->builder->getDocument()->getRootElement();
        $text = $root->getChildren()[0];

        $this->assertInstanceOf(TextElement::class, $text);
        $this->assertEquals('10', $text->getAttribute('x'));
        $this->assertEquals('20', $text->getAttribute('y'));
        $this->assertEquals('Hello World', $text->getAttribute('textContent'));
    }

    public function testLinearGradientCreation(): void
    {
        $this->builder->svg(800, 600)
            ->linearGradient('grad1', 0, 0, 100, 100);

        $root = $this->builder->getDocument()->getRootElement();
        $gradient = $root->getChildren()[0];

        $this->assertInstanceOf(LinearGradientElement::class, $gradient);
        $this->assertEquals('grad1', $gradient->getAttribute('id'));
        $this->assertEquals('0', $gradient->getAttribute('x1'));
        $this->assertEquals('0', $gradient->getAttribute('y1'));
        $this->assertEquals('100', $gradient->getAttribute('x2'));
        $this->assertEquals('100', $gradient->getAttribute('y2'));
    }

    public function testRadialGradientCreation(): void
    {
        $this->builder->svg(800, 600)
            ->radialGradient('grad2', 50, 50, 100);

        $root = $this->builder->getDocument()->getRootElement();
        $gradient = $root->getChildren()[0];

        $this->assertInstanceOf(RadialGradientElement::class, $gradient);
        $this->assertEquals('grad2', $gradient->getAttribute('id'));
        $this->assertEquals('50', $gradient->getAttribute('cx'));
        $this->assertEquals('50', $gradient->getAttribute('cy'));
        $this->assertEquals('100', $gradient->getAttribute('r'));
    }

    public function testDefsCreation(): void
    {
        $this->builder->svg(800, 600)
            ->defs();

        $root = $this->builder->getDocument()->getRootElement();
        $defs = $root->getChildren()[0];

        $this->assertInstanceOf(DefsElement::class, $defs);
    }

    public function testFillAttribute(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 10, 100, 100)
            ->fill('#ff0000');

        $root = $this->builder->getDocument()->getRootElement();
        $rect = $root->getChildren()[0];

        $this->assertEquals('#ff0000', $rect->getAttribute('fill'));
    }

    public function testStrokeAttribute(): void
    {
        $this->builder->svg(800, 600)
            ->circle(100, 100, 50)
            ->stroke('#000000');

        $root = $this->builder->getDocument()->getRootElement();
        $circle = $root->getChildren()[0];

        $this->assertEquals('#000000', $circle->getAttribute('stroke'));
    }

    public function testStrokeWidthAttribute(): void
    {
        $this->builder->svg(800, 600)
            ->circle(100, 100, 50)
            ->strokeWidth(2);

        $root = $this->builder->getDocument()->getRootElement();
        $circle = $root->getChildren()[0];

        $this->assertEquals('2', $circle->getAttribute('stroke-width'));
    }

    public function testOpacityAttribute(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 10, 100, 100)
            ->opacity(0.5);

        $root = $this->builder->getDocument()->getRootElement();
        $rect = $root->getChildren()[0];

        $this->assertEquals('0.5', $rect->getAttribute('opacity'));
    }

    public function testTransformAttribute(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 10, 100, 100)
            ->transform('rotate(45)');

        $root = $this->builder->getDocument()->getRootElement();
        $rect = $root->getChildren()[0];

        $this->assertEquals('rotate(45)', $rect->getAttribute('transform'));
    }

    public function testIdAttribute(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 10, 100, 100)
            ->id('myRect');

        $root = $this->builder->getDocument()->getRootElement();
        $rect = $root->getChildren()[0];

        $this->assertEquals('myRect', $rect->getAttribute('id'));
    }

    public function testClassAttribute(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 10, 100, 100)
            ->class('my-class');

        $root = $this->builder->getDocument()->getRootElement();
        $rect = $root->getChildren()[0];

        $this->assertEquals('my-class', $rect->getAttribute('class'));
    }

    public function testCustomAttribute(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 10, 100, 100)
            ->attr('data-id', '123');

        $root = $this->builder->getDocument()->getRootElement();
        $rect = $root->getChildren()[0];

        $this->assertEquals('123', $rect->getAttribute('data-id'));
    }

    public function testChainedAttributes(): void
    {
        $this->builder->svg(800, 600)
            ->circle(100, 100, 50)
            ->fill('#ff0000')
            ->stroke('#000000')
            ->strokeWidth(2)
            ->opacity(0.8);

        $root = $this->builder->getDocument()->getRootElement();
        $circle = $root->getChildren()[0];

        $this->assertEquals('#ff0000', $circle->getAttribute('fill'));
        $this->assertEquals('#000000', $circle->getAttribute('stroke'));
        $this->assertEquals('2', $circle->getAttribute('stroke-width'));
        $this->assertEquals('0.8', $circle->getAttribute('opacity'));
    }

    public function testGroupCreation(): void
    {
        $this->builder->svg(800, 600)
            ->g();

        $root = $this->builder->getDocument()->getRootElement();
        $group = $root->getChildren()[0];

        $this->assertInstanceOf(GroupElement::class, $group);
    }

    public function testNestedElements(): void
    {
        $this->builder->svg(800, 600)
            ->g()
                ->rect(10, 10, 100, 100)->end()
                ->rect(120, 10, 100, 100)->end()
            ->end();

        $root = $this->builder->getDocument()->getRootElement();
        $group = $root->getChildren()[0];

        $this->assertInstanceOf(GroupElement::class, $group);
        $this->assertCount(2, $group->getChildren());
        $this->assertInstanceOf(RectElement::class, $group->getChildren()[0]);
        $this->assertInstanceOf(RectElement::class, $group->getChildren()[1]);
    }

    public function testComplexNestedStructure(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 10, 100, 100)->fill('#ff0000')->end()
            ->circle(200, 200, 50)->fill('#00ff00')->stroke('#000')->strokeWidth(2)->end()
            ->g()
                ->rect(300, 100, 50, 50)->fill('#0000ff')->end()
                ->rect(360, 100, 50, 50)->fill('#ffff00')->end()
            ->end();

        $root = $this->builder->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(3, $children);
        $this->assertInstanceOf(RectElement::class, $children[0]);
        $this->assertInstanceOf(CircleElement::class, $children[1]);
        $this->assertInstanceOf(GroupElement::class, $children[2]);

        $groupChildren = $children[2]->getChildren();
        $this->assertCount(2, $groupChildren);
    }

    public function testDeepNesting(): void
    {
        $this->builder->svg(800, 600)
            ->g()
                ->g()
                    ->g()
                        ->rect(10, 10, 100, 100)->end()
                    ->end()
                ->end()
            ->end();

        $root = $this->builder->getDocument()->getRootElement();
        $g1 = $root->getChildren()[0];
        $g2 = $g1->getChildren()[0];
        $g3 = $g2->getChildren()[0];
        $rect = $g3->getChildren()[0];

        $this->assertInstanceOf(GroupElement::class, $g1);
        $this->assertInstanceOf(GroupElement::class, $g2);
        $this->assertInstanceOf(GroupElement::class, $g3);
        $this->assertInstanceOf(RectElement::class, $rect);
    }

    public function testUseElement(): void
    {
        $this->builder->svg(800, 600)
            ->use('#myRect', 100, 100);

        $root = $this->builder->getDocument()->getRootElement();
        $use = $root->getChildren()[0];

        $this->assertEquals('#myRect', $use->getAttribute('href'));
        $this->assertEquals('100', $use->getAttribute('x'));
        $this->assertEquals('100', $use->getAttribute('y'));
    }

    public function testImageElement(): void
    {
        $this->builder->svg(800, 600)
            ->image('image.png', 10, 10, 100, 100);

        $root = $this->builder->getDocument()->getRootElement();
        $image = $root->getChildren()[0];

        $this->assertEquals('image.png', $image->getAttribute('href'));
        $this->assertEquals('10', $image->getAttribute('x'));
        $this->assertEquals('10', $image->getAttribute('y'));
        $this->assertEquals('100', $image->getAttribute('width'));
        $this->assertEquals('100', $image->getAttribute('height'));
    }

    public function testGetDocumentWithoutSvg(): void
    {
        $doc = $this->builder->getDocument();

        $this->assertInstanceOf(Document::class, $doc);
        $root = $doc->getRootElement();
        $this->assertInstanceOf(SvgElement::class, $root);
    }

    public function testThrowsExceptionWhenSettingAttributeWithoutElement(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No current element to set attributes on');

        $this->builder->fill('#ff0000');
    }

    public function testThrowsExceptionWhenAddingElementWithoutSvg(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No container to add element to');

        $builder = new Builder();
        $builder->rect(10, 10, 100, 100);
    }

    public function testToStringSerializesDocument(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 10, 100, 100);

        $output = $this->builder->toString();

        $this->assertNotEmpty($output);
        $this->assertIsString($output);
    }

    public function testMultipleShapesInSequence(): void
    {
        $this->builder->svg(800, 600)
            ->rect(10, 10, 50, 50)->end()
            ->circle(100, 100, 25)->end()
            ->ellipse(200, 100, 30, 20)->end()
            ->line(0, 0, 100, 100)->end();

        $root = $this->builder->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(4, $children);
        $this->assertInstanceOf(RectElement::class, $children[0]);
        $this->assertInstanceOf(CircleElement::class, $children[1]);
        $this->assertInstanceOf(EllipseElement::class, $children[2]);
        $this->assertInstanceOf(LineElement::class, $children[3]);
    }

    public function testTextWithoutContent(): void
    {
        $this->builder->svg(800, 600)
            ->text(10, 20);

        $root = $this->builder->getDocument()->getRootElement();
        $text = $root->getChildren()[0];

        $this->assertInstanceOf(TextElement::class, $text);
        $this->assertEquals('10', $text->getAttribute('x'));
        $this->assertEquals('20', $text->getAttribute('y'));
    }

    public function testImageWithMinimalParams(): void
    {
        $this->builder->svg(800, 600)
            ->image('img.svg');

        $root = $this->builder->getDocument()->getRootElement();
        $image = $root->getChildren()[0];

        $this->assertEquals('img.svg', $image->getAttribute('href'));
        $this->assertNull($image->getAttribute('x'));
        $this->assertNull($image->getAttribute('y'));
    }

    public function testUseWithMinimalParams(): void
    {
        $this->builder->svg(800, 600)
            ->use('#ref');

        $root = $this->builder->getDocument()->getRootElement();
        $use = $root->getChildren()[0];

        $this->assertEquals('#ref', $use->getAttribute('href'));
        $this->assertNull($use->getAttribute('x'));
        $this->assertNull($use->getAttribute('y'));
    }

    public function testLinearGradientWithMinimalParams(): void
    {
        $this->builder->svg(800, 600)
            ->linearGradient('grad-min');

        $root = $this->builder->getDocument()->getRootElement();
        $gradient = $root->getChildren()[0];

        $this->assertInstanceOf(LinearGradientElement::class, $gradient);
        $this->assertEquals('grad-min', $gradient->getAttribute('id'));
    }

    public function testRadialGradientWithMinimalParams(): void
    {
        $this->builder->svg(800, 600)
            ->radialGradient('rgrad-min');

        $root = $this->builder->getDocument()->getRootElement();
        $gradient = $root->getChildren()[0];

        $this->assertInstanceOf(RadialGradientElement::class, $gradient);
        $this->assertEquals('rgrad-min', $gradient->getAttribute('id'));
    }
}
