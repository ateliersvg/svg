<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeFuncBElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeFuncBElement::class)]
final class FeFuncBElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeFuncBElement();

        $this->assertSame('feFuncB', $element->getTagName());
    }

    public function testSetAndGetType(): void
    {
        $element = new FeFuncBElement();
        $result = $element->setType('linear');

        $this->assertSame($element, $result);
        $this->assertSame('linear', $element->getType());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeFuncBElement();

        $this->assertNull($element->getType());
        $this->assertNull($element->getTableValues());
        $this->assertNull($element->getSlope());
        $this->assertNull($element->getIntercept());
    }
}
