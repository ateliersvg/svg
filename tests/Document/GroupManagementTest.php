<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Document;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
final class GroupManagementTest extends TestCase
{
    private function createTestDocument(): Document
    {
        $doc = Document::create();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'rect1');
        $rect1->addClass('shape');

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'rect2');
        $rect2->addClass('shape');

        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');
        $circle->addClass('shape');

        $doc->getRootElement()->appendChild($rect1);
        $doc->getRootElement()->appendChild($rect2);
        $doc->getRootElement()->appendChild($circle);

        return $doc;
    }

    public function testGroupElements(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('rect')->toArray();
        $group = $doc->groupElements($elements);

        $this->assertInstanceOf(GroupElement::class, $group);
        $this->assertCount(2, $group->getChildren());
    }

    public function testGroupElementsWithId(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('.shape')->toArray();
        $group = $doc->groupElements($elements, id: 'myGroup');

        $this->assertEquals('myGroup', $group->getAttribute('id'));
        $this->assertCount(3, $group->getChildren());
    }

    public function testGroupElementsWithAttributes(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('rect')->toArray();
        $group = $doc->groupElements($elements, attributes: [
            'transform' => 'translate(10, 20)',
            'opacity' => '0.5',
        ]);

        $this->assertEquals('translate(10, 20)', $group->getAttribute('transform'));
        $this->assertEquals('0.5', $group->getAttribute('opacity'));
    }

    public function testGroupElementsRemovesFromParent(): void
    {
        $doc = $this->createTestDocument();

        $rootBefore = $doc->getRootElement()->getChildren();
        $this->assertCount(3, $rootBefore);

        $elements = $doc->querySelectorAll('rect')->toArray();
        $group = $doc->groupElements($elements);

        // Elements should be removed from root
        $rootAfter = $doc->getRootElement()->getChildren();
        $this->assertCount(1, $rootAfter); // Only circle remains

        // Elements should be in group
        $this->assertCount(2, $group->getChildren());
    }

    public function testUngroupElement(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('rect')->toArray();
        $group = $doc->groupElements($elements, id: 'testGroup');
        $doc->getRootElement()->appendChild($group);

        // Before ungroup: 1 circle + 1 group
        $this->assertCount(2, $doc->getRootElement()->getChildren());

        $doc->ungroup($group);

        // After ungroup: 1 circle + 2 rects (group removed)
        $this->assertCount(3, $doc->getRootElement()->getChildren());

        // Group should not exist anymore
        $this->assertNull($doc->querySelector('#testGroup'));
    }

    public function testUngroupPreservesChildren(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('rect')->toArray();
        $group = $doc->groupElements($elements);
        $doc->getRootElement()->appendChild($group);

        $childrenBefore = $group->getChildren();
        $childIds = array_map(fn ($el) => $el->getAttribute('id'), $childrenBefore);

        $doc->ungroup($group);

        // Children should still exist in document
        foreach ($childIds as $id) {
            $this->assertNotNull($doc->querySelector('#'.$id));
        }
    }

    public function testFlattenGroups(): void
    {
        $doc = Document::create();

        // Create nested structure: root > g1 > g2 > rect
        $g1 = new GroupElement();
        $g2 = new GroupElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'rect1');

        $g2->appendChild($rect);
        $g1->appendChild($g2);
        $doc->getRootElement()->appendChild($g1);

        $doc->flattenGroups();

        // After flattening, rect should be directly under root
        $children = $doc->getRootElement()->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(RectElement::class, $children[0]);
    }

    public function testFlattenGroupsWithMaxDepth(): void
    {
        $doc = Document::create();

        // Create: root > g1 > g2 > g3 > rect
        $g1 = new GroupElement();
        $g2 = new GroupElement();
        $g3 = new GroupElement();
        $rect = new RectElement();

        $g3->appendChild($rect);
        $g2->appendChild($g3);
        $g1->appendChild($g2);
        $doc->getRootElement()->appendChild($g1);

        // Flatten only 1 level
        $doc->flattenGroups(maxDepth: 1);

        // g1 should be removed, but g2 and g3 should remain
        $children = $doc->getRootElement()->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(GroupElement::class, $children[0]);

        // g2 should have g3 as child
        $g2Result = $children[0];
        $this->assertCount(1, $g2Result->getChildren());
        $this->assertInstanceOf(GroupElement::class, $g2Result->getChildren()[0]);
    }

    public function testFlattenGroupsPreservesImportantAttributes(): void
    {
        $doc = Document::create();

        $group = new GroupElement();
        $group->setAttribute('id', 'important');
        $group->setAttribute('transform', 'translate(10, 20)');
        $rect = new RectElement();
        $group->appendChild($rect);
        $doc->getRootElement()->appendChild($group);

        $doc->flattenGroups();

        // Group with important attributes should NOT be flattened
        $this->assertNotNull($doc->querySelector('#important'));
        $this->assertCount(1, $doc->getRootElement()->getChildren());
        $this->assertInstanceOf(GroupElement::class, $doc->getRootElement()->getChildren()[0]);
    }

    public function testFlattenGroupsOnlyFlattensEmptyGroups(): void
    {
        $doc = Document::create();

        $g1 = new GroupElement();
        $g1->addClass('important'); // Has class, shouldn't be flattened

        $g2 = new GroupElement(); // No attributes, should be flattened

        $rect = new RectElement();
        $g2->appendChild($rect);
        $g1->appendChild($g2);
        $doc->getRootElement()->appendChild($g1);

        $doc->flattenGroups();

        // g1 should remain (has class)
        $children = $doc->getRootElement()->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(GroupElement::class, $children[0]);
        $this->assertTrue($children[0]->hasClass('important'));

        // g2 should be flattened
        $this->assertCount(1, $children[0]->getChildren());
        $this->assertInstanceOf(RectElement::class, $children[0]->getChildren()[0]);
    }

    public function testGroupElementsWithEmptyArray(): void
    {
        $doc = $this->createTestDocument();

        $group = $doc->groupElements([]);

        $this->assertInstanceOf(GroupElement::class, $group);
        $this->assertCount(0, $group->getChildren());
    }

    public function testUngroupWithNoParent(): void
    {
        $doc = $this->createTestDocument();

        $group = new GroupElement();
        $rect = new RectElement();
        $group->appendChild($rect);

        // Group has no parent
        $doc->ungroup($group);

        // Should not throw error, just return
        $this->assertCount(1, $group->getChildren());
    }

    public function testFlattenGroupsOnEmptyDocument(): void
    {
        $doc = new Document();

        // Should not throw error
        $result = $doc->flattenGroups();

        $this->assertInstanceOf(Document::class, $result);
    }
}
