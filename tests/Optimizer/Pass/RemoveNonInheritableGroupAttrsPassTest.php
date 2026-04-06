<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveNonInheritableGroupAttrsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveNonInheritableGroupAttrsPass::class)]
final class RemoveNonInheritableGroupAttrsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveNonInheritableGroupAttrsPass();
        $this->assertSame('remove-non-inheritable-group-attrs', $pass->getName());
    }

    public function testRemovesPositionalAttributesFromGroup(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('x', '10');
        $group->setAttribute('y', '20');
        $group->setAttribute('fill', 'red');

        $svg->appendChild($group);
        $document = new Document($svg);

        $pass = new RemoveNonInheritableGroupAttrsPass();
        $pass->optimize($document);

        $this->assertFalse($group->hasAttribute('x'));
        $this->assertFalse($group->hasAttribute('y'));
        $this->assertTrue($group->hasAttribute('fill'), 'Should preserve inheritable attributes');
    }

    public function testRemovesDimensionalAttributesFromGroup(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('width', '100');
        $group->setAttribute('height', '50');

        $svg->appendChild($group);
        $document = new Document($svg);

        $pass = new RemoveNonInheritableGroupAttrsPass();
        $pass->optimize($document);

        $this->assertFalse($group->hasAttribute('width'));
        $this->assertFalse($group->hasAttribute('height'));
    }

    public function testRemovesCircleAttributesFromGroup(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('cx', '50');
        $group->setAttribute('cy', '50');
        $group->setAttribute('r', '25');

        $svg->appendChild($group);
        $document = new Document($svg);

        $pass = new RemoveNonInheritableGroupAttrsPass();
        $pass->optimize($document);

        $this->assertFalse($group->hasAttribute('cx'));
        $this->assertFalse($group->hasAttribute('cy'));
        $this->assertFalse($group->hasAttribute('r'));
    }

    public function testPreservesInheritableAttributes(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('fill', 'red');
        $group->setAttribute('stroke', 'blue');
        $group->setAttribute('opacity', '0.5');
        $group->setAttribute('transform', 'rotate(45)');

        $svg->appendChild($group);
        $document = new Document($svg);

        $pass = new RemoveNonInheritableGroupAttrsPass();
        $pass->optimize($document);

        $this->assertTrue($group->hasAttribute('fill'));
        $this->assertTrue($group->hasAttribute('stroke'));
        $this->assertTrue($group->hasAttribute('opacity'));
        $this->assertTrue($group->hasAttribute('transform'));
    }

    public function testDoesNotAffectNonGroupElements(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new RemoveNonInheritableGroupAttrsPass();
        $pass->optimize($document);

        $this->assertTrue($rect->hasAttribute('x'), 'Should preserve rect x attribute');
        $this->assertTrue($rect->hasAttribute('y'), 'Should preserve rect y attribute');
        $this->assertTrue($rect->hasAttribute('width'), 'Should preserve rect width');
        $this->assertTrue($rect->hasAttribute('height'), 'Should preserve rect height');
    }

    public function testHandlesNestedGroups(): void
    {
        $svg = new SvgElement();
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();
        $outerGroup->setAttribute('x', '10');
        $innerGroup->setAttribute('y', '20');

        $svg->appendChild($outerGroup);
        $outerGroup->appendChild($innerGroup);
        $document = new Document($svg);

        $pass = new RemoveNonInheritableGroupAttrsPass();
        $pass->optimize($document);

        $this->assertFalse($outerGroup->hasAttribute('x'));
        $this->assertFalse($innerGroup->hasAttribute('y'));
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();
        $pass = new RemoveNonInheritableGroupAttrsPass();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }
}
