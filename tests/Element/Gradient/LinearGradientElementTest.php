<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Gradient;

use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\StopElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LinearGradientElement::class)]
final class LinearGradientElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $gradient = new LinearGradientElement();

        $this->assertSame('linearGradient', $gradient->getTagName());
    }

    public function testSetAndGetX1(): void
    {
        $gradient = new LinearGradientElement();
        $result = $gradient->setX1(0);

        $this->assertSame($gradient, $result, 'setX1 should return self for chaining');
        $this->assertSame('0', $gradient->getAttribute('x1'));

        $x1 = $gradient->getX1();
        $this->assertInstanceOf(Length::class, $x1);
        $this->assertSame(0.0, $x1->getValue());
    }

    public function testSetAndGetX1WithString(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setX1('10%');

        $x1 = $gradient->getX1();
        $this->assertInstanceOf(Length::class, $x1);
        $this->assertSame(10.0, $x1->getValue());
        $this->assertSame('%', $x1->getUnit());
    }

    public function testGetX1ReturnsNullWhenNotSet(): void
    {
        $gradient = new LinearGradientElement();

        $this->assertNull($gradient->getX1());
    }

    public function testSetAndGetY1(): void
    {
        $gradient = new LinearGradientElement();
        $result = $gradient->setY1(0);

        $this->assertSame($gradient, $result, 'setY1 should return self for chaining');
        $this->assertSame('0', $gradient->getAttribute('y1'));

        $y1 = $gradient->getY1();
        $this->assertInstanceOf(Length::class, $y1);
        $this->assertSame(0.0, $y1->getValue());
    }

    public function testSetAndGetY1WithString(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setY1('20%');

        $y1 = $gradient->getY1();
        $this->assertInstanceOf(Length::class, $y1);
        $this->assertSame(20.0, $y1->getValue());
        $this->assertSame('%', $y1->getUnit());
    }

    public function testGetY1ReturnsNullWhenNotSet(): void
    {
        $gradient = new LinearGradientElement();

        $this->assertNull($gradient->getY1());
    }

    public function testSetAndGetX2(): void
    {
        $gradient = new LinearGradientElement();
        $result = $gradient->setX2(100);

        $this->assertSame($gradient, $result, 'setX2 should return self for chaining');
        $this->assertSame('100', $gradient->getAttribute('x2'));

        $x2 = $gradient->getX2();
        $this->assertInstanceOf(Length::class, $x2);
        $this->assertSame(100.0, $x2->getValue());
    }

    public function testSetAndGetX2WithString(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setX2('100%');

        $x2 = $gradient->getX2();
        $this->assertInstanceOf(Length::class, $x2);
        $this->assertSame(100.0, $x2->getValue());
        $this->assertSame('%', $x2->getUnit());
    }

    public function testGetX2ReturnsNullWhenNotSet(): void
    {
        $gradient = new LinearGradientElement();

        $this->assertNull($gradient->getX2());
    }

    public function testSetAndGetY2(): void
    {
        $gradient = new LinearGradientElement();
        $result = $gradient->setY2(100);

        $this->assertSame($gradient, $result, 'setY2 should return self for chaining');
        $this->assertSame('100', $gradient->getAttribute('y2'));

        $y2 = $gradient->getY2();
        $this->assertInstanceOf(Length::class, $y2);
        $this->assertSame(100.0, $y2->getValue());
    }

    public function testSetAndGetY2WithString(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setY2('50%');

        $y2 = $gradient->getY2();
        $this->assertInstanceOf(Length::class, $y2);
        $this->assertSame(50.0, $y2->getValue());
        $this->assertSame('%', $y2->getUnit());
    }

    public function testGetY2ReturnsNullWhenNotSet(): void
    {
        $gradient = new LinearGradientElement();

        $this->assertNull($gradient->getY2());
    }

    public function testSetAndGetGradientUnits(): void
    {
        $gradient = new LinearGradientElement();
        $result = $gradient->setGradientUnits('userSpaceOnUse');

        $this->assertSame($gradient, $result, 'setGradientUnits should return self for chaining');
        $this->assertSame('userSpaceOnUse', $gradient->getAttribute('gradientUnits'));
        $this->assertSame('userSpaceOnUse', $gradient->getGradientUnits());
    }

    public function testSetAndGetGradientUnitsObjectBoundingBox(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setGradientUnits('objectBoundingBox');

        $this->assertSame('objectBoundingBox', $gradient->getGradientUnits());
    }

    public function testGetGradientUnitsReturnsNullWhenNotSet(): void
    {
        $gradient = new LinearGradientElement();

        $this->assertNull($gradient->getGradientUnits());
    }

    public function testSetAndGetGradientTransform(): void
    {
        $gradient = new LinearGradientElement();
        $result = $gradient->setGradientTransform('rotate(45)');

        $this->assertSame($gradient, $result, 'setGradientTransform should return self for chaining');
        $this->assertSame('rotate(45)', $gradient->getAttribute('gradientTransform'));
        $this->assertSame('rotate(45)', $gradient->getGradientTransform());
    }

    public function testSetAndGetGradientTransformComplex(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setGradientTransform('translate(50,50) rotate(45) scale(2)');

        $this->assertSame('translate(50,50) rotate(45) scale(2)', $gradient->getGradientTransform());
    }

    public function testGetGradientTransformReturnsNullWhenNotSet(): void
    {
        $gradient = new LinearGradientElement();

        $this->assertNull($gradient->getGradientTransform());
    }

    public function testSetAndGetSpreadMethod(): void
    {
        $gradient = new LinearGradientElement();
        $result = $gradient->setSpreadMethod('pad');

        $this->assertSame($gradient, $result, 'setSpreadMethod should return self for chaining');
        $this->assertSame('pad', $gradient->getAttribute('spreadMethod'));
        $this->assertSame('pad', $gradient->getSpreadMethod());
    }

    public function testSetAndGetSpreadMethodReflect(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setSpreadMethod('reflect');

        $this->assertSame('reflect', $gradient->getSpreadMethod());
    }

    public function testSetAndGetSpreadMethodRepeat(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setSpreadMethod('repeat');

        $this->assertSame('repeat', $gradient->getSpreadMethod());
    }

    public function testGetSpreadMethodReturnsNullWhenNotSet(): void
    {
        $gradient = new LinearGradientElement();

        $this->assertNull($gradient->getSpreadMethod());
    }

    public function testSetDirection(): void
    {
        $gradient = new LinearGradientElement();
        $result = $gradient->setDirection(0, 0, 100, 100);

        $this->assertSame($gradient, $result, 'setDirection should return self for chaining');
        $this->assertSame('0', $gradient->getAttribute('x1'));
        $this->assertSame('0', $gradient->getAttribute('y1'));
        $this->assertSame('100', $gradient->getAttribute('x2'));
        $this->assertSame('100', $gradient->getAttribute('y2'));

        $this->assertSame(0.0, $gradient->getX1()->getValue());
        $this->assertSame(0.0, $gradient->getY1()->getValue());
        $this->assertSame(100.0, $gradient->getX2()->getValue());
        $this->assertSame(100.0, $gradient->getY2()->getValue());
    }

    public function testSetDirectionWithStrings(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setDirection('0%', '0%', '100%', '100%');

        $this->assertSame('0%', $gradient->getAttribute('x1'));
        $this->assertSame('0%', $gradient->getAttribute('y1'));
        $this->assertSame('100%', $gradient->getAttribute('x2'));
        $this->assertSame('100%', $gradient->getAttribute('y2'));
    }

    public function testMethodChaining(): void
    {
        $gradient = new LinearGradientElement();
        $result = $gradient
            ->setX1(0)
            ->setY1(0)
            ->setX2(100)
            ->setY2(100)
            ->setGradientUnits('userSpaceOnUse')
            ->setGradientTransform('rotate(45)')
            ->setSpreadMethod('reflect');

        $this->assertSame($gradient, $result);
        $this->assertSame(0.0, $gradient->getX1()->getValue());
        $this->assertSame(0.0, $gradient->getY1()->getValue());
        $this->assertSame(100.0, $gradient->getX2()->getValue());
        $this->assertSame(100.0, $gradient->getY2()->getValue());
        $this->assertSame('userSpaceOnUse', $gradient->getGradientUnits());
        $this->assertSame('rotate(45)', $gradient->getGradientTransform());
        $this->assertSame('reflect', $gradient->getSpreadMethod());
    }

    public function testCompleteLinearGradientConfiguration(): void
    {
        $gradient = new LinearGradientElement();
        $gradient
            ->setDirection('0%', '0%', '100%', '0%')
            ->setGradientUnits('objectBoundingBox')
            ->setSpreadMethod('pad');

        $this->assertSame('0%', $gradient->getAttribute('x1'));
        $this->assertSame('0%', $gradient->getAttribute('y1'));
        $this->assertSame('100%', $gradient->getAttribute('x2'));
        $this->assertSame('0%', $gradient->getAttribute('y2'));
        $this->assertSame('objectBoundingBox', $gradient->getAttribute('gradientUnits'));
        $this->assertSame('pad', $gradient->getAttribute('spreadMethod'));
    }

    public function testCanContainStopElements(): void
    {
        $gradient = new LinearGradientElement();
        $stop1 = new StopElement();
        $stop2 = new StopElement();

        $gradient->appendChild($stop1);
        $gradient->appendChild($stop2);

        $this->assertTrue($gradient->hasChildren());
        $this->assertSame(2, $gradient->getChildCount());
        $this->assertSame([$stop1, $stop2], $gradient->getChildren());
        $this->assertSame($gradient, $stop1->getParent());
        $this->assertSame($gradient, $stop2->getParent());
    }
}
