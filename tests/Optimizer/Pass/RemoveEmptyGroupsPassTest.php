<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyGroupsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveEmptyGroupsPass::class)]
final class RemoveEmptyGroupsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveEmptyGroupsPass();

        $this->assertSame('remove-empty-groups', $pass->getName());
    }

    public function testRemovesEmptyGroupsWithoutAttributes(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass();
        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepsGroupsWithChildren(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(10, 10)');
        $group->appendChild(new PathElement());
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass();
        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
    }

    public function testUnwrapGroupsWithoutAttributes(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $child1 = new PathElement();
        $child2 = new PathElement();
        $group->appendChild($child1);
        $group->appendChild($child2);
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass();
        $pass->optimize($document);

        $this->assertCount(2, $svg->getChildren());
        $this->assertSame([$child1, $child2], $svg->getChildren());
    }

    public function testKeepsGroupsWithPreservingAttributes(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('id', 'keep-me');
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass();
        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
    }

    public function testRemovesNestedEmptyGroups(): void
    {
        $svg = new SvgElement();
        $outer = new GroupElement();
        $inner = new GroupElement();
        $outer->appendChild($inner);
        $svg->appendChild($outer);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass();
        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testPropagatesFillBeforeUnwrapping(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('fill', '#facc15');

        $path1 = new PathElement();
        $path2 = new PathElement();

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass();
        $pass->optimize($document);

        $this->assertCount(2, $svg->getChildren());
        $this->assertSame('#facc15', $path1->getAttribute('fill'));
        $this->assertSame('#facc15', $path2->getAttribute('fill'));
    }

    public function testOptimizeEmptyDocument(): void
    {
        $document = new Document();
        $pass = new RemoveEmptyGroupsPass();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testDoNotUnwrapGroupWithPreservingAttributes(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('id', 'important');
        $group->setAttribute('fill', 'red');
        $path = new PathElement();
        $group->appendChild($path);
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass();
        $pass->optimize($document);

        // Group with id should not be unwrapped
        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($group, $svg->getChildren()[0]);
    }

    public function testDoNotUnwrapGroupWithNonPropagatableAttribute(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('transform', 'rotate(45)');
        $path = new PathElement();
        $group->appendChild($path);
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass();
        $pass->optimize($document);

        // Group with non-propagatable attribute (transform) should not be unwrapped
        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($group, $svg->getChildren()[0]);
    }

    public function testDoNotPropagateAttributeIfChildAlreadyHasIt(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('fill', 'red');

        $path = new PathElement();
        $path->setAttribute('fill', 'blue');
        $group->appendChild($path);
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass();
        $pass->optimize($document);

        // Child's fill should not be overridden
        $this->assertSame('blue', $path->getAttribute('fill'));
    }

    public function testDisableUnwrapAttributeLessGroups(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $group->appendChild($path);
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveEmptyGroupsPass(unwrapAttributeLessGroups: false);
        $pass->optimize($document);

        // Group without attributes should NOT be unwrapped
        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($group, $svg->getChildren()[0]);
    }
}
