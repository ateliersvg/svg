<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\CollapseGroupsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollapseGroupsPass::class)]
final class CollapseGroupsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new CollapseGroupsPass();

        $this->assertSame('collapse-groups', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new CollapseGroupsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveEmptyGroup(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $emptyGroup = new GroupElement();

        $svg->appendChild($emptyGroup);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemoveMultipleEmptyGroups(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $emptyGroup1 = new GroupElement();
        $emptyGroup2 = new GroupElement();
        $path = new PathElement();

        $svg->appendChild($emptyGroup1);
        $svg->appendChild($path);
        $svg->appendChild($emptyGroup2);

        $document = new Document($svg);

        $this->assertCount(3, $svg->getChildren());

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($path, $svg->getChildren()[0]);
    }

    public function testCollapseGroupWithOneChild(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $svg->appendChild($group);
        $group->appendChild($path);

        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());
        $this->assertCount(1, $group->getChildren());

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($path, $svg->getChildren()[0]);
    }

    public function testPreserveGroupWithMultipleChildren(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path2 = new PathElement();

        $svg->appendChild($group);
        $group->appendChild($path1);
        $group->appendChild($path2);

        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());
        $this->assertCount(2, $group->getChildren());

        $pass->optimize($document);

        // Group should be preserved because it has multiple children
        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($group, $svg->getChildren()[0]);
        $this->assertCount(2, $group->getChildren());
    }

    public function testMergeGroupAttributesIntoChild(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $group->setAttribute('fill', 'red');
        $group->setAttribute('stroke', 'blue');

        $svg->appendChild($group);
        $group->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($path, $svg->getChildren()[0]);
        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('blue', $path->getAttribute('stroke'));
    }

    public function testPreserveChildAttributesWhenMerging(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $group->setAttribute('fill', 'red');
        $path->setAttribute('fill', 'blue');

        $svg->appendChild($group);
        $group->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        // Child's fill should be preserved, not overwritten by group's fill
        $this->assertSame('blue', $path->getAttribute('fill'));
    }

    public function testMergeTransformAttributes(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $group->setAttribute('transform', 'translate(10, 20)');

        $svg->appendChild($group);
        $group->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($path, $svg->getChildren()[0]);
        $this->assertSame('translate(10, 20)', $path->getAttribute('transform'));
    }

    public function testCombineTransformAttributes(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $group->setAttribute('transform', 'translate(10, 20)');
        $path->setAttribute('transform', 'scale(2)');

        $svg->appendChild($group);
        $group->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($path, $svg->getChildren()[0]);
        // Group transform is applied first, then child transform
        $this->assertSame('translate(10, 20) scale(2)', $path->getAttribute('transform'));
    }

    public function testCollapseNestedEmptyGroups(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();

        $svg->appendChild($outerGroup);
        $outerGroup->appendChild($innerGroup);

        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Both groups should be removed because they're empty
        $this->assertCount(0, $svg->getChildren());
    }

    public function testCollapseNestedSingleChildGroups(): void
    {
        $pass = new CollapseGroupsPass();
        $svg = new SvgElement();
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();
        $path = new PathElement();

        $svg->appendChild($outerGroup);
        $outerGroup->appendChild($innerGroup);
        $innerGroup->appendChild($path);

        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Both groups should be collapsed, leaving just the path
        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($path, $svg->getChildren()[0]);
    }
}
