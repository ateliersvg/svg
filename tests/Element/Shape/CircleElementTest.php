<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Shape;

use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CircleElement::class)]
final class CircleElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $circle = new CircleElement();

        $this->assertSame('circle', $circle->getTagName());
    }

    public function testSetAndGetCx(): void
    {
        $circle = new CircleElement();
        $result = $circle->setCx(100);

        $this->assertSame($circle, $result, 'setCx should return self for chaining');
        $this->assertSame('100', $circle->getAttribute('cx'));

        $cx = $circle->getCx();
        $this->assertInstanceOf(Length::class, $cx);
        $this->assertSame(100.0, $cx->getValue());
    }

    public function testSetAndGetCxWithString(): void
    {
        $circle = new CircleElement();
        $circle->setCx('50px');

        $cx = $circle->getCx();
        $this->assertInstanceOf(Length::class, $cx);
        $this->assertSame(50.0, $cx->getValue());
        $this->assertSame('px', $cx->getUnit());
    }

    public function testSetAndGetCxWithFloat(): void
    {
        $circle = new CircleElement();
        $circle->setCx(75.5);

        $cx = $circle->getCx();
        $this->assertInstanceOf(Length::class, $cx);
        $this->assertSame(75.5, $cx->getValue());
    }

    public function testGetCxReturnsNullWhenNotSet(): void
    {
        $circle = new CircleElement();

        $this->assertNull($circle->getCx());
    }

    public function testSetAndGetCy(): void
    {
        $circle = new CircleElement();
        $result = $circle->setCy(200);

        $this->assertSame($circle, $result, 'setCy should return self for chaining');
        $this->assertSame('200', $circle->getAttribute('cy'));

        $cy = $circle->getCy();
        $this->assertInstanceOf(Length::class, $cy);
        $this->assertSame(200.0, $cy->getValue());
    }

    public function testSetAndGetCyWithString(): void
    {
        $circle = new CircleElement();
        $circle->setCy('25%');

        $cy = $circle->getCy();
        $this->assertInstanceOf(Length::class, $cy);
        $this->assertSame(25.0, $cy->getValue());
        $this->assertSame('%', $cy->getUnit());
    }

    public function testGetCyReturnsNullWhenNotSet(): void
    {
        $circle = new CircleElement();

        $this->assertNull($circle->getCy());
    }

    public function testSetAndGetRadius(): void
    {
        $circle = new CircleElement();
        $result = $circle->setRadius(50);

        $this->assertSame($circle, $result, 'setRadius should return self for chaining');
        $this->assertSame('50', $circle->getAttribute('r'));

        $r = $circle->getRadius();
        $this->assertInstanceOf(Length::class, $r);
        $this->assertSame(50.0, $r->getValue());
    }

    public function testSetAndGetRadiusWithString(): void
    {
        $circle = new CircleElement();
        $circle->setRadius('30em');

        $r = $circle->getRadius();
        $this->assertInstanceOf(Length::class, $r);
        $this->assertSame(30.0, $r->getValue());
        $this->assertSame('em', $r->getUnit());
    }

    public function testGetRadiusReturnsNullWhenNotSet(): void
    {
        $circle = new CircleElement();

        $this->assertNull($circle->getRadius());
    }

    public function testSetAndGetR(): void
    {
        $circle = new CircleElement();
        $result = $circle->setR(60);

        $this->assertSame($circle, $result, 'setR should return self for chaining');
        $this->assertSame('60', $circle->getAttribute('r'));

        $r = $circle->getR();
        $this->assertInstanceOf(Length::class, $r);
        $this->assertSame(60.0, $r->getValue());
    }

    public function testGetRReturnsNullWhenNotSet(): void
    {
        $circle = new CircleElement();

        $this->assertNull($circle->getR());
    }

    public function testRAndRadiusMethodsAreAliases(): void
    {
        $circle = new CircleElement();
        $circle->setR(40);

        $this->assertSame(40.0, $circle->getRadius()->getValue());
        $this->assertSame(40.0, $circle->getR()->getValue());

        $circle->setRadius(80);

        $this->assertSame(80.0, $circle->getRadius()->getValue());
        $this->assertSame(80.0, $circle->getR()->getValue());
    }

    public function testMethodChaining(): void
    {
        $circle = new CircleElement();
        $result = $circle
            ->setCx(100)
            ->setCy(200)
            ->setRadius(50);

        $this->assertSame($circle, $result);
        $this->assertSame(100.0, $circle->getCx()->getValue());
        $this->assertSame(200.0, $circle->getCy()->getValue());
        $this->assertSame(50.0, $circle->getRadius()->getValue());
    }

    public function testCompleteCircleConfiguration(): void
    {
        $circle = new CircleElement();
        $circle
            ->setCx('150px')
            ->setCy('100px')
            ->setR('75px');

        $this->assertSame('150px', $circle->getAttribute('cx'));
        $this->assertSame('100px', $circle->getAttribute('cy'));
        $this->assertSame('75px', $circle->getAttribute('r'));

        $cx = $circle->getCx();
        $cy = $circle->getCy();
        $r = $circle->getR();

        $this->assertSame(150.0, $cx->getValue());
        $this->assertSame('px', $cx->getUnit());
        $this->assertSame(100.0, $cy->getValue());
        $this->assertSame('px', $cy->getUnit());
        $this->assertSame(75.0, $r->getValue());
        $this->assertSame('px', $r->getUnit());
    }
}
