<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Shape;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RectElement::class)]
final class RectElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $rect = new RectElement();

        $this->assertSame('rect', $rect->getTagName());
    }

    public function testSetAndGetX(): void
    {
        $rect = new RectElement();
        $result = $rect->setX(10);

        $this->assertSame($rect, $result, 'setX should return self for chaining');
        $this->assertSame('10', $rect->getAttribute('x'));

        $x = $rect->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(10.0, $x->getValue());
    }

    public function testSetAndGetXWithString(): void
    {
        $rect = new RectElement();
        $rect->setX('20px');

        $x = $rect->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(20.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
    }

    public function testGetXReturnsNullWhenNotSet(): void
    {
        $rect = new RectElement();

        $this->assertNull($rect->getX());
    }

    public function testSetAndGetY(): void
    {
        $rect = new RectElement();
        $result = $rect->setY(20);

        $this->assertSame($rect, $result, 'setY should return self for chaining');
        $this->assertSame('20', $rect->getAttribute('y'));

        $y = $rect->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(20.0, $y->getValue());
    }

    public function testSetAndGetYWithString(): void
    {
        $rect = new RectElement();
        $rect->setY('30%');

        $y = $rect->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(30.0, $y->getValue());
        $this->assertSame('%', $y->getUnit());
    }

    public function testGetYReturnsNullWhenNotSet(): void
    {
        $rect = new RectElement();

        $this->assertNull($rect->getY());
    }

    public function testSetAndGetWidth(): void
    {
        $rect = new RectElement();
        $result = $rect->setWidth(100);

        $this->assertSame($rect, $result, 'setWidth should return self for chaining');
        $this->assertSame('100', $rect->getAttribute('width'));

        $width = $rect->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(100.0, $width->getValue());
    }

    public function testSetAndGetWidthWithString(): void
    {
        $rect = new RectElement();
        $rect->setWidth('150em');

        $width = $rect->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(150.0, $width->getValue());
        $this->assertSame('em', $width->getUnit());
    }

    public function testGetWidthReturnsNullWhenNotSet(): void
    {
        $rect = new RectElement();

        $this->assertNull($rect->getWidth());
    }

    public function testSetAndGetHeight(): void
    {
        $rect = new RectElement();
        $result = $rect->setHeight(200);

        $this->assertSame($rect, $result, 'setHeight should return self for chaining');
        $this->assertSame('200', $rect->getAttribute('height'));

        $height = $rect->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(200.0, $height->getValue());
    }

    public function testSetAndGetHeightWithString(): void
    {
        $rect = new RectElement();
        $rect->setHeight('250mm');

        $height = $rect->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(250.0, $height->getValue());
        $this->assertSame('mm', $height->getUnit());
    }

    public function testGetHeightReturnsNullWhenNotSet(): void
    {
        $rect = new RectElement();

        $this->assertNull($rect->getHeight());
    }

    public function testSetAndGetRx(): void
    {
        $rect = new RectElement();
        $result = $rect->setRx(5);

        $this->assertSame($rect, $result, 'setRx should return self for chaining');
        $this->assertSame('5', $rect->getAttribute('rx'));

        $rx = $rect->getRx();
        $this->assertInstanceOf(Length::class, $rx);
        $this->assertSame(5.0, $rx->getValue());
    }

    public function testSetAndGetRxWithString(): void
    {
        $rect = new RectElement();
        $rect->setRx('10px');

        $rx = $rect->getRx();
        $this->assertInstanceOf(Length::class, $rx);
        $this->assertSame(10.0, $rx->getValue());
        $this->assertSame('px', $rx->getUnit());
    }

    public function testGetRxReturnsNullWhenNotSet(): void
    {
        $rect = new RectElement();

        $this->assertNull($rect->getRx());
    }

    public function testSetAndGetRy(): void
    {
        $rect = new RectElement();
        $result = $rect->setRy(5);

        $this->assertSame($rect, $result, 'setRy should return self for chaining');
        $this->assertSame('5', $rect->getAttribute('ry'));

        $ry = $rect->getRy();
        $this->assertInstanceOf(Length::class, $ry);
        $this->assertSame(5.0, $ry->getValue());
    }

    public function testSetAndGetRyWithString(): void
    {
        $rect = new RectElement();
        $rect->setRy('10px');

        $ry = $rect->getRy();
        $this->assertInstanceOf(Length::class, $ry);
        $this->assertSame(10.0, $ry->getValue());
        $this->assertSame('px', $ry->getUnit());
    }

    public function testGetRyReturnsNullWhenNotSet(): void
    {
        $rect = new RectElement();

        $this->assertNull($rect->getRy());
    }

    public function testMethodChaining(): void
    {
        $rect = new RectElement();
        $result = $rect
            ->setX(10)
            ->setY(20)
            ->setWidth(100)
            ->setHeight(200)
            ->setRx(5)
            ->setRy(5);

        $this->assertSame($rect, $result);
        $this->assertSame(10.0, $rect->getX()->getValue());
        $this->assertSame(20.0, $rect->getY()->getValue());
        $this->assertSame(100.0, $rect->getWidth()->getValue());
        $this->assertSame(200.0, $rect->getHeight()->getValue());
        $this->assertSame(5.0, $rect->getRx()->getValue());
        $this->assertSame(5.0, $rect->getRy()->getValue());
    }

    public function testCompleteRectConfiguration(): void
    {
        $rect = new RectElement();
        $rect
            ->setX('10px')
            ->setY('20px')
            ->setWidth('100px')
            ->setHeight('200px');

        $this->assertSame('10px', $rect->getAttribute('x'));
        $this->assertSame('20px', $rect->getAttribute('y'));
        $this->assertSame('100px', $rect->getAttribute('width'));
        $this->assertSame('200px', $rect->getAttribute('height'));

        $x = $rect->getX();
        $y = $rect->getY();
        $width = $rect->getWidth();
        $height = $rect->getHeight();

        $this->assertSame(10.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
        $this->assertSame(20.0, $y->getValue());
        $this->assertSame('px', $y->getUnit());
        $this->assertSame(100.0, $width->getValue());
        $this->assertSame('px', $width->getUnit());
        $this->assertSame(200.0, $height->getValue());
        $this->assertSame('px', $height->getUnit());
    }

    public function testRoundedRectangle(): void
    {
        $rect = new RectElement();
        $rect
            ->setX(0)
            ->setY(0)
            ->setWidth(100)
            ->setHeight(100)
            ->setRx(10)
            ->setRy(10);

        $this->assertSame(10.0, $rect->getRx()->getValue());
        $this->assertSame(10.0, $rect->getRy()->getValue());
    }

    public function testRectWithFloatValues(): void
    {
        $rect = new RectElement();
        $rect
            ->setX(10.5)
            ->setY(20.75)
            ->setWidth(100.25)
            ->setHeight(200.125);

        $this->assertSame(10.5, $rect->getX()->getValue());
        $this->assertSame(20.75, $rect->getY()->getValue());
        $this->assertSame(100.25, $rect->getWidth()->getValue());
        $this->assertSame(200.125, $rect->getHeight()->getValue());
    }

    public function testRectWithDifferentUnits(): void
    {
        $rect = new RectElement();
        $rect
            ->setX('10%')
            ->setY('20em')
            ->setWidth('100px')
            ->setHeight('200mm');

        $x = $rect->getX();
        $y = $rect->getY();
        $width = $rect->getWidth();
        $height = $rect->getHeight();

        $this->assertSame('%', $x->getUnit());
        $this->assertSame('em', $y->getUnit());
        $this->assertSame('px', $width->getUnit());
        $this->assertSame('mm', $height->getUnit());
    }
}
