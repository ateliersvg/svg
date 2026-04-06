<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeOffsetElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeOffsetElement::class)]
final class FeOffsetElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeOffsetElement();

        $this->assertSame('feOffset', $element->getTagName());
    }

    public function testSetAndGetDx(): void
    {
        $element = new FeOffsetElement();
        $result = $element->setDx('10');

        $this->assertSame($element, $result);
        $this->assertSame('10', $element->getDx());
    }

    public function testSetAndGetDy(): void
    {
        $element = new FeOffsetElement();
        $result = $element->setDy('20');

        $this->assertSame($element, $result);
        $this->assertSame('20', $element->getDy());
    }

    public function testSetDxWithNumeric(): void
    {
        $element = new FeOffsetElement();
        $element->setDx(5);

        $this->assertSame('5', $element->getDx());
    }

    public function testSetDyWithFloat(): void
    {
        $element = new FeOffsetElement();
        $element->setDy(7.5);

        $this->assertSame('7.5', $element->getDy());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeOffsetElement();

        $this->assertNull($element->getDx());
        $this->assertNull($element->getDy());
    }
}
