<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Gradient;

use Atelier\Svg\Element\Gradient\RadialGradientElement;
use Atelier\Svg\Element\Gradient\StopElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RadialGradientElement::class)]
final class RadialGradientElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertSame('radialGradient', $gradient->getTagName());
    }

    public function testSetAndGetCx(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setCx(50);

        $this->assertSame($gradient, $result, 'setCx should return self for chaining');
        $this->assertSame('50', $gradient->getAttribute('cx'));

        $cx = $gradient->getCx();
        $this->assertInstanceOf(Length::class, $cx);
        $this->assertSame(50.0, $cx->getValue());
    }

    public function testSetAndGetCxWithString(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setCx('50%');

        $cx = $gradient->getCx();
        $this->assertInstanceOf(Length::class, $cx);
        $this->assertSame(50.0, $cx->getValue());
        $this->assertSame('%', $cx->getUnit());
    }

    public function testGetCxReturnsNullWhenNotSet(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertNull($gradient->getCx());
    }

    public function testSetAndGetCy(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setCy(50);

        $this->assertSame($gradient, $result, 'setCy should return self for chaining');
        $this->assertSame('50', $gradient->getAttribute('cy'));

        $cy = $gradient->getCy();
        $this->assertInstanceOf(Length::class, $cy);
        $this->assertSame(50.0, $cy->getValue());
    }

    public function testSetAndGetCyWithString(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setCy('50%');

        $cy = $gradient->getCy();
        $this->assertInstanceOf(Length::class, $cy);
        $this->assertSame(50.0, $cy->getValue());
        $this->assertSame('%', $cy->getUnit());
    }

    public function testGetCyReturnsNullWhenNotSet(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertNull($gradient->getCy());
    }

    public function testSetAndGetR(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setR(50);

        $this->assertSame($gradient, $result, 'setR should return self for chaining');
        $this->assertSame('50', $gradient->getAttribute('r'));

        $r = $gradient->getR();
        $this->assertInstanceOf(Length::class, $r);
        $this->assertSame(50.0, $r->getValue());
    }

    public function testSetAndGetRWithString(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setR('50%');

        $r = $gradient->getR();
        $this->assertInstanceOf(Length::class, $r);
        $this->assertSame(50.0, $r->getValue());
        $this->assertSame('%', $r->getUnit());
    }

    public function testGetRReturnsNullWhenNotSet(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertNull($gradient->getR());
    }

    public function testSetAndGetFx(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setFx(40);

        $this->assertSame($gradient, $result, 'setFx should return self for chaining');
        $this->assertSame('40', $gradient->getAttribute('fx'));

        $fx = $gradient->getFx();
        $this->assertInstanceOf(Length::class, $fx);
        $this->assertSame(40.0, $fx->getValue());
    }

    public function testSetAndGetFxWithString(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setFx('40%');

        $fx = $gradient->getFx();
        $this->assertInstanceOf(Length::class, $fx);
        $this->assertSame(40.0, $fx->getValue());
        $this->assertSame('%', $fx->getUnit());
    }

    public function testGetFxReturnsNullWhenNotSet(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertNull($gradient->getFx());
    }

    public function testSetAndGetFy(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setFy(40);

        $this->assertSame($gradient, $result, 'setFy should return self for chaining');
        $this->assertSame('40', $gradient->getAttribute('fy'));

        $fy = $gradient->getFy();
        $this->assertInstanceOf(Length::class, $fy);
        $this->assertSame(40.0, $fy->getValue());
    }

    public function testSetAndGetFyWithString(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setFy('40%');

        $fy = $gradient->getFy();
        $this->assertInstanceOf(Length::class, $fy);
        $this->assertSame(40.0, $fy->getValue());
        $this->assertSame('%', $fy->getUnit());
    }

    public function testGetFyReturnsNullWhenNotSet(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertNull($gradient->getFy());
    }

    public function testSetAndGetFr(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setFr(10);

        $this->assertSame($gradient, $result, 'setFr should return self for chaining');
        $this->assertSame('10', $gradient->getAttribute('fr'));

        $fr = $gradient->getFr();
        $this->assertInstanceOf(Length::class, $fr);
        $this->assertSame(10.0, $fr->getValue());
    }

    public function testSetAndGetFrWithString(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setFr('10%');

        $fr = $gradient->getFr();
        $this->assertInstanceOf(Length::class, $fr);
        $this->assertSame(10.0, $fr->getValue());
        $this->assertSame('%', $fr->getUnit());
    }

    public function testGetFrReturnsNullWhenNotSet(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertNull($gradient->getFr());
    }

    public function testSetAndGetGradientUnits(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setGradientUnits('userSpaceOnUse');

        $this->assertSame($gradient, $result, 'setGradientUnits should return self for chaining');
        $this->assertSame('userSpaceOnUse', $gradient->getAttribute('gradientUnits'));
        $this->assertSame('userSpaceOnUse', $gradient->getGradientUnits());
    }

    public function testSetAndGetGradientUnitsObjectBoundingBox(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setGradientUnits('objectBoundingBox');

        $this->assertSame('objectBoundingBox', $gradient->getGradientUnits());
    }

    public function testGetGradientUnitsReturnsNullWhenNotSet(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertNull($gradient->getGradientUnits());
    }

    public function testSetAndGetGradientTransform(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setGradientTransform('rotate(45)');

        $this->assertSame($gradient, $result, 'setGradientTransform should return self for chaining');
        $this->assertSame('rotate(45)', $gradient->getAttribute('gradientTransform'));
        $this->assertSame('rotate(45)', $gradient->getGradientTransform());
    }

    public function testSetAndGetGradientTransformComplex(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setGradientTransform('translate(50,50) rotate(45) scale(2)');

        $this->assertSame('translate(50,50) rotate(45) scale(2)', $gradient->getGradientTransform());
    }

    public function testGetGradientTransformReturnsNullWhenNotSet(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertNull($gradient->getGradientTransform());
    }

    public function testSetAndGetSpreadMethod(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setSpreadMethod('pad');

        $this->assertSame($gradient, $result, 'setSpreadMethod should return self for chaining');
        $this->assertSame('pad', $gradient->getAttribute('spreadMethod'));
        $this->assertSame('pad', $gradient->getSpreadMethod());
    }

    public function testSetAndGetSpreadMethodReflect(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setSpreadMethod('reflect');

        $this->assertSame('reflect', $gradient->getSpreadMethod());
    }

    public function testSetAndGetSpreadMethodRepeat(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setSpreadMethod('repeat');

        $this->assertSame('repeat', $gradient->getSpreadMethod());
    }

    public function testGetSpreadMethodReturnsNullWhenNotSet(): void
    {
        $gradient = new RadialGradientElement();

        $this->assertNull($gradient->getSpreadMethod());
    }

    public function testSetCenter(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setCenter(50, 50);

        $this->assertSame($gradient, $result, 'setCenter should return self for chaining');
        $this->assertSame('50', $gradient->getAttribute('cx'));
        $this->assertSame('50', $gradient->getAttribute('cy'));

        $this->assertSame(50.0, $gradient->getCx()->getValue());
        $this->assertSame(50.0, $gradient->getCy()->getValue());
    }

    public function testSetCenterWithStrings(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setCenter('50%', '50%');

        $this->assertSame('50%', $gradient->getAttribute('cx'));
        $this->assertSame('50%', $gradient->getAttribute('cy'));
    }

    public function testSetFocalPoint(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient->setFocalPoint(40, 40);

        $this->assertSame($gradient, $result, 'setFocalPoint should return self for chaining');
        $this->assertSame('40', $gradient->getAttribute('fx'));
        $this->assertSame('40', $gradient->getAttribute('fy'));

        $this->assertSame(40.0, $gradient->getFx()->getValue());
        $this->assertSame(40.0, $gradient->getFy()->getValue());
    }

    public function testSetFocalPointWithStrings(): void
    {
        $gradient = new RadialGradientElement();
        $gradient->setFocalPoint('30%', '30%');

        $this->assertSame('30%', $gradient->getAttribute('fx'));
        $this->assertSame('30%', $gradient->getAttribute('fy'));
    }

    public function testMethodChaining(): void
    {
        $gradient = new RadialGradientElement();
        $result = $gradient
            ->setCx(50)
            ->setCy(50)
            ->setR(50)
            ->setFx(40)
            ->setFy(40)
            ->setFr(10)
            ->setGradientUnits('userSpaceOnUse')
            ->setGradientTransform('rotate(45)')
            ->setSpreadMethod('reflect');

        $this->assertSame($gradient, $result);
        $this->assertSame(50.0, $gradient->getCx()->getValue());
        $this->assertSame(50.0, $gradient->getCy()->getValue());
        $this->assertSame(50.0, $gradient->getR()->getValue());
        $this->assertSame(40.0, $gradient->getFx()->getValue());
        $this->assertSame(40.0, $gradient->getFy()->getValue());
        $this->assertSame(10.0, $gradient->getFr()->getValue());
        $this->assertSame('userSpaceOnUse', $gradient->getGradientUnits());
        $this->assertSame('rotate(45)', $gradient->getGradientTransform());
        $this->assertSame('reflect', $gradient->getSpreadMethod());
    }

    public function testCompleteRadialGradientConfiguration(): void
    {
        $gradient = new RadialGradientElement();
        $gradient
            ->setCenter('50%', '50%')
            ->setR('50%')
            ->setFocalPoint('30%', '30%')
            ->setFr('5%')
            ->setGradientUnits('objectBoundingBox')
            ->setSpreadMethod('pad');

        $this->assertSame('50%', $gradient->getAttribute('cx'));
        $this->assertSame('50%', $gradient->getAttribute('cy'));
        $this->assertSame('50%', $gradient->getAttribute('r'));
        $this->assertSame('30%', $gradient->getAttribute('fx'));
        $this->assertSame('30%', $gradient->getAttribute('fy'));
        $this->assertSame('5%', $gradient->getAttribute('fr'));
        $this->assertSame('objectBoundingBox', $gradient->getAttribute('gradientUnits'));
        $this->assertSame('pad', $gradient->getAttribute('spreadMethod'));

        $cx = $gradient->getCx();
        $cy = $gradient->getCy();
        $r = $gradient->getR();
        $fx = $gradient->getFx();
        $fy = $gradient->getFy();
        $fr = $gradient->getFr();

        $this->assertSame(50.0, $cx->getValue());
        $this->assertSame('%', $cx->getUnit());
        $this->assertSame(50.0, $cy->getValue());
        $this->assertSame('%', $cy->getUnit());
        $this->assertSame(50.0, $r->getValue());
        $this->assertSame('%', $r->getUnit());
        $this->assertSame(30.0, $fx->getValue());
        $this->assertSame('%', $fx->getUnit());
        $this->assertSame(30.0, $fy->getValue());
        $this->assertSame('%', $fy->getUnit());
        $this->assertSame(5.0, $fr->getValue());
        $this->assertSame('%', $fr->getUnit());
    }

    public function testCanContainStopElements(): void
    {
        $gradient = new RadialGradientElement();
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
