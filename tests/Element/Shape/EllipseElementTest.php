<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Shape;

use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EllipseElement::class)]
final class EllipseElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $ellipse = new EllipseElement();

        $this->assertSame('ellipse', $ellipse->getTagName());
    }

    public function testSetAndGetCx(): void
    {
        $ellipse = new EllipseElement();
        $result = $ellipse->setCx(100);

        $this->assertSame($ellipse, $result, 'setCx should return self for chaining');
        $this->assertSame('100', $ellipse->getAttribute('cx'));

        $cx = $ellipse->getCx();
        $this->assertInstanceOf(Length::class, $cx);
        $this->assertSame(100.0, $cx->getValue());
    }

    public function testSetAndGetCxWithString(): void
    {
        $ellipse = new EllipseElement();
        $ellipse->setCx('50px');

        $cx = $ellipse->getCx();
        $this->assertInstanceOf(Length::class, $cx);
        $this->assertSame(50.0, $cx->getValue());
        $this->assertSame('px', $cx->getUnit());
    }

    public function testGetCxReturnsNullWhenNotSet(): void
    {
        $ellipse = new EllipseElement();

        $this->assertNull($ellipse->getCx());
    }

    public function testSetAndGetCy(): void
    {
        $ellipse = new EllipseElement();
        $result = $ellipse->setCy(200);

        $this->assertSame($ellipse, $result, 'setCy should return self for chaining');
        $this->assertSame('200', $ellipse->getAttribute('cy'));

        $cy = $ellipse->getCy();
        $this->assertInstanceOf(Length::class, $cy);
        $this->assertSame(200.0, $cy->getValue());
    }

    public function testSetAndGetCyWithString(): void
    {
        $ellipse = new EllipseElement();
        $ellipse->setCy('25%');

        $cy = $ellipse->getCy();
        $this->assertInstanceOf(Length::class, $cy);
        $this->assertSame(25.0, $cy->getValue());
        $this->assertSame('%', $cy->getUnit());
    }

    public function testGetCyReturnsNullWhenNotSet(): void
    {
        $ellipse = new EllipseElement();

        $this->assertNull($ellipse->getCy());
    }

    public function testSetAndGetRx(): void
    {
        $ellipse = new EllipseElement();
        $result = $ellipse->setRx(75);

        $this->assertSame($ellipse, $result, 'setRx should return self for chaining');
        $this->assertSame('75', $ellipse->getAttribute('rx'));

        $rx = $ellipse->getRx();
        $this->assertInstanceOf(Length::class, $rx);
        $this->assertSame(75.0, $rx->getValue());
    }

    public function testSetAndGetRxWithString(): void
    {
        $ellipse = new EllipseElement();
        $ellipse->setRx('40em');

        $rx = $ellipse->getRx();
        $this->assertInstanceOf(Length::class, $rx);
        $this->assertSame(40.0, $rx->getValue());
        $this->assertSame('em', $rx->getUnit());
    }

    public function testGetRxReturnsNullWhenNotSet(): void
    {
        $ellipse = new EllipseElement();

        $this->assertNull($ellipse->getRx());
    }

    public function testSetAndGetRy(): void
    {
        $ellipse = new EllipseElement();
        $result = $ellipse->setRy(50);

        $this->assertSame($ellipse, $result, 'setRy should return self for chaining');
        $this->assertSame('50', $ellipse->getAttribute('ry'));

        $ry = $ellipse->getRy();
        $this->assertInstanceOf(Length::class, $ry);
        $this->assertSame(50.0, $ry->getValue());
    }

    public function testSetAndGetRyWithString(): void
    {
        $ellipse = new EllipseElement();
        $ellipse->setRy('30mm');

        $ry = $ellipse->getRy();
        $this->assertInstanceOf(Length::class, $ry);
        $this->assertSame(30.0, $ry->getValue());
        $this->assertSame('mm', $ry->getUnit());
    }

    public function testGetRyReturnsNullWhenNotSet(): void
    {
        $ellipse = new EllipseElement();

        $this->assertNull($ellipse->getRy());
    }

    public function testMethodChaining(): void
    {
        $ellipse = new EllipseElement();
        $result = $ellipse
            ->setCx(100)
            ->setCy(200)
            ->setRx(75)
            ->setRy(50);

        $this->assertSame($ellipse, $result);
        $this->assertSame(100.0, $ellipse->getCx()->getValue());
        $this->assertSame(200.0, $ellipse->getCy()->getValue());
        $this->assertSame(75.0, $ellipse->getRx()->getValue());
        $this->assertSame(50.0, $ellipse->getRy()->getValue());
    }

    public function testCompleteEllipseConfiguration(): void
    {
        $ellipse = new EllipseElement();
        $ellipse
            ->setCx('200px')
            ->setCy('150px')
            ->setRx('100px')
            ->setRy('75px');

        $this->assertSame('200px', $ellipse->getAttribute('cx'));
        $this->assertSame('150px', $ellipse->getAttribute('cy'));
        $this->assertSame('100px', $ellipse->getAttribute('rx'));
        $this->assertSame('75px', $ellipse->getAttribute('ry'));

        $cx = $ellipse->getCx();
        $cy = $ellipse->getCy();
        $rx = $ellipse->getRx();
        $ry = $ellipse->getRy();

        $this->assertSame(200.0, $cx->getValue());
        $this->assertSame('px', $cx->getUnit());
        $this->assertSame(150.0, $cy->getValue());
        $this->assertSame('px', $cy->getUnit());
        $this->assertSame(100.0, $rx->getValue());
        $this->assertSame('px', $rx->getUnit());
        $this->assertSame(75.0, $ry->getValue());
        $this->assertSame('px', $ry->getUnit());
    }

    public function testEllipseWithDifferentUnits(): void
    {
        $ellipse = new EllipseElement();
        $ellipse
            ->setCx('50%')
            ->setCy('50%')
            ->setRx('10em')
            ->setRy('8em');

        $cx = $ellipse->getCx();
        $cy = $ellipse->getCy();
        $rx = $ellipse->getRx();
        $ry = $ellipse->getRy();

        $this->assertSame('%', $cx->getUnit());
        $this->assertSame('%', $cy->getUnit());
        $this->assertSame('em', $rx->getUnit());
        $this->assertSame('em', $ry->getUnit());
    }
}
