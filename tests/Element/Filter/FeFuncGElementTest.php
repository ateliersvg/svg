<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeFuncGElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeFuncGElement::class)]
final class FeFuncGElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeFuncGElement();

        $this->assertSame('feFuncG', $element->getTagName());
    }

    public function testSetAndGetType(): void
    {
        $element = new FeFuncGElement();
        $result = $element->setType('linear');

        $this->assertSame($element, $result);
        $this->assertSame('linear', $element->getType());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeFuncGElement();

        $this->assertNull($element->getType());
        $this->assertNull($element->getTableValues());
        $this->assertNull($element->getSlope());
        $this->assertNull($element->getIntercept());
    }
}
