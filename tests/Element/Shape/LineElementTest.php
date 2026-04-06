<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Shape;

use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LineElement::class)]
final class LineElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $line = new LineElement();

        $this->assertSame('line', $line->getTagName());
    }

    public function testSetAndGetX1(): void
    {
        $line = new LineElement();
        $result = $line->setX1(10);

        $this->assertSame($line, $result, 'setX1 should return self for chaining');
        $this->assertSame('10', $line->getAttribute('x1'));

        $x1 = $line->getX1();
        $this->assertInstanceOf(Length::class, $x1);
        $this->assertSame(10.0, $x1->getValue());
    }

    public function testSetAndGetX1WithString(): void
    {
        $line = new LineElement();
        $line->setX1('20px');

        $x1 = $line->getX1();
        $this->assertInstanceOf(Length::class, $x1);
        $this->assertSame(20.0, $x1->getValue());
        $this->assertSame('px', $x1->getUnit());
    }

    public function testGetX1ReturnsNullWhenNotSet(): void
    {
        $line = new LineElement();

        $this->assertNull($line->getX1());
    }

    public function testSetAndGetY1(): void
    {
        $line = new LineElement();
        $result = $line->setY1(20);

        $this->assertSame($line, $result, 'setY1 should return self for chaining');
        $this->assertSame('20', $line->getAttribute('y1'));

        $y1 = $line->getY1();
        $this->assertInstanceOf(Length::class, $y1);
        $this->assertSame(20.0, $y1->getValue());
    }

    public function testSetAndGetY1WithString(): void
    {
        $line = new LineElement();
        $line->setY1('30%');

        $y1 = $line->getY1();
        $this->assertInstanceOf(Length::class, $y1);
        $this->assertSame(30.0, $y1->getValue());
        $this->assertSame('%', $y1->getUnit());
    }

    public function testGetY1ReturnsNullWhenNotSet(): void
    {
        $line = new LineElement();

        $this->assertNull($line->getY1());
    }

    public function testSetAndGetX2(): void
    {
        $line = new LineElement();
        $result = $line->setX2(100);

        $this->assertSame($line, $result, 'setX2 should return self for chaining');
        $this->assertSame('100', $line->getAttribute('x2'));

        $x2 = $line->getX2();
        $this->assertInstanceOf(Length::class, $x2);
        $this->assertSame(100.0, $x2->getValue());
    }

    public function testSetAndGetX2WithString(): void
    {
        $line = new LineElement();
        $line->setX2('150em');

        $x2 = $line->getX2();
        $this->assertInstanceOf(Length::class, $x2);
        $this->assertSame(150.0, $x2->getValue());
        $this->assertSame('em', $x2->getUnit());
    }

    public function testGetX2ReturnsNullWhenNotSet(): void
    {
        $line = new LineElement();

        $this->assertNull($line->getX2());
    }

    public function testSetAndGetY2(): void
    {
        $line = new LineElement();
        $result = $line->setY2(200);

        $this->assertSame($line, $result, 'setY2 should return self for chaining');
        $this->assertSame('200', $line->getAttribute('y2'));

        $y2 = $line->getY2();
        $this->assertInstanceOf(Length::class, $y2);
        $this->assertSame(200.0, $y2->getValue());
    }

    public function testSetAndGetY2WithString(): void
    {
        $line = new LineElement();
        $line->setY2('250mm');

        $y2 = $line->getY2();
        $this->assertInstanceOf(Length::class, $y2);
        $this->assertSame(250.0, $y2->getValue());
        $this->assertSame('mm', $y2->getUnit());
    }

    public function testGetY2ReturnsNullWhenNotSet(): void
    {
        $line = new LineElement();

        $this->assertNull($line->getY2());
    }

    public function testMethodChaining(): void
    {
        $line = new LineElement();
        $result = $line
            ->setX1(10)
            ->setY1(20)
            ->setX2(100)
            ->setY2(200);

        $this->assertSame($line, $result);
        $this->assertSame(10.0, $line->getX1()->getValue());
        $this->assertSame(20.0, $line->getY1()->getValue());
        $this->assertSame(100.0, $line->getX2()->getValue());
        $this->assertSame(200.0, $line->getY2()->getValue());
    }

    public function testCompleteLineConfiguration(): void
    {
        $line = new LineElement();
        $line
            ->setX1('0px')
            ->setY1('0px')
            ->setX2('100px')
            ->setY2('100px');

        $this->assertSame('0px', $line->getAttribute('x1'));
        $this->assertSame('0px', $line->getAttribute('y1'));
        $this->assertSame('100px', $line->getAttribute('x2'));
        $this->assertSame('100px', $line->getAttribute('y2'));

        $x1 = $line->getX1();
        $y1 = $line->getY1();
        $x2 = $line->getX2();
        $y2 = $line->getY2();

        $this->assertSame(0.0, $x1->getValue());
        $this->assertSame('px', $x1->getUnit());
        $this->assertSame(0.0, $y1->getValue());
        $this->assertSame('px', $y1->getUnit());
        $this->assertSame(100.0, $x2->getValue());
        $this->assertSame('px', $x2->getUnit());
        $this->assertSame(100.0, $y2->getValue());
        $this->assertSame('px', $y2->getUnit());
    }

    public function testLineWithFloatValues(): void
    {
        $line = new LineElement();
        $line
            ->setX1(10.5)
            ->setY1(20.75)
            ->setX2(100.25)
            ->setY2(200.125);

        $this->assertSame(10.5, $line->getX1()->getValue());
        $this->assertSame(20.75, $line->getY1()->getValue());
        $this->assertSame(100.25, $line->getX2()->getValue());
        $this->assertSame(200.125, $line->getY2()->getValue());
    }

    public function testLineWithDifferentUnits(): void
    {
        $line = new LineElement();
        $line
            ->setX1('10%')
            ->setY1('20em')
            ->setX2('100px')
            ->setY2('200mm');

        $x1 = $line->getX1();
        $y1 = $line->getY1();
        $x2 = $line->getX2();
        $y2 = $line->getY2();

        $this->assertSame('%', $x1->getUnit());
        $this->assertSame('em', $y1->getUnit());
        $this->assertSame('px', $x2->getUnit());
        $this->assertSame('mm', $y2->getUnit());
    }
}
