<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Text\TextElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RectElement::class)]
#[CoversClass(CircleElement::class)]
#[CoversClass(EllipseElement::class)]
#[CoversClass(LineElement::class)]
#[CoversClass(PolygonElement::class)]
#[CoversClass(PolylineElement::class)]
#[CoversClass(TextElement::class)]
final class FactoryMethodTest extends TestCase
{
    public function testRectCreate(): void
    {
        $rect = RectElement::create(10, 20, 100, 200);

        $this->assertInstanceOf(RectElement::class, $rect);
        $this->assertSame('10', $rect->getAttribute('x'));
        $this->assertSame('20', $rect->getAttribute('y'));
        $this->assertSame('100', $rect->getAttribute('width'));
        $this->assertSame('200', $rect->getAttribute('height'));
    }

    public function testRectCreateWithStringValues(): void
    {
        $rect = RectElement::create('5px', '10px', '50%', '80%');

        $this->assertSame('5px', $rect->getAttribute('x'));
        $this->assertSame('10px', $rect->getAttribute('y'));
        $this->assertSame('50%', $rect->getAttribute('width'));
        $this->assertSame('80%', $rect->getAttribute('height'));
    }

    public function testRectCreateAllowsChaining(): void
    {
        $rect = RectElement::create(0, 0, 100, 100)->setFill('#f00');

        $this->assertInstanceOf(RectElement::class, $rect);
        $this->assertSame('#f00', $rect->getAttribute('fill'));
        $this->assertSame('100', $rect->getAttribute('width'));
    }

    public function testCircleCreate(): void
    {
        $circle = CircleElement::create(50, 50, 25);

        $this->assertInstanceOf(CircleElement::class, $circle);
        $this->assertSame('50', $circle->getAttribute('cx'));
        $this->assertSame('50', $circle->getAttribute('cy'));
        $this->assertSame('25', $circle->getAttribute('r'));
    }

    public function testCircleCreateWithFloats(): void
    {
        $circle = CircleElement::create(10.5, 20.5, 5.5);

        $this->assertSame('10.5', $circle->getAttribute('cx'));
        $this->assertSame('20.5', $circle->getAttribute('cy'));
        $this->assertSame('5.5', $circle->getAttribute('r'));
    }

    public function testEllipseCreate(): void
    {
        $ellipse = EllipseElement::create(100, 200, 30, 40);

        $this->assertInstanceOf(EllipseElement::class, $ellipse);
        $this->assertSame('100', $ellipse->getAttribute('cx'));
        $this->assertSame('200', $ellipse->getAttribute('cy'));
        $this->assertSame('30', $ellipse->getAttribute('rx'));
        $this->assertSame('40', $ellipse->getAttribute('ry'));
    }

    public function testLineCreate(): void
    {
        $line = LineElement::create(0, 0, 100, 200);

        $this->assertInstanceOf(LineElement::class, $line);
        $this->assertSame('0', $line->getAttribute('x1'));
        $this->assertSame('0', $line->getAttribute('y1'));
        $this->assertSame('100', $line->getAttribute('x2'));
        $this->assertSame('200', $line->getAttribute('y2'));
    }

    public function testPolygonCreate(): void
    {
        $polygon = PolygonElement::create('0,0 100,0 100,100 0,100');

        $this->assertInstanceOf(PolygonElement::class, $polygon);
        $this->assertSame('0,0 100,0 100,100 0,100', $polygon->getAttribute('points'));
    }

    public function testPolylineCreate(): void
    {
        $polyline = PolylineElement::create('0,0 50,50 100,0');

        $this->assertInstanceOf(PolylineElement::class, $polyline);
        $this->assertSame('0,0 50,50 100,0', $polyline->getAttribute('points'));
    }

    public function testTextCreateWithContent(): void
    {
        $text = TextElement::create(10, 20, 'Hello World');

        $this->assertInstanceOf(TextElement::class, $text);
        $this->assertSame('10', $text->getAttribute('x'));
        $this->assertSame('20', $text->getAttribute('y'));
        $this->assertSame('Hello World', $text->getTextContent());
    }

    public function testTextCreateWithoutContent(): void
    {
        $text = TextElement::create(10, 20);

        $this->assertInstanceOf(TextElement::class, $text);
        $this->assertSame('10', $text->getAttribute('x'));
        $this->assertSame('20', $text->getAttribute('y'));
        $this->assertNull($text->getTextContent());
    }

    public function testTextCreateAllowsChaining(): void
    {
        $text = TextElement::create(10, 20)->setTextContent('Chained');

        $this->assertInstanceOf(TextElement::class, $text);
        $this->assertSame('Chained', $text->getTextContent());
    }
}
