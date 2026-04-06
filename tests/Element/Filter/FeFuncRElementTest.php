<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeFuncRElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeFuncRElement::class)]
final class FeFuncRElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeFuncRElement();

        $this->assertSame('feFuncR', $element->getTagName());
    }

    public function testSetAndGetType(): void
    {
        $element = new FeFuncRElement();
        $result = $element->setType('linear');

        $this->assertSame($element, $result);
        $this->assertSame('linear', $element->getType());
    }

    public function testSetAndGetTableValues(): void
    {
        $element = new FeFuncRElement();
        $result = $element->setTableValues('0 0.5 1');

        $this->assertSame($element, $result);
        $this->assertSame('0 0.5 1', $element->getTableValues());
    }

    public function testSetAndGetSlope(): void
    {
        $element = new FeFuncRElement();
        $result = $element->setSlope(1.5);

        $this->assertSame($element, $result);
        $this->assertSame('1.5', $element->getSlope());
    }

    public function testSetAndGetIntercept(): void
    {
        $element = new FeFuncRElement();
        $result = $element->setIntercept(0.2);

        $this->assertSame($element, $result);
        $this->assertSame('0.2', $element->getIntercept());
    }

    public function testSetAndGetAmplitude(): void
    {
        $element = new FeFuncRElement();
        $result = $element->setAmplitude(1);

        $this->assertSame($element, $result);
        $this->assertSame('1', $element->getAmplitude());
    }

    public function testSetAndGetExponent(): void
    {
        $element = new FeFuncRElement();
        $result = $element->setExponent(2);

        $this->assertSame($element, $result);
        $this->assertSame('2', $element->getExponent());
    }

    public function testSetAndGetOffset(): void
    {
        $element = new FeFuncRElement();
        $result = $element->setOffset(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getOffset());
    }

    public function testTypes(): void
    {
        $element = new FeFuncRElement();

        $types = ['identity', 'table', 'discrete', 'linear', 'gamma'];
        foreach ($types as $type) {
            $element->setType($type);
            $this->assertSame($type, $element->getType());
        }
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeFuncRElement();

        $this->assertNull($element->getType());
        $this->assertNull($element->getTableValues());
        $this->assertNull($element->getSlope());
        $this->assertNull($element->getIntercept());
        $this->assertNull($element->getAmplitude());
        $this->assertNull($element->getExponent());
        $this->assertNull($element->getOffset());
    }
}
