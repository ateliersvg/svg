<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Structural;

use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\PreserveAspectRatio;
use Atelier\Svg\Value\Viewbox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SymbolElement::class)]
final class SymbolElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $symbol = new SymbolElement();

        $this->assertSame('symbol', $symbol->getTagName());
    }

    public function testIsContainerElement(): void
    {
        $symbol = new SymbolElement();
        $circle = new CircleElement();

        $symbol->appendChild($circle);

        $this->assertTrue($symbol->hasChildren());
        $this->assertCount(1, $symbol->getChildren());
        $this->assertSame($circle, $symbol->getChildren()[0]);
    }

    public function testSetAndGetViewboxWithString(): void
    {
        $symbol = new SymbolElement();
        $result = $symbol->setViewbox('0 0 100 100');

        $this->assertSame($symbol, $result, 'setViewbox should return self for chaining');

        $viewbox = $symbol->getViewbox();
        $this->assertInstanceOf(Viewbox::class, $viewbox);
        $this->assertSame(0.0, $viewbox->getMinX());
        $this->assertSame(0.0, $viewbox->getMinY());
        $this->assertSame(100.0, $viewbox->getWidth());
        $this->assertSame(100.0, $viewbox->getHeight());
    }

    public function testSetAndGetViewboxWithObject(): void
    {
        $symbol = new SymbolElement();
        $viewbox = new Viewbox(10, 20, 200, 300);
        $symbol->setViewbox($viewbox);

        $retrieved = $symbol->getViewbox();
        $this->assertInstanceOf(Viewbox::class, $retrieved);
        $this->assertSame(10.0, $retrieved->getMinX());
        $this->assertSame(20.0, $retrieved->getMinY());
        $this->assertSame(200.0, $retrieved->getWidth());
        $this->assertSame(300.0, $retrieved->getHeight());
    }

    public function testGetViewboxReturnsNullWhenNotSet(): void
    {
        $symbol = new SymbolElement();

        $this->assertNull($symbol->getViewbox());
    }

    public function testSetAndGetPreserveAspectRatioWithString(): void
    {
        $symbol = new SymbolElement();
        $result = $symbol->setPreserveAspectRatio('xMidYMid meet');

        $this->assertSame($symbol, $result, 'setPreserveAspectRatio should return self for chaining');

        $par = $symbol->getPreserveAspectRatio();
        $this->assertInstanceOf(PreserveAspectRatio::class, $par);
        $this->assertSame('xMidYMid', $par->getAlign());
        $this->assertSame('meet', $par->getMeetOrSlice());
    }

    public function testSetAndGetPreserveAspectRatioWithObject(): void
    {
        $symbol = new SymbolElement();
        $par = PreserveAspectRatio::fromAlignment('xMax', 'YMax', 'slice');
        $symbol->setPreserveAspectRatio($par);

        $retrieved = $symbol->getPreserveAspectRatio();
        $this->assertInstanceOf(PreserveAspectRatio::class, $retrieved);
        $this->assertSame('xMaxYMax', $retrieved->getAlign());
        $this->assertSame('slice', $retrieved->getMeetOrSlice());
    }

    public function testGetPreserveAspectRatioReturnsNullWhenNotSet(): void
    {
        $symbol = new SymbolElement();

        $this->assertNull($symbol->getPreserveAspectRatio());
    }

    public function testSetAndGetX(): void
    {
        $symbol = new SymbolElement();
        $result = $symbol->setX(10);

        $this->assertSame($symbol, $result, 'setX should return self for chaining');
        $this->assertSame('10', $symbol->getAttribute('x'));

        $x = $symbol->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(10.0, $x->getValue());
    }

    public function testSetAndGetXWithString(): void
    {
        $symbol = new SymbolElement();
        $symbol->setX('20px');

        $x = $symbol->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(20.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
    }

    public function testGetXReturnsNullWhenNotSet(): void
    {
        $symbol = new SymbolElement();

        $this->assertNull($symbol->getX());
    }

    public function testSetAndGetY(): void
    {
        $symbol = new SymbolElement();
        $result = $symbol->setY(20);

        $this->assertSame($symbol, $result, 'setY should return self for chaining');
        $this->assertSame('20', $symbol->getAttribute('y'));

        $y = $symbol->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(20.0, $y->getValue());
    }

    public function testSetAndGetYWithString(): void
    {
        $symbol = new SymbolElement();
        $symbol->setY('30%');

        $y = $symbol->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(30.0, $y->getValue());
        $this->assertSame('%', $y->getUnit());
    }

    public function testGetYReturnsNullWhenNotSet(): void
    {
        $symbol = new SymbolElement();

        $this->assertNull($symbol->getY());
    }

    public function testSetAndGetWidth(): void
    {
        $symbol = new SymbolElement();
        $result = $symbol->setWidth(100);

        $this->assertSame($symbol, $result, 'setWidth should return self for chaining');
        $this->assertSame('100', $symbol->getAttribute('width'));

        $width = $symbol->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(100.0, $width->getValue());
    }

    public function testSetAndGetWidthWithString(): void
    {
        $symbol = new SymbolElement();
        $symbol->setWidth('150em');

        $width = $symbol->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(150.0, $width->getValue());
        $this->assertSame('em', $width->getUnit());
    }

    public function testGetWidthReturnsNullWhenNotSet(): void
    {
        $symbol = new SymbolElement();

        $this->assertNull($symbol->getWidth());
    }

    public function testSetAndGetHeight(): void
    {
        $symbol = new SymbolElement();
        $result = $symbol->setHeight(200);

        $this->assertSame($symbol, $result, 'setHeight should return self for chaining');
        $this->assertSame('200', $symbol->getAttribute('height'));

        $height = $symbol->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(200.0, $height->getValue());
    }

    public function testSetAndGetHeightWithString(): void
    {
        $symbol = new SymbolElement();
        $symbol->setHeight('250mm');

        $height = $symbol->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(250.0, $height->getValue());
        $this->assertSame('mm', $height->getUnit());
    }

    public function testGetHeightReturnsNullWhenNotSet(): void
    {
        $symbol = new SymbolElement();

        $this->assertNull($symbol->getHeight());
    }

    public function testMethodChaining(): void
    {
        $symbol = new SymbolElement();
        $result = $symbol
            ->setViewbox('0 0 100 100')
            ->setPreserveAspectRatio('xMidYMid meet')
            ->setX(10)
            ->setY(20)
            ->setWidth(100)
            ->setHeight(200);

        $this->assertSame($symbol, $result);
        $this->assertInstanceOf(Viewbox::class, $symbol->getViewbox());
        $this->assertInstanceOf(PreserveAspectRatio::class, $symbol->getPreserveAspectRatio());
        $this->assertSame(10.0, $symbol->getX()->getValue());
        $this->assertSame(20.0, $symbol->getY()->getValue());
        $this->assertSame(100.0, $symbol->getWidth()->getValue());
        $this->assertSame(200.0, $symbol->getHeight()->getValue());
    }

    public function testCompleteSymbolConfiguration(): void
    {
        $symbol = new SymbolElement();
        $symbol
            ->setViewbox('0 0 200 200')
            ->setPreserveAspectRatio('xMinYMin slice')
            ->setX('10px')
            ->setY('20px')
            ->setWidth('100px')
            ->setHeight('200px');

        $this->assertSame('0 0 200 200', $symbol->getAttribute('viewBox'));
        $this->assertSame('xMinYMin slice', $symbol->getAttribute('preserveAspectRatio'));
        $this->assertSame('10px', $symbol->getAttribute('x'));
        $this->assertSame('20px', $symbol->getAttribute('y'));
        $this->assertSame('100px', $symbol->getAttribute('width'));
        $this->assertSame('200px', $symbol->getAttribute('height'));
    }

    public function testSymbolWithChildren(): void
    {
        $symbol = new SymbolElement();
        $circle1 = new CircleElement();
        $circle2 = new CircleElement();

        $symbol->appendChild($circle1);
        $symbol->appendChild($circle2);

        $this->assertCount(2, $symbol->getChildren());
        $this->assertSame($circle1, $symbol->getChildren()[0]);
        $this->assertSame($circle2, $symbol->getChildren()[1]);
    }
}
