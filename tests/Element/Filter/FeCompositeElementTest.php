<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeCompositeElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeCompositeElement::class)]
final class FeCompositeElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeCompositeElement();

        $this->assertSame('feComposite', $element->getTagName());
    }

    public function testSetAndGetOperator(): void
    {
        $element = new FeCompositeElement();
        $result = $element->setOperator('arithmetic');

        $this->assertSame($element, $result);
        $this->assertSame('arithmetic', $element->getOperator());
    }

    public function testSetAndGetK1(): void
    {
        $element = new FeCompositeElement();
        $result = $element->setK1(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getK1());
    }

    public function testSetAndGetK2(): void
    {
        $element = new FeCompositeElement();
        $result = $element->setK2(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getK2());
    }

    public function testSetAndGetK3(): void
    {
        $element = new FeCompositeElement();
        $result = $element->setK3(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getK3());
    }

    public function testSetAndGetK4(): void
    {
        $element = new FeCompositeElement();
        $result = $element->setK4(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getK4());
    }

    public function testSetAndGetIn2(): void
    {
        $element = new FeCompositeElement();
        $result = $element->setIn2('BackgroundImage');

        $this->assertSame($element, $result);
        $this->assertSame('BackgroundImage', $element->getIn2());
    }

    public function testPorterDuffOperators(): void
    {
        $element = new FeCompositeElement();

        $operators = ['over', 'in', 'out', 'atop', 'xor'];
        foreach ($operators as $operator) {
            $element->setOperator($operator);
            $this->assertSame($operator, $element->getOperator());
        }
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeCompositeElement();

        $this->assertNull($element->getOperator());
        $this->assertNull($element->getK1());
        $this->assertNull($element->getK2());
        $this->assertNull($element->getK3());
        $this->assertNull($element->getK4());
        $this->assertNull($element->getIn2());
    }
}
