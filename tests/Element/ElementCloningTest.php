<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractElement::class)]
#[CoversClass(AbstractContainerElement::class)]
final class ElementCloningTest extends TestCase
{
    public function testCloneCreatesNewInstance(): void
    {
        $original = new RectElement();
        $original->setX(10)->setY(20)->setWidth(100)->setHeight(50);

        $clone = $original->clone();

        $this->assertNotSame($original, $clone);
        $this->assertInstanceOf(RectElement::class, $clone);
    }

    public function testCloneCopiesAllAttributes(): void
    {
        $original = new RectElement();
        $original->setX(10);
        $original->setY(20);
        $original->setWidth(100);
        $original->setHeight(50);
        $original->setAttribute('fill', '#ff0000');
        $original->setAttribute('stroke', '#000000');
        $original->setAttribute('id', 'myRect');

        $clone = $original->clone();

        $this->assertSame('10', $clone->getAttribute('x'));
        $this->assertSame('20', $clone->getAttribute('y'));
        $this->assertSame('100', $clone->getAttribute('width'));
        $this->assertSame('50', $clone->getAttribute('height'));
        $this->assertSame('#ff0000', $clone->getAttribute('fill'));
        $this->assertSame('#000000', $clone->getAttribute('stroke'));
        $this->assertSame('myRect', $clone->getAttribute('id'));
    }

    public function testCloneDoesNotCopyParent(): void
    {
        $group = new GroupElement();
        $rect = new RectElement();
        $group->appendChild($rect);

        $clone = $rect->clone();

        $this->assertNull($clone->getParent());
        $this->assertSame($group, $rect->getParent());
    }

    public function testCloneIsIndependent(): void
    {
        $original = new RectElement();
        $original->setAttribute('fill', '#ff0000');

        $clone = $original->clone();
        $clone->setAttribute('fill', '#00ff00');

        $this->assertSame('#ff0000', $original->getAttribute('fill'));
        $this->assertSame('#00ff00', $clone->getAttribute('fill'));
    }

    public function testCloneWithClasses(): void
    {
        $original = new RectElement();
        $original->addClass('button primary');

        $clone = $original->clone();

        $this->assertSame(['button', 'primary'], $clone->getClasses());

        // Modify clone's classes
        $clone->addClass('large');

        $this->assertSame(['button', 'primary'], $original->getClasses());
        $this->assertSame(['button', 'primary', 'large'], $clone->getClasses());
    }

    public function testCloneEmptyElement(): void
    {
        $original = new CircleElement();
        $clone = $original->clone();

        $this->assertNotSame($original, $clone);
        $this->assertInstanceOf(CircleElement::class, $clone);
        $this->assertEmpty($clone->getAttributes());
    }

    public function testCloneDeepCreatesNewInstance(): void
    {
        $group = new GroupElement();
        $rect = new RectElement();
        $group->appendChild($rect);

        $clone = $group->cloneDeep();

        $this->assertNotSame($group, $clone);
        $this->assertInstanceOf(GroupElement::class, $clone);
    }

    public function testCloneDeepCopiesChildren(): void
    {
        $group = new GroupElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'rect1');
        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');

        $group->appendChild($rect);
        $group->appendChild($circle);

        $clone = $group->cloneDeep();

        $this->assertCount(2, $clone->getChildren());

        $clonedChildren = $clone->getChildren();
        $this->assertInstanceOf(RectElement::class, $clonedChildren[0]);
        $this->assertInstanceOf(CircleElement::class, $clonedChildren[1]);

        $this->assertSame('rect1', $clonedChildren[0]->getAttribute('id'));
        $this->assertSame('circle1', $clonedChildren[1]->getAttribute('id'));
    }

    public function testCloneDeepCreatesIndependentCopies(): void
    {
        $group = new GroupElement();
        $rect = new RectElement();
        $rect->setAttribute('fill', '#ff0000');
        $group->appendChild($rect);

        $clone = $group->cloneDeep();

        // Modify cloned child
        $clonedRect = $clone->getChildren()[0];
        $clonedRect->setAttribute('fill', '#00ff00');

        // Original should be unchanged
        $this->assertSame('#ff0000', $rect->getAttribute('fill'));
        $this->assertSame('#00ff00', $clonedRect->getAttribute('fill'));
    }

    public function testCloneDeepWithNestedGroups(): void
    {
        $outerGroup = new GroupElement();
        $outerGroup->setAttribute('id', 'outer');

        $innerGroup = new GroupElement();
        $innerGroup->setAttribute('id', 'inner');

        $rect = new RectElement();
        $rect->setAttribute('id', 'rect');

        $innerGroup->appendChild($rect);
        $outerGroup->appendChild($innerGroup);

        $clone = $outerGroup->cloneDeep();

        $this->assertCount(1, $clone->getChildren());
        $this->assertSame('outer', $clone->getAttribute('id'));

        $clonedInner = $clone->getChildren()[0];
        $this->assertInstanceOf(GroupElement::class, $clonedInner);
        $this->assertSame('inner', $clonedInner->getAttribute('id'));

        $clonedRect = $clonedInner->getChildren()[0];
        $this->assertInstanceOf(RectElement::class, $clonedRect);
        $this->assertSame('rect', $clonedRect->getAttribute('id'));
    }

    public function testCloneDeepSetsParentReferences(): void
    {
        $group = new GroupElement();
        $rect = new RectElement();
        $group->appendChild($rect);

        $clone = $group->cloneDeep();

        $clonedRect = $clone->getChildren()[0];
        $this->assertSame($clone, $clonedRect->getParent());
        $this->assertNotSame($group, $clonedRect->getParent());
    }

    public function testCloneDeepWithTransformCallback(): void
    {
        $group = new GroupElement();
        $rect1 = new RectElement();
        $rect1->setAttribute('fill', '#ff0000');
        $rect2 = new RectElement();
        $rect2->setAttribute('fill', '#00ff00');

        $group->appendChild($rect1);
        $group->appendChild($rect2);

        $clone = $group->cloneDeep(function ($element) {
            if ($element instanceof RectElement) {
                $element->setAttribute('stroke', '#000000');
            }

            return $element;
        });

        $clonedChildren = $clone->getChildren();
        $this->assertSame('#000000', $clonedChildren[0]->getAttribute('stroke'));
        $this->assertSame('#000000', $clonedChildren[1]->getAttribute('stroke'));

        // Originals should be unchanged
        $this->assertNull($rect1->getAttribute('stroke'));
        $this->assertNull($rect2->getAttribute('stroke'));
    }

    public function testCloneDeepWithTransformReturningNull(): void
    {
        $group = new GroupElement();
        $rect = new RectElement();
        $rect->setAttribute('fill', '#ff0000');
        $group->appendChild($rect);

        $clone = $group->cloneDeep(
            // Return null, element should still be included
            fn ($element) => null);

        $this->assertCount(1, $clone->getChildren());
    }

    public function testCloneDeepEmptyContainer(): void
    {
        $group = new GroupElement();
        $clone = $group->cloneDeep();

        $this->assertNotSame($group, $clone);
        $this->assertCount(0, $clone->getChildren());
    }

    public function testCloneDeepComplexStructure(): void
    {
        $svg = new SvgElement();
        $svg->setWidth(800);
        $svg->setHeight(600);

        $group1 = new GroupElement();
        $group1->setAttribute('id', 'layer1');

        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(100)->setHeight(100);
        $rect1->addClass('shape primary');

        $rect2 = new RectElement();
        $rect2->setX(120)->setY(10)->setWidth(100)->setHeight(100);
        $rect2->addClass('shape secondary');

        $group1->appendChild($rect1);
        $group1->appendChild($rect2);

        $group2 = new GroupElement();
        $group2->setAttribute('id', 'layer2');

        $circle = new CircleElement();
        $circle->setCx(200)->setCy(200)->setR(50);

        $group2->appendChild($circle);

        $svg->appendChild($group1);
        $svg->appendChild($group2);

        $clone = $svg->cloneDeep();

        // Verify structure
        $this->assertInstanceOf(SvgElement::class, $clone);
        $this->assertSame('800', $clone->getAttribute('width'));
        $this->assertSame('600', $clone->getAttribute('height'));
        $this->assertCount(2, $clone->getChildren());

        $clonedGroup1 = $clone->getChildren()[0];
        $this->assertSame('layer1', $clonedGroup1->getAttribute('id'));
        $this->assertCount(2, $clonedGroup1->getChildren());

        $clonedRect1 = $clonedGroup1->getChildren()[0];
        $this->assertTrue($clonedRect1->hasClass('primary'));

        $clonedGroup2 = $clone->getChildren()[1];
        $this->assertSame('layer2', $clonedGroup2->getAttribute('id'));
        $this->assertCount(1, $clonedGroup2->getChildren());
    }

    public function testCloneDeepMaintainsAttributeTypes(): void
    {
        $rect = new RectElement();
        $rect->setX(10.5);
        $rect->setY(20.75);
        $rect->setWidth(100);
        $rect->setHeight(50);

        $clone = $rect->clone();

        // Attributes are stored as strings
        $this->assertSame('10.5', $clone->getAttribute('x'));
        $this->assertSame('20.75', $clone->getAttribute('y'));
        $this->assertSame('100', $clone->getAttribute('width'));
        $this->assertSame('50', $clone->getAttribute('height'));
    }

    public function testMultipleClones(): void
    {
        $original = new RectElement();
        $original->setAttribute('fill', '#ff0000');

        $clone1 = $original->clone();
        $clone2 = $original->clone();
        $clone3 = $original->clone();

        $this->assertNotSame($clone1, $clone2);
        $this->assertNotSame($clone2, $clone3);
        $this->assertNotSame($clone1, $clone3);

        // All should have the same attributes
        $this->assertSame('#ff0000', $clone1->getAttribute('fill'));
        $this->assertSame('#ff0000', $clone2->getAttribute('fill'));
        $this->assertSame('#ff0000', $clone3->getAttribute('fill'));
    }

    public function testCloneDeepWithTransformModifyingFill(): void
    {
        $group = new GroupElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('fill', '#ff0000');

        $rect2 = new RectElement();
        $rect2->setAttribute('fill', '#00ff00');

        $circle = new CircleElement();
        $circle->setAttribute('fill', '#0000ff');

        $group->appendChild($rect1);
        $group->appendChild($rect2);
        $group->appendChild($circle);

        $clone = $group->cloneDeep(function ($element) {
            // Change all fills to black
            if ($element->hasAttribute('fill')) {
                $element->setAttribute('fill', '#000000');
            }

            return $element;
        });

        $clonedChildren = $clone->getChildren();
        $this->assertSame('#000000', $clonedChildren[0]->getAttribute('fill'));
        $this->assertSame('#000000', $clonedChildren[1]->getAttribute('fill'));
        $this->assertSame('#000000', $clonedChildren[2]->getAttribute('fill'));

        // Originals unchanged
        $this->assertSame('#ff0000', $rect1->getAttribute('fill'));
        $this->assertSame('#00ff00', $rect2->getAttribute('fill'));
        $this->assertSame('#0000ff', $circle->getAttribute('fill'));
    }
}
