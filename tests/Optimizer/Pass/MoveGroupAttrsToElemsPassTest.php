<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
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
        $this->assertSame('move-group-attrs-to-elems', (new MoveGroupAttrsToElemsPass())->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $document = new Document();

        (new MoveGroupAttrsToElemsPass())->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testMovesTransformFromGroupToChildren(): void
    {
        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(10 20)');
        $first = new PathElement();
        $second = new PathElement();
        $group->appendChild($first);
        $group->appendChild($second);

        $this->applyTo($group);

        $this->assertFalse($group->hasAttribute('transform'), 'the group gives its transform away');
        $this->assertSame('translate(10 20)', $first->getAttribute('transform'));
        $this->assertSame('translate(10 20)', $second->getAttribute('transform'));
    }

    public function testPrependsTheGroupTransformToAnExistingChildTransform(): void
    {
        // The group applies first, so it has to come first in the child's list.
        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(10 20)');
        $child = new PathElement();
        $child->setAttribute('transform', 'scale(2)');
        $group->appendChild($child);

        $this->applyTo($group);

        $this->assertSame('translate(10 20) scale(2)', $child->getAttribute('transform'));
    }

    public function testLeavesInheritedPresentationAttributesOnTheGroup(): void
    {
        // `fill` is inherited, so a child already gets it for free. Copying it onto
        // every child costs bytes and buys nothing.
        $group = new GroupElement();
        $group->setAttribute('fill', 'red');
        $group->setAttribute('stroke-width', '2');
        $child = new PathElement();
        $group->appendChild($child);

        $this->applyTo($group);

        $this->assertSame('red', $group->getAttribute('fill'));
        $this->assertSame('2', $group->getAttribute('stroke-width'));
        $this->assertFalse($child->hasAttribute('fill'));
        $this->assertFalse($child->hasAttribute('stroke-width'));
    }

    public function testSkipsAGroupWhoseChildCarriesAnId(): void
    {
        // Something may reference that child through a <use>, where the group
        // transform does not apply.
        $group = new GroupElement();
        $group->setAttribute('transform', 'rotate(45)');
        $child = new RectElement();
        $child->setId('target');
        $group->appendChild($child);

        $this->applyTo($group);

        $this->assertSame('rotate(45)', $group->getAttribute('transform'));
        $this->assertFalse($child->hasAttribute('transform'));
    }

    public function testSkipsAGroupHoldingAUrlReference(): void
    {
        // A clip path or a gradient resolves in the group's coordinate system.
        $group = new GroupElement();
        $group->setAttribute('transform', 'scale(3)');
        $group->setAttribute('clip-path', 'url(#frame)');
        $group->appendChild(new PathElement());

        $this->applyTo($group);

        $this->assertSame('scale(3)', $group->getAttribute('transform'));
    }

    public function testSkipsAGroupWithAChildThatIgnoresTransform(): void
    {
        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(5 5)');
        $group->appendChild(new PathElement());
        $group->appendChild(new DefsElement());

        $this->applyTo($group);

        $this->assertSame('translate(5 5)', $group->getAttribute('transform'));
    }

    public function testSkipsAGroupWithoutTransform(): void
    {
        $group = new GroupElement();
        $child = new CircleElement();
        $group->appendChild($child);

        $this->applyTo($group);

        $this->assertFalse($child->hasAttribute('transform'));
    }

    public function testSkipsAnEmptyGroup(): void
    {
        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(1 1)');

        $this->applyTo($group);

        $this->assertSame('translate(1 1)', $group->getAttribute('transform'));
    }

    public function testHandlesNestedGroups(): void
    {
        $outer = new GroupElement();
        $outer->setAttribute('transform', 'translate(10 0)');
        $inner = new GroupElement();
        $inner->setAttribute('transform', 'scale(2)');
        $leaf = new PathElement();
        $inner->appendChild($leaf);
        $outer->appendChild($inner);

        $this->applyTo($outer);

        $this->assertFalse($outer->hasAttribute('transform'));
        $this->assertSame('translate(10 0) scale(2)', $leaf->getAttribute('transform'));
    }

    private function applyTo(GroupElement $group): void
    {
        $svg = new SvgElement();
        $svg->appendChild($group);

        (new MoveGroupAttrsToElemsPass())->optimize(new Document($svg));
    }
}
