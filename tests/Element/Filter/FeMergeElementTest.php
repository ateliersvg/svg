<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeMergeElement;
use Atelier\Svg\Element\Filter\FeMergeNodeElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeMergeElement::class)]
final class FeMergeElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeMergeElement();

        $this->assertSame('feMerge', $element->getTagName());
    }

    public function testCanContainMergeNodes(): void
    {
        $merge = new FeMergeElement();
        $node1 = new FeMergeNodeElement();
        $node2 = new FeMergeNodeElement();

        $merge->appendChild($node1);
        $merge->appendChild($node2);

        $this->assertTrue($merge->hasChildren());
        $this->assertSame(2, $merge->getChildCount());
    }
}
