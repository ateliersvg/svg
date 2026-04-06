<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Hyperlinking;

use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\Hyperlinking\AnchorElement;
use Atelier\Svg\Element\Shape\CircleElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnchorElement::class)]
final class AnchorElementTest extends TestCase
{
    public function testTagName(): void
    {
        $anchor = new AnchorElement();

        $this->assertSame('a', $anchor->getTagName());
    }

    public function testIsContainerElement(): void
    {
        $anchor = new AnchorElement();

        $this->assertInstanceOf(ContainerElementInterface::class, $anchor);
    }

    public function testSetAndGetHref(): void
    {
        $anchor = new AnchorElement();

        $result = $anchor->setHref('https://example.com');

        $this->assertSame($anchor, $result);
        $this->assertSame('https://example.com', $anchor->getHref());
    }

    public function testGetHrefReturnsNullWhenNotSet(): void
    {
        $anchor = new AnchorElement();

        $this->assertNull($anchor->getHref());
    }

    public function testSetAndGetTarget(): void
    {
        $anchor = new AnchorElement();

        $result = $anchor->setTarget('_blank');

        $this->assertSame($anchor, $result);
        $this->assertSame('_blank', $anchor->getTarget());
    }

    public function testGetTargetReturnsNullWhenNotSet(): void
    {
        $anchor = new AnchorElement();

        $this->assertNull($anchor->getTarget());
    }

    public function testCanAppendChildren(): void
    {
        $anchor = new AnchorElement();
        $circle = new CircleElement();

        $anchor->appendChild($circle);

        $children = $anchor->getChildren();
        $this->assertCount(1, $children);
        $this->assertSame($circle, $children[0]);
    }

    public function testSetGenericAttribute(): void
    {
        $anchor = new AnchorElement();
        $anchor->setAttribute('id', 'my-link');

        $this->assertSame('my-link', $anchor->getAttribute('id'));
    }
}
