<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeMorphologyElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeMorphologyElement::class)]
final class FeMorphologyElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeMorphologyElement();

        $this->assertSame('feMorphology', $element->getTagName());
    }

    public function testSetAndGetOperator(): void
    {
        $element = new FeMorphologyElement();
        $result = $element->setOperator('dilate');

        $this->assertSame($element, $result);
        $this->assertSame('dilate', $element->getOperator());
    }

    public function testSetAndGetRadius(): void
    {
        $element = new FeMorphologyElement();
        $result = $element->setRadius('2');

        $this->assertSame($element, $result);
        $this->assertSame('2', $element->getRadius());
    }

    public function testSetRadiusWithNumeric(): void
    {
        $element = new FeMorphologyElement();
        $element->setRadius(3);

        $this->assertSame('3', $element->getRadius());
    }

    public function testOperators(): void
    {
        $element = new FeMorphologyElement();

        $element->setOperator('erode');
        $this->assertSame('erode', $element->getOperator());

        $element->setOperator('dilate');
        $this->assertSame('dilate', $element->getOperator());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeMorphologyElement();

        $this->assertNull($element->getOperator());
        $this->assertNull($element->getRadius());
    }
}
