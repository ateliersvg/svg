<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\Structural\UseElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UseElement::class)]
final class UseElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $use = new UseElement();

        $this->assertSame('use', $use->getTagName());
    }

    public function testSetAndGetHref(): void
    {
        $use = new UseElement();
        $result = $use->setHref('#mySymbol');

        $this->assertSame($use, $result, 'setHref should return self for chaining');
        $this->assertSame('#mySymbol', $use->getHref());
        $this->assertSame('#mySymbol', $use->getAttribute('href'));
    }

    public function testGetHrefReturnsNullWhenNotSet(): void
    {
        $use = new UseElement();

        $this->assertNull($use->getHref());
    }

    public function testGetHrefSupportsXlinkHref(): void
    {
        $use = new UseElement();
        $use->setAttribute('xlink:href', '#legacySymbol');

        $this->assertSame('#legacySymbol', $use->getHref());
    }

    public function testGetHrefPrefersHrefOverXlinkHref(): void
    {
        $use = new UseElement();
        $use->setAttribute('xlink:href', '#legacySymbol');
        $use->setHref('#modernSymbol');

        $this->assertSame('#modernSymbol', $use->getHref());
    }

    public function testSetAndGetX(): void
    {
        $use = new UseElement();
        $result = $use->setX(10);

        $this->assertSame($use, $result, 'setX should return self for chaining');
        $this->assertSame('10', $use->getAttribute('x'));

        $x = $use->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(10.0, $x->getValue());
    }

    public function testSetAndGetXWithString(): void
    {
        $use = new UseElement();
        $use->setX('20px');

        $x = $use->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(20.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
    }

    public function testGetXReturnsNullWhenNotSet(): void
    {
        $use = new UseElement();

        $this->assertNull($use->getX());
    }

    public function testSetAndGetY(): void
    {
        $use = new UseElement();
        $result = $use->setY(20);

        $this->assertSame($use, $result, 'setY should return self for chaining');
        $this->assertSame('20', $use->getAttribute('y'));

        $y = $use->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(20.0, $y->getValue());
    }

    public function testSetAndGetYWithString(): void
    {
        $use = new UseElement();
        $use->setY('30%');

        $y = $use->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(30.0, $y->getValue());
        $this->assertSame('%', $y->getUnit());
    }

    public function testGetYReturnsNullWhenNotSet(): void
    {
        $use = new UseElement();

        $this->assertNull($use->getY());
    }

    public function testSetAndGetWidth(): void
    {
        $use = new UseElement();
        $result = $use->setWidth(100);

        $this->assertSame($use, $result, 'setWidth should return self for chaining');
        $this->assertSame('100', $use->getAttribute('width'));

        $width = $use->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(100.0, $width->getValue());
    }

    public function testSetAndGetWidthWithString(): void
    {
        $use = new UseElement();
        $use->setWidth('150em');

        $width = $use->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(150.0, $width->getValue());
        $this->assertSame('em', $width->getUnit());
    }

    public function testGetWidthReturnsNullWhenNotSet(): void
    {
        $use = new UseElement();

        $this->assertNull($use->getWidth());
    }

    public function testSetAndGetHeight(): void
    {
        $use = new UseElement();
        $result = $use->setHeight(200);

        $this->assertSame($use, $result, 'setHeight should return self for chaining');
        $this->assertSame('200', $use->getAttribute('height'));

        $height = $use->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(200.0, $height->getValue());
    }

    public function testSetAndGetHeightWithString(): void
    {
        $use = new UseElement();
        $use->setHeight('250mm');

        $height = $use->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(250.0, $height->getValue());
        $this->assertSame('mm', $height->getUnit());
    }

    public function testGetHeightReturnsNullWhenNotSet(): void
    {
        $use = new UseElement();

        $this->assertNull($use->getHeight());
    }

    public function testMethodChaining(): void
    {
        $use = new UseElement();
        $result = $use
            ->setHref('#icon')
            ->setX(10)
            ->setY(20)
            ->setWidth(100)
            ->setHeight(200);

        $this->assertSame($use, $result);
        $this->assertSame('#icon', $use->getHref());
        $this->assertSame(10.0, $use->getX()->getValue());
        $this->assertSame(20.0, $use->getY()->getValue());
        $this->assertSame(100.0, $use->getWidth()->getValue());
        $this->assertSame(200.0, $use->getHeight()->getValue());
    }

    public function testCompleteUseConfiguration(): void
    {
        $use = new UseElement();
        $use
            ->setHref('#myElement')
            ->setX('10px')
            ->setY('20px')
            ->setWidth('100px')
            ->setHeight('200px');

        $this->assertSame('#myElement', $use->getAttribute('href'));
        $this->assertSame('10px', $use->getAttribute('x'));
        $this->assertSame('20px', $use->getAttribute('y'));
        $this->assertSame('100px', $use->getAttribute('width'));
        $this->assertSame('200px', $use->getAttribute('height'));
    }

    public function testUseWithFloatValues(): void
    {
        $use = new UseElement();
        $use
            ->setX(10.5)
            ->setY(20.75)
            ->setWidth(100.25)
            ->setHeight(200.125);

        $this->assertSame(10.5, $use->getX()->getValue());
        $this->assertSame(20.75, $use->getY()->getValue());
        $this->assertSame(100.25, $use->getWidth()->getValue());
        $this->assertSame(200.125, $use->getHeight()->getValue());
    }

    public function testUseWithDifferentUnits(): void
    {
        $use = new UseElement();
        $use
            ->setX('10%')
            ->setY('20em')
            ->setWidth('100px')
            ->setHeight('200mm');

        $x = $use->getX();
        $y = $use->getY();
        $width = $use->getWidth();
        $height = $use->getHeight();

        $this->assertSame('%', $x->getUnit());
        $this->assertSame('em', $y->getUnit());
        $this->assertSame('px', $width->getUnit());
        $this->assertSame('mm', $height->getUnit());
    }
}
