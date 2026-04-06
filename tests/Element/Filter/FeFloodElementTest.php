<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeFloodElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeFloodElement::class)]
final class FeFloodElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeFloodElement();

        $this->assertSame('feFlood', $element->getTagName());
    }

    public function testSetAndGetFloodColor(): void
    {
        $element = new FeFloodElement();
        $result = $element->setFloodColor('red');

        $this->assertSame($element, $result);
        $this->assertSame('red', $element->getFloodColor());
    }

    public function testSetAndGetFloodOpacity(): void
    {
        $element = new FeFloodElement();
        $result = $element->setFloodOpacity(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getFloodOpacity());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeFloodElement();

        $this->assertNull($element->getFloodColor());
        $this->assertNull($element->getFloodOpacity());
    }
}
