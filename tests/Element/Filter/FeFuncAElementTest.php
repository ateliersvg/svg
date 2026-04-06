<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeFuncAElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeFuncAElement::class)]
final class FeFuncAElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeFuncAElement();

        $this->assertSame('feFuncA', $element->getTagName());
    }

    public function testSetAndGetType(): void
    {
        $element = new FeFuncAElement();
        $result = $element->setType('linear');

        $this->assertSame($element, $result);
        $this->assertSame('linear', $element->getType());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeFuncAElement();

        $this->assertNull($element->getType());
        $this->assertNull($element->getTableValues());
        $this->assertNull($element->getSlope());
        $this->assertNull($element->getIntercept());
    }
}
