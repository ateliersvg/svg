<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\MoveAttributesToGroupPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MoveAttributesToGroupPass::class)]
final class MoveAttributesToGroupPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new MoveAttributesToGroupPass();

        $this->assertSame('move-attributes-to-group', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testMoveCommonFillToGroup(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'red');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($group->hasAttribute('fill'));
        $this->assertSame('red', $group->getAttribute('fill'));
        $this->assertFalse($path1->hasAttribute('fill'));
        $this->assertFalse($path2->hasAttribute('fill'));
    }

    public function testMoveCommonStrokeToGroup(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('stroke', 'blue');
        $path2 = new PathElement();
        $path2->setAttribute('stroke', 'blue');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($group->hasAttribute('stroke'));
        $this->assertSame('blue', $group->getAttribute('stroke'));
        $this->assertFalse($path1->hasAttribute('stroke'));
        $this->assertFalse($path2->hasAttribute('stroke'));
    }

    public function testMoveMultipleCommonAttributes(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path1->setAttribute('stroke', 'blue');
        $path1->setAttribute('opacity', '0.5');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'red');
        $path2->setAttribute('stroke', 'blue');
        $path2->setAttribute('opacity', '0.5');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('red', $group->getAttribute('fill'));
        $this->assertSame('blue', $group->getAttribute('stroke'));
        $this->assertSame('0.5', $group->getAttribute('opacity'));
        $this->assertFalse($path1->hasAttribute('fill'));
        $this->assertFalse($path1->hasAttribute('stroke'));
        $this->assertFalse($path1->hasAttribute('opacity'));
    }

    public function testDoNotMoveDifferentValues(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'blue');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should not move fill since values differ
        $this->assertFalse($group->hasAttribute('fill'));
        $this->assertSame('red', $path1->getAttribute('fill'));
        $this->assertSame('blue', $path2->getAttribute('fill'));
    }

    public function testRequireMinimumChildren(): void
    {
        $pass = new MoveAttributesToGroupPass(3);
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'red');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should not move because only 2 children (need 3)
        $this->assertFalse($group->hasAttribute('fill'));
        $this->assertTrue($path1->hasAttribute('fill'));
        $this->assertTrue($path2->hasAttribute('fill'));
    }

    public function testMoveWithMinimumChildrenMet(): void
    {
        $pass = new MoveAttributesToGroupPass(3);
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'red');
        $path3 = new PathElement();
        $path3->setAttribute('fill', 'red');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $group->appendChild($path3);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should move because we have 3 children
        $this->assertTrue($group->hasAttribute('fill'));
        $this->assertSame('red', $group->getAttribute('fill'));
    }

    public function testDoNotOverrideExistingParentAttribute(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('fill', 'green');
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'red');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should not override existing parent attribute
        $this->assertSame('green', $group->getAttribute('fill'));
        $this->assertSame('red', $path1->getAttribute('fill'));
        $this->assertSame('red', $path2->getAttribute('fill'));
    }

    public function testMoveOnlyInheritableAttributes(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path1->setAttribute('x', '10');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'red');
        $path2->setAttribute('x', '10');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should move fill (inheritable) but not x (not inheritable)
        $this->assertTrue($group->hasAttribute('fill'));
        $this->assertFalse($group->hasAttribute('x'));
        $this->assertTrue($path1->hasAttribute('x'));
        $this->assertTrue($path2->hasAttribute('x'));
    }

    public function testHandlePartialMatches(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path1->setAttribute('stroke', 'blue');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'red');
        $path2->setAttribute('stroke', 'green');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should move fill but not stroke
        $this->assertSame('red', $group->getAttribute('fill'));
        $this->assertFalse($group->hasAttribute('stroke'));
        $this->assertSame('blue', $path1->getAttribute('stroke'));
        $this->assertSame('green', $path2->getAttribute('stroke'));
    }

    public function testMoveNestedGroups(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'red');

        $innerGroup->appendChild($path1);
        $innerGroup->appendChild($path2);
        $outerGroup->appendChild($innerGroup);
        $svg->appendChild($outerGroup);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should move fill to inner group
        $this->assertTrue($innerGroup->hasAttribute('fill'));
        $this->assertSame('red', $innerGroup->getAttribute('fill'));
        $this->assertFalse($path1->hasAttribute('fill'));
        $this->assertFalse($path2->hasAttribute('fill'));
    }

    public function testHandleMixedElements(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'red');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'red');
        $path = new PathElement();
        $path->setAttribute('fill', 'red');

        $group->appendChild($path);
        $group->appendChild($path2);
        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($group->hasAttribute('fill'));
        $this->assertSame('red', $group->getAttribute('fill'));
        $this->assertFalse($path->hasAttribute('fill'));
        $this->assertFalse($path2->hasAttribute('fill'));
        $this->assertFalse($path->hasAttribute('fill'));
    }

    public function testHandleOneChildMissingAttribute(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('fill', 'red');
        $path2 = new PathElement();
        // rect2 has no fill

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should not move fill since not all children have it
        $this->assertFalse($group->hasAttribute('fill'));
        $this->assertTrue($path1->hasAttribute('fill'));
    }

    public function testMoveStrokeWidth(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('stroke-width', '2');
        $path2 = new PathElement();
        $path2->setAttribute('stroke-width', '2');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($group->hasAttribute('stroke-width'));
        $this->assertSame('2', $group->getAttribute('stroke-width'));
        $this->assertFalse($path1->hasAttribute('stroke-width'));
        $this->assertFalse($path2->hasAttribute('stroke-width'));
    }

    public function testMoveFontProperties(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setAttribute('font-family', 'Arial');
        $path1->setAttribute('font-size', '12');
        $path2 = new PathElement();
        $path2->setAttribute('font-family', 'Arial');
        $path2->setAttribute('font-size', '12');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('Arial', $group->getAttribute('font-family'));
        $this->assertSame('12', $group->getAttribute('font-size'));
        $this->assertFalse($path1->hasAttribute('font-family'));
        $this->assertFalse($path1->hasAttribute('font-size'));
    }

    public function testSingleChildDoesNotMove(): void
    {
        $pass = new MoveAttributesToGroupPass(2);
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'red');

        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should not move with only one child
        $this->assertFalse($group->hasAttribute('fill'));
        $this->assertTrue($path->hasAttribute('fill'));
    }

    public function testEmptyGroupReturnsNoCommonAttributes(): void
    {
        $pass = new MoveAttributesToGroupPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($group->hasAttribute('fill'));
    }
}
