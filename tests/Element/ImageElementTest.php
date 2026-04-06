<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\PreserveAspectRatio;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageElement::class)]
final class ImageElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $image = new ImageElement();

        $this->assertSame('image', $image->getTagName());
    }

    public function testSetAndGetHref(): void
    {
        $image = new ImageElement();
        $result = $image->setHref('photo.jpg');

        $this->assertSame($image, $result, 'setHref should return self for chaining');
        $this->assertSame('photo.jpg', $image->getHref());
        $this->assertSame('photo.jpg', $image->getAttribute('href'));
    }

    public function testGetHrefReturnsNullWhenNotSet(): void
    {
        $image = new ImageElement();

        $this->assertNull($image->getHref());
    }

    public function testGetHrefSupportsXlinkHref(): void
    {
        $image = new ImageElement();
        $image->setAttribute('xlink:href', 'legacy-photo.jpg');

        $this->assertSame('legacy-photo.jpg', $image->getHref());
    }

    public function testGetHrefPrefersHrefOverXlinkHref(): void
    {
        $image = new ImageElement();
        $image->setAttribute('xlink:href', 'legacy-photo.jpg');
        $image->setHref('modern-photo.jpg');

        $this->assertSame('modern-photo.jpg', $image->getHref());
    }

    public function testSetAndGetX(): void
    {
        $image = new ImageElement();
        $result = $image->setX(10);

        $this->assertSame($image, $result, 'setX should return self for chaining');
        $this->assertSame('10', $image->getAttribute('x'));

        $x = $image->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(10.0, $x->getValue());
    }

    public function testSetAndGetXWithString(): void
    {
        $image = new ImageElement();
        $image->setX('20px');

        $x = $image->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(20.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
    }

    public function testGetXReturnsNullWhenNotSet(): void
    {
        $image = new ImageElement();

        $this->assertNull($image->getX());
    }

    public function testSetAndGetY(): void
    {
        $image = new ImageElement();
        $result = $image->setY(20);

        $this->assertSame($image, $result, 'setY should return self for chaining');
        $this->assertSame('20', $image->getAttribute('y'));

        $y = $image->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(20.0, $y->getValue());
    }

    public function testSetAndGetYWithString(): void
    {
        $image = new ImageElement();
        $image->setY('30%');

        $y = $image->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(30.0, $y->getValue());
        $this->assertSame('%', $y->getUnit());
    }

    public function testGetYReturnsNullWhenNotSet(): void
    {
        $image = new ImageElement();

        $this->assertNull($image->getY());
    }

    public function testSetAndGetWidth(): void
    {
        $image = new ImageElement();
        $result = $image->setWidth(100);

        $this->assertSame($image, $result, 'setWidth should return self for chaining');
        $this->assertSame('100', $image->getAttribute('width'));

        $width = $image->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(100.0, $width->getValue());
    }

    public function testSetAndGetWidthWithString(): void
    {
        $image = new ImageElement();
        $image->setWidth('150em');

        $width = $image->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(150.0, $width->getValue());
        $this->assertSame('em', $width->getUnit());
    }

    public function testGetWidthReturnsNullWhenNotSet(): void
    {
        $image = new ImageElement();

        $this->assertNull($image->getWidth());
    }

    public function testSetAndGetHeight(): void
    {
        $image = new ImageElement();
        $result = $image->setHeight(200);

        $this->assertSame($image, $result, 'setHeight should return self for chaining');
        $this->assertSame('200', $image->getAttribute('height'));

        $height = $image->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(200.0, $height->getValue());
    }

    public function testSetAndGetHeightWithString(): void
    {
        $image = new ImageElement();
        $image->setHeight('250mm');

        $height = $image->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(250.0, $height->getValue());
        $this->assertSame('mm', $height->getUnit());
    }

    public function testGetHeightReturnsNullWhenNotSet(): void
    {
        $image = new ImageElement();

        $this->assertNull($image->getHeight());
    }

    public function testSetAndGetPreserveAspectRatioWithString(): void
    {
        $image = new ImageElement();
        $result = $image->setPreserveAspectRatio('xMidYMid meet');

        $this->assertSame($image, $result, 'setPreserveAspectRatio should return self for chaining');

        $par = $image->getPreserveAspectRatio();
        $this->assertInstanceOf(PreserveAspectRatio::class, $par);
        $this->assertSame('xMidYMid', $par->getAlign());
        $this->assertSame('meet', $par->getMeetOrSlice());
    }

    public function testSetAndGetPreserveAspectRatioWithObject(): void
    {
        $image = new ImageElement();
        $par = PreserveAspectRatio::fromAlignment('xMax', 'YMax', 'slice');
        $image->setPreserveAspectRatio($par);

        $retrieved = $image->getPreserveAspectRatio();
        $this->assertInstanceOf(PreserveAspectRatio::class, $retrieved);
        $this->assertSame('xMaxYMax', $retrieved->getAlign());
        $this->assertSame('slice', $retrieved->getMeetOrSlice());
    }

    public function testGetPreserveAspectRatioReturnsNullWhenNotSet(): void
    {
        $image = new ImageElement();

        $this->assertNull($image->getPreserveAspectRatio());
    }

    public function testMethodChaining(): void
    {
        $image = new ImageElement();
        $result = $image
            ->setHref('photo.jpg')
            ->setX(10)
            ->setY(20)
            ->setWidth(100)
            ->setHeight(200)
            ->setPreserveAspectRatio('xMidYMid meet');

        $this->assertSame($image, $result);
        $this->assertSame('photo.jpg', $image->getHref());
        $this->assertSame(10.0, $image->getX()->getValue());
        $this->assertSame(20.0, $image->getY()->getValue());
        $this->assertSame(100.0, $image->getWidth()->getValue());
        $this->assertSame(200.0, $image->getHeight()->getValue());
        $this->assertInstanceOf(PreserveAspectRatio::class, $image->getPreserveAspectRatio());
    }

    public function testCompleteImageConfiguration(): void
    {
        $image = new ImageElement();
        $image
            ->setHref('image.png')
            ->setX('10px')
            ->setY('20px')
            ->setWidth('100px')
            ->setHeight('200px')
            ->setPreserveAspectRatio('xMinYMin slice');

        $this->assertSame('image.png', $image->getAttribute('href'));
        $this->assertSame('10px', $image->getAttribute('x'));
        $this->assertSame('20px', $image->getAttribute('y'));
        $this->assertSame('100px', $image->getAttribute('width'));
        $this->assertSame('200px', $image->getAttribute('height'));
        $this->assertSame('xMinYMin slice', $image->getAttribute('preserveAspectRatio'));
    }

    public function testImageWithFloatValues(): void
    {
        $image = new ImageElement();
        $image
            ->setX(10.5)
            ->setY(20.75)
            ->setWidth(100.25)
            ->setHeight(200.125);

        $this->assertSame(10.5, $image->getX()->getValue());
        $this->assertSame(20.75, $image->getY()->getValue());
        $this->assertSame(100.25, $image->getWidth()->getValue());
        $this->assertSame(200.125, $image->getHeight()->getValue());
    }

    public function testImageWithDifferentUnits(): void
    {
        $image = new ImageElement();
        $image
            ->setX('10%')
            ->setY('20em')
            ->setWidth('100px')
            ->setHeight('200mm');

        $x = $image->getX();
        $y = $image->getY();
        $width = $image->getWidth();
        $height = $image->getHeight();

        $this->assertSame('%', $x->getUnit());
        $this->assertSame('em', $y->getUnit());
        $this->assertSame('px', $width->getUnit());
        $this->assertSame('mm', $height->getUnit());
    }
}
