<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\GroupElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AbstractContainerElement methods.
 *
 * Covers the prependChild() method added for filter support.
 */
#[CoversClass(AbstractContainerElement::class)]
final class AbstractContainerElementTest extends TestCase
{
    private GroupElement $container;

    protected function setUp(): void
    {
        $this->container = new GroupElement();
    }

    public function testPrependChildAddsChildAtBeginning(): void
    {
        $rect = new RectElement();
        $circle = new CircleElement();

        $this->container->appendChild($rect);
        $result = $this->container->prependChild($circle);

        $this->assertSame($this->container, $result, 'prependChild should return $this for method chaining');

        $children = $this->container->getChildren();
        $this->assertCount(2, $children);
        $this->assertSame($circle, $children[0], 'Circle should be first');
        $this->assertSame($rect, $children[1], 'Rect should be second');
    }

    public function testPrependChildSetsParent(): void
    {
        $circle = new CircleElement();

        $this->container->prependChild($circle);

        $this->assertSame($this->container, $circle->getParent());
    }

    public function testPrependChildOnEmptyContainer(): void
    {
        $rect = new RectElement();

        $this->container->prependChild($rect);

        $children = $this->container->getChildren();
        $this->assertCount(1, $children);
        $this->assertSame($rect, $children[0]);
    }

    public function testPrependChildMultipleTimes(): void
    {
        $rect1 = new RectElement();
        $rect2 = new RectElement();
        $rect3 = new RectElement();

        $this->container->prependChild($rect1);
        $this->container->prependChild($rect2);
        $this->container->prependChild($rect3);

        $children = $this->container->getChildren();
        $this->assertCount(3, $children);
        // Last prepended should be first
        $this->assertSame($rect3, $children[0]);
        $this->assertSame($rect2, $children[1]);
        $this->assertSame($rect1, $children[2]);
    }

    public function testPrependChildCanBeChained(): void
    {
        $circle = new CircleElement();
        $rect = new RectElement();

        $result = $this->container
            ->prependChild($circle)
            ->prependChild($rect)
            ->setId('myGroup');

        $this->assertSame($this->container, $result);

        $children = $this->container->getChildren();
        $this->assertCount(2, $children);
        $this->assertSame($rect, $children[0]);
        $this->assertSame($circle, $children[1]);
        $this->assertSame('myGroup', $this->container->getId());
    }

    public function testPrependChildMixedWithAppendChild(): void
    {
        $rect1 = new RectElement();
        $rect2 = new RectElement();
        $circle1 = new CircleElement();
        $circle2 = new CircleElement();

        $this->container->appendChild($rect1);
        $this->container->prependChild($circle1);
        $this->container->appendChild($rect2);
        $this->container->prependChild($circle2);

        $children = $this->container->getChildren();
        $this->assertCount(4, $children);

        // Expected order: circle2 (prepended last), circle1 (prepended first), rect1 (appended first), rect2 (appended last)
        $this->assertSame($circle2, $children[0]);
        $this->assertSame($circle1, $children[1]);
        $this->assertSame($rect1, $children[2]);
        $this->assertSame($rect2, $children[3]);
    }

    public function testPrependChildOnDefsElement(): void
    {
        // Test with a different container type to ensure it works for all AbstractContainerElement subclasses
        $defs = new DefsElement();
        $rect1 = new RectElement();
        $rect2 = new RectElement();

        $defs->appendChild($rect1);
        $defs->prependChild($rect2);

        $children = $defs->getChildren();
        $this->assertCount(2, $children);
        $this->assertSame($rect2, $children[0]);
        $this->assertSame($rect1, $children[1]);
    }

    public function testPrependChildUpdatesChildCount(): void
    {
        $this->assertSame(0, $this->container->getChildCount());

        $this->container->prependChild(new RectElement());
        $this->assertSame(1, $this->container->getChildCount());

        $this->container->prependChild(new CircleElement());
        $this->assertSame(2, $this->container->getChildCount());
    }

    public function testPrependChildUpdatesHasChildren(): void
    {
        $this->assertFalse($this->container->hasChildren());

        $this->container->prependChild(new RectElement());

        $this->assertTrue($this->container->hasChildren());
    }

    public function testPrependChildWithNestedContainers(): void
    {
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();
        $rect = new RectElement();

        $innerGroup->appendChild($rect);
        $outerGroup->prependChild($innerGroup);

        $this->assertSame($outerGroup, $innerGroup->getParent());
        $this->assertSame($innerGroup, $rect->getParent());

        $children = $outerGroup->getChildren();
        $this->assertCount(1, $children);
        $this->assertSame($innerGroup, $children[0]);
    }

    public function testCloneDeepClonesNonContainerChildren(): void
    {
        $image = new ImageElement();
        $image->setAttribute('id', 'original');

        $this->container->appendChild($image);

        $clone = $this->container->cloneDeep();

        $children = $clone->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(ImageElement::class, $children[0]);
        $this->assertNotSame($image, $children[0]);
        $this->assertSame('original', $children[0]->getAttribute('id'));
    }

    public function testLayoutReturnsLayoutBuilder(): void
    {
        $builder = $this->container->layout();

        $this->assertInstanceOf(\Atelier\Svg\Layout\LayoutBuilder::class, $builder);
    }

    public function testPrependChildPreservesExistingChildrenOrder(): void
    {
        $rect1 = new RectElement();
        $rect2 = new RectElement();
        $rect3 = new RectElement();
        $circle = new CircleElement();

        // Add three rects
        $this->container->appendChild($rect1);
        $this->container->appendChild($rect2);
        $this->container->appendChild($rect3);

        // Prepend a circle
        $this->container->prependChild($circle);

        $children = $this->container->getChildren();
        $this->assertCount(4, $children);

        // Circle should be first, rects should maintain their relative order
        $this->assertSame($circle, $children[0]);
        $this->assertSame($rect1, $children[1]);
        $this->assertSame($rect2, $children[2]);
        $this->assertSame($rect3, $children[3]);
    }
}
