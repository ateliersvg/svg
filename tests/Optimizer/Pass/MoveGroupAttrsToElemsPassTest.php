<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\MoveGroupAttrsToElemsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MoveGroupAttrsToElemsPass::class)]
final class MoveGroupAttrsToElemsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $this->assertSame('move-group-attrs-to-elems', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $document = new Document();
        $pass->optimize($document);
        $this->assertNull($document->getRootElement());
    }

    public function testMovesFillFromGroupToChildren(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('fill', 'red');

        $path1 = new PathElement();
        $path2 = new PathElement();
        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertNull($group->getAttribute('fill'));
        $this->assertSame('red', $path1->getAttribute('fill'));
        $this->assertSame('red', $path2->getAttribute('fill'));
    }

    public function testDoesNotOverrideExistingChildAttribute(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('fill', 'red');

        $path1 = new PathElement();
        $path1->setAttribute('fill', 'blue');
        $path2 = new PathElement();
        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // path1 keeps its own fill, path2 gets the group's fill
        $this->assertNull($group->getAttribute('fill'));
        $this->assertSame('blue', $path1->getAttribute('fill'));
        $this->assertSame('red', $path2->getAttribute('fill'));
    }

    public function testMovesMultipleInheritableAttributes(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('fill', 'red');
        $group->setAttribute('stroke', 'blue');
        $group->setAttribute('opacity', '0.5');

        $path = new PathElement();
        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertNull($group->getAttribute('fill'));
        $this->assertNull($group->getAttribute('stroke'));
        $this->assertNull($group->getAttribute('opacity'));
        $this->assertSame('red', $path->getAttribute('fill'));
        $this->assertSame('blue', $path->getAttribute('stroke'));
        $this->assertSame('0.5', $path->getAttribute('opacity'));
    }

    public function testIgnoresNonInheritableAttributes(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(10,20)');
        $group->setAttribute('fill', 'red');

        $path = new PathElement();
        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // transform is NOT inheritable, stays on group
        $this->assertSame('translate(10,20)', $group->getAttribute('transform'));
        // fill IS inheritable, moves to child
        $this->assertSame('red', $path->getAttribute('fill'));
    }

    public function testSkipsEmptyGroup(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('fill', 'red');
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // No children to move to, attribute stays
        $this->assertSame('red', $group->getAttribute('fill'));
    }

    public function testSkipsGroupWithNoInheritableAttrs(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('id', 'my-group');
        $group->setAttribute('transform', 'rotate(45)');

        $path = new PathElement();
        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('my-group', $group->getAttribute('id'));
        $this->assertSame('rotate(45)', $group->getAttribute('transform'));
        $this->assertNull($path->getAttribute('id'));
    }

    public function testHandlesNestedGroups(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $svg = new SvgElement();
        $outer = new GroupElement();
        $outer->setAttribute('fill', 'red');
        $inner = new GroupElement();
        $inner->setAttribute('stroke', 'blue');
        $path = new PathElement();

        $inner->appendChild($path);
        $outer->appendChild($inner);
        $svg->appendChild($outer);
        $document = new Document($svg);

        $pass->optimize($document);

        // Bottom-up: inner processes first, then outer
        $this->assertSame('blue', $path->getAttribute('stroke'));
        $this->assertNull($inner->getAttribute('stroke'));
        $this->assertSame('red', $inner->getAttribute('fill'));
        $this->assertNull($outer->getAttribute('fill'));
    }

    public function testDoesNotAffectNonGroupContainers(): void
    {
        $pass = new MoveGroupAttrsToElemsPass();
        $svg = new SvgElement();
        $svg->setAttribute('fill', 'red');

        $path = new PathElement();
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // SVG is not a GroupElement, should not be processed
        $this->assertSame('red', $svg->getAttribute('fill'));
        $this->assertNull($path->getAttribute('fill'));
    }
}
