<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Structural;

use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Structural\ForeignObjectElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ForeignObjectElement::class)]
final class ForeignObjectElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $foreignObject = new ForeignObjectElement();

        $this->assertSame('foreignObject', $foreignObject->getTagName());
    }

    public function testIsContainerElement(): void
    {
        $foreignObject = new ForeignObjectElement();
        $circle = new CircleElement();

        $foreignObject->appendChild($circle);

        $this->assertTrue($foreignObject->hasChildren());
        $this->assertCount(1, $foreignObject->getChildren());
        $this->assertSame($circle, $foreignObject->getChildren()[0]);
    }

    public function testSetAndGetX(): void
    {
        $foreignObject = new ForeignObjectElement();
        $result = $foreignObject->setX(10);

        $this->assertSame($foreignObject, $result, 'setX should return self for chaining');
        $this->assertSame('10', $foreignObject->getAttribute('x'));

        $x = $foreignObject->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(10.0, $x->getValue());
    }

    public function testSetAndGetXWithString(): void
    {
        $foreignObject = new ForeignObjectElement();
        $foreignObject->setX('20px');

        $x = $foreignObject->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(20.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
    }

    public function testGetXReturnsNullWhenNotSet(): void
    {
        $foreignObject = new ForeignObjectElement();

        $this->assertNull($foreignObject->getX());
    }

    public function testSetAndGetY(): void
    {
        $foreignObject = new ForeignObjectElement();
        $result = $foreignObject->setY(20);

        $this->assertSame($foreignObject, $result, 'setY should return self for chaining');
        $this->assertSame('20', $foreignObject->getAttribute('y'));

        $y = $foreignObject->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(20.0, $y->getValue());
    }

    public function testSetAndGetYWithString(): void
    {
        $foreignObject = new ForeignObjectElement();
        $foreignObject->setY('30%');

        $y = $foreignObject->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(30.0, $y->getValue());
        $this->assertSame('%', $y->getUnit());
    }

    public function testGetYReturnsNullWhenNotSet(): void
    {
        $foreignObject = new ForeignObjectElement();

        $this->assertNull($foreignObject->getY());
    }

    public function testSetAndGetWidth(): void
    {
        $foreignObject = new ForeignObjectElement();
        $result = $foreignObject->setWidth(100);

        $this->assertSame($foreignObject, $result, 'setWidth should return self for chaining');
        $this->assertSame('100', $foreignObject->getAttribute('width'));

        $width = $foreignObject->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(100.0, $width->getValue());
    }

    public function testSetAndGetWidthWithString(): void
    {
        $foreignObject = new ForeignObjectElement();
        $foreignObject->setWidth('150em');

        $width = $foreignObject->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(150.0, $width->getValue());
        $this->assertSame('em', $width->getUnit());
    }

    public function testGetWidthReturnsNullWhenNotSet(): void
    {
        $foreignObject = new ForeignObjectElement();

        $this->assertNull($foreignObject->getWidth());
    }

    public function testSetAndGetHeight(): void
    {
        $foreignObject = new ForeignObjectElement();
        $result = $foreignObject->setHeight(200);

        $this->assertSame($foreignObject, $result, 'setHeight should return self for chaining');
        $this->assertSame('200', $foreignObject->getAttribute('height'));

        $height = $foreignObject->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(200.0, $height->getValue());
    }

    public function testSetAndGetHeightWithString(): void
    {
        $foreignObject = new ForeignObjectElement();
        $foreignObject->setHeight('250mm');

        $height = $foreignObject->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(250.0, $height->getValue());
        $this->assertSame('mm', $height->getUnit());
    }

    public function testGetHeightReturnsNullWhenNotSet(): void
    {
        $foreignObject = new ForeignObjectElement();

        $this->assertNull($foreignObject->getHeight());
    }

    public function testMethodChaining(): void
    {
        $foreignObject = new ForeignObjectElement();
        $result = $foreignObject
            ->setX(10)
            ->setY(20)
            ->setWidth(100)
            ->setHeight(200);

        $this->assertSame($foreignObject, $result);
        $this->assertSame(10.0, $foreignObject->getX()->getValue());
        $this->assertSame(20.0, $foreignObject->getY()->getValue());
        $this->assertSame(100.0, $foreignObject->getWidth()->getValue());
        $this->assertSame(200.0, $foreignObject->getHeight()->getValue());
    }

    public function testCompleteForeignObjectConfiguration(): void
    {
        $foreignObject = new ForeignObjectElement();
        $foreignObject
            ->setX('10px')
            ->setY('20px')
            ->setWidth('100px')
            ->setHeight('200px');

        $this->assertSame('10px', $foreignObject->getAttribute('x'));
        $this->assertSame('20px', $foreignObject->getAttribute('y'));
        $this->assertSame('100px', $foreignObject->getAttribute('width'));
        $this->assertSame('200px', $foreignObject->getAttribute('height'));
    }

    public function testForeignObjectWithFloatValues(): void
    {
        $foreignObject = new ForeignObjectElement();
        $foreignObject
            ->setX(10.5)
            ->setY(20.75)
            ->setWidth(100.25)
            ->setHeight(200.125);

        $this->assertSame(10.5, $foreignObject->getX()->getValue());
        $this->assertSame(20.75, $foreignObject->getY()->getValue());
        $this->assertSame(100.25, $foreignObject->getWidth()->getValue());
        $this->assertSame(200.125, $foreignObject->getHeight()->getValue());
    }

    public function testForeignObjectWithDifferentUnits(): void
    {
        $foreignObject = new ForeignObjectElement();
        $foreignObject
            ->setX('10%')
            ->setY('20em')
            ->setWidth('100px')
            ->setHeight('200mm');

        $x = $foreignObject->getX();
        $y = $foreignObject->getY();
        $width = $foreignObject->getWidth();
        $height = $foreignObject->getHeight();

        $this->assertSame('%', $x->getUnit());
        $this->assertSame('em', $y->getUnit());
        $this->assertSame('px', $width->getUnit());
        $this->assertSame('mm', $height->getUnit());
    }
}
