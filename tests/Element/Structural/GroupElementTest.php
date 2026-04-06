<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Structural;

use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GroupElement::class)]
final class GroupElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $group = new GroupElement();

        $this->assertSame('g', $group->getTagName());
    }

    public function testGetTagName(): void
    {
        $group = new GroupElement();

        $this->assertSame('g', $group->getTagName());
    }

    public function testCanContainChildren(): void
    {
        $group = new GroupElement();
        $path = new PathElement();

        $group->appendChild($path);

        $this->assertTrue($group->hasChildren());
        $this->assertSame(1, $group->getChildCount());
        $this->assertSame($path, $group->getChildren()[0]);
    }

    public function testCanContainMultipleChildren(): void
    {
        $group = new GroupElement();
        $path1 = new PathElement();
        $path2 = new PathElement();

        $group->appendChild($path1);
        $group->appendChild($path2);

        $this->assertSame(2, $group->getChildCount());
        $this->assertSame($path1, $group->getChildren()[0]);
        $this->assertSame($path2, $group->getChildren()[1]);
    }

    public function testCanContainNestedGroups(): void
    {
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();

        $outerGroup->appendChild($innerGroup);

        $this->assertTrue($outerGroup->hasChildren());
        $this->assertSame(1, $outerGroup->getChildCount());
        $this->assertSame($innerGroup, $outerGroup->getChildren()[0]);
    }

    public function testRemoveChild(): void
    {
        $group = new GroupElement();
        $path = new PathElement();

        $group->appendChild($path);
        $this->assertSame(1, $group->getChildCount());

        $group->removeChild($path);
        $this->assertSame(0, $group->getChildCount());
        $this->assertFalse($group->hasChildren());
    }

    public function testClearChildren(): void
    {
        $group = new GroupElement();
        $path1 = new PathElement();
        $path2 = new PathElement();

        $group->appendChild($path1);
        $group->appendChild($path2);
        $this->assertSame(2, $group->getChildCount());

        $group->clearChildren();
        $this->assertSame(0, $group->getChildCount());
        $this->assertFalse($group->hasChildren());
    }

    public function testSetAttribute(): void
    {
        $group = new GroupElement();
        $result = $group->setAttribute('id', 'my-group');

        $this->assertSame($group, $result);
        $this->assertSame('my-group', $group->getAttribute('id'));
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $group = new GroupElement();

        $this->assertNull($group->getAttribute('id'));
    }

    public function testHasAttribute(): void
    {
        $group = new GroupElement();
        $group->setAttribute('class', 'icon');

        $this->assertTrue($group->hasAttribute('class'));
        $this->assertFalse($group->hasAttribute('id'));
    }

    public function testRemoveAttribute(): void
    {
        $group = new GroupElement();
        $group->setAttribute('class', 'icon');
        $this->assertTrue($group->hasAttribute('class'));

        $result = $group->removeAttribute('class');
        $this->assertSame($group, $result);
        $this->assertFalse($group->hasAttribute('class'));
    }

    public function testGetAttributes(): void
    {
        $group = new GroupElement();
        $group->setAttribute('id', 'my-group');
        $group->setAttribute('class', 'icon');

        $attributes = $group->getAttributes();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('id', $attributes);
        $this->assertArrayHasKey('class', $attributes);
        $this->assertSame('my-group', $attributes['id']);
        $this->assertSame('icon', $attributes['class']);
    }

    public function testSetMultipleAttributes(): void
    {
        $group = new GroupElement();
        $group->setAttribute('id', 'my-group');
        $group->setAttribute('class', 'icon');
        $group->setAttribute('transform', 'translate(10, 20)');

        $this->assertSame('my-group', $group->getAttribute('id'));
        $this->assertSame('icon', $group->getAttribute('class'));
        $this->assertSame('translate(10, 20)', $group->getAttribute('transform'));
    }

    public function testParentRelationship(): void
    {
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();

        $outerGroup->appendChild($innerGroup);

        $this->assertSame($outerGroup, $innerGroup->getParent());
    }

    public function testParentIsNullByDefault(): void
    {
        $group = new GroupElement();

        $this->assertNull($group->getParent());
    }

    public function testSetParent(): void
    {
        $group1 = new GroupElement();
        $group2 = new GroupElement();

        $result = $group2->setParent($group1);

        $this->assertSame($group2, $result);
        $this->assertSame($group1, $group2->getParent());
    }
}
