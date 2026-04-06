<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeMergeNodeElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeMergeNodeElement::class)]
final class FeMergeNodeElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeMergeNodeElement();

        $this->assertSame('feMergeNode', $element->getTagName());
    }

    public function testSetAndGetIn(): void
    {
        $element = new FeMergeNodeElement();
        $result = $element->setIn('SourceGraphic');

        $this->assertSame($element, $result);
        $this->assertSame('SourceGraphic', $element->getIn());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeMergeNodeElement();

        $this->assertNull($element->getIn());
    }
}
