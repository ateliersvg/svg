<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests;

use Atelier\Svg\Document;
use Atelier\Svg\Document\MergeStrategy;
use Atelier\Svg\Element\ElementCollection;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Validation\ValidationResult;
use Atelier\Svg\Value\Style\StyleManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
final class DocumentTest extends TestCase
{
    public function testConstructEmpty(): void
    {
        $doc = new Document();

        $this->assertNull($doc->getRootElement());
    }

    public function testConstructWithRoot(): void
    {
        $svg = new SvgElement();
        $doc = new Document($svg);

        $this->assertSame($svg, $doc->getRootElement());
    }

    public function testCreate(): void
    {
        $doc = Document::create(800, 600);
        $root = $doc->getRootElement();

        $this->assertInstanceOf(SvgElement::class, $root);
        $this->assertSame('800', $root->getAttribute('width'));
        $this->assertSame('600', $root->getAttribute('height'));
        $this->assertSame('http://www.w3.org/2000/svg', $root->getAttribute('xmlns'));
    }

    public function testCreateWithDefaults(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $this->assertSame('300', $root->getAttribute('width'));
        $this->assertSame('150', $root->getAttribute('height'));
    }

    public function testSetRootElement(): void
    {
        $doc = new Document();
        $svg = new SvgElement();

        $result = $doc->setRootElement($svg);

        $this->assertSame($doc, $result);
        $this->assertSame($svg, $doc->getRootElement());
    }

    public function testGetElementByIdNotFound(): void
    {
        $doc = new Document();

        $this->assertNull($doc->getElementById('not-found'));
    }

    public function testRegisterElementId(): void
    {
        $doc = new Document();
        $svg = new SvgElement();

        $doc->registerElementId('test-id', $svg);

        $this->assertSame($svg, $doc->getElementById('test-id'));
    }

    public function testRegisterDuplicateIdThrows(): void
    {
        $doc = new Document();
        $svg1 = new SvgElement();
        $svg2 = new SvgElement();

        $doc->registerElementId('test-id', $svg1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("An element with ID 'test-id' is already registered");

        $doc->registerElementId('test-id', $svg2);
    }

    public function testUnregisterElementId(): void
    {
        $doc = new Document();
        $svg = new SvgElement();

        $doc->registerElementId('test-id', $svg);
        $this->assertSame($svg, $doc->getElementById('test-id'));

        $doc->unregisterElementId('test-id');
        $this->assertNull($doc->getElementById('test-id'));
    }

    public function testToString(): void
    {
        $doc = new Document();

        $this->assertSame('', $doc->toString());
        $this->assertSame('', (string) $doc);
    }

    public function testToStringWithRoot(): void
    {
        $doc = Document::create();

        $result = $doc->toString();

        $this->assertStringContainsString('svg', $result);
    }

    // ========================================================================
    // generateUniqueId
    // ========================================================================

    public function testGenerateUniqueIdDefault(): void
    {
        $doc = new Document();

        $this->assertSame('el-1', $doc->generateUniqueId());
    }

    public function testGenerateUniqueIdWithPrefix(): void
    {
        $doc = new Document();

        $this->assertSame('shape-1', $doc->generateUniqueId('shape'));
    }

    public function testGenerateUniqueIdSkipsExisting(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->registerElementId('el-1', $svg);

        $this->assertSame('el-2', $doc->generateUniqueId());
    }

    public function testGenerateUniqueIdSkipsMultipleExisting(): void
    {
        $doc = new Document();
        $doc->registerElementId('id-1', new SvgElement());
        $doc->registerElementId('id-2', new SvgElement());
        $doc->registerElementId('id-3', new SvgElement());

        $this->assertSame('id-4', $doc->generateUniqueId('id'));
    }

    // ========================================================================
    // hasDuplicateIds / getDuplicateIds
    // ========================================================================

    public function testHasDuplicateIdsReturnsFalseWhenNone(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10, ['id' => 'r1']);

        $this->assertFalse($doc->hasDuplicateIds());
        $this->assertSame([], $doc->getDuplicateIds());
    }

    // ========================================================================
    // prefixAllIds
    // ========================================================================

    public function testPrefixAllIds(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'myRect');
        $svg->appendChild($rect);
        $doc = new Document($svg);

        $doc->prefixAllIds('pfx-');

        $this->assertSame('pfx-myRect', $rect->getAttribute('id'));
        $this->assertNotNull($doc->getElementById('pfx-myRect'));
        $this->assertNull($doc->getElementById('myRect'));
    }

    public function testPrefixAllIdsOnEmptyDocument(): void
    {
        $doc = new Document();

        $result = $doc->prefixAllIds('pfx-');

        $this->assertSame($doc, $result);
    }

    // ========================================================================
    // querySelector / querySelectorAll
    // ========================================================================

    public function testQuerySelectorReturnsNullOnEmptyDocument(): void
    {
        $doc = new Document();

        $this->assertNull($doc->querySelector('rect'));
    }

    public function testQuerySelectorAllReturnsEmptyOnEmptyDocument(): void
    {
        $doc = new Document();

        $result = $doc->querySelectorAll('rect');

        $this->assertInstanceOf(ElementCollection::class, $result);
        $this->assertCount(0, $result);
    }

    public function testQuerySelectorFindsFirstMatch(): void
    {
        $doc = Document::create();
        $r1 = $doc->rect(0, 0, 10, 10, ['id' => 'first']);
        $doc->rect(0, 0, 20, 20, ['id' => 'second']);

        $found = $doc->querySelector('rect');

        $this->assertNotNull($found);
        $this->assertSame('first', $found->getAttribute('id'));
    }

    public function testQuerySelectorAllFindsAllMatches(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);
        $doc->rect(0, 0, 20, 20);
        $doc->circle(5, 5, 5);

        $rects = $doc->querySelectorAll('rect');

        $this->assertCount(2, $rects);
    }

    public function testQuerySelectorById(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10, ['id' => 'target']);

        $found = $doc->querySelector('#target');

        $this->assertNotNull($found);
        $this->assertSame('target', $found->getAttribute('id'));
    }

    // ========================================================================
    // findByTag
    // ========================================================================

    public function testFindByTag(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);
        $doc->circle(5, 5, 5);
        $doc->rect(10, 10, 20, 20);

        $rects = $doc->findByTag('rect');

        $this->assertCount(2, $rects);
    }

    public function testFindByTagNoMatches(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);

        $circles = $doc->findByTag('circle');

        $this->assertCount(0, $circles);
    }

    // ========================================================================
    // findByClass
    // ========================================================================

    public function testFindByClass(): void
    {
        $doc = Document::create();
        $r1 = $doc->rect(0, 0, 10, 10);
        $r1->addClass('highlight');
        $r2 = $doc->rect(0, 0, 20, 20);
        $r2->addClass('highlight');
        $doc->circle(5, 5, 5);

        $result = $doc->findByClass('highlight');

        $this->assertCount(2, $result);
    }

    public function testFindByClassNoMatches(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);

        $result = $doc->findByClass('nonexistent');

        $this->assertCount(0, $result);
    }

    // ========================================================================
    // select (alias for querySelectorAll)
    // ========================================================================

    public function testSelectIsAliasForQuerySelectorAll(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);
        $doc->rect(0, 0, 20, 20);

        $result = $doc->select('rect');

        $this->assertCount(2, $result);
    }

    // ========================================================================
    // getRoot (alias for getRootElement)
    // ========================================================================

    public function testGetRootReturnsRootElement(): void
    {
        $doc = Document::create();

        $this->assertSame($doc->getRootElement(), $doc->getRoot());
    }

    public function testGetRootReturnsNullWhenEmpty(): void
    {
        $doc = new Document();

        $this->assertNull($doc->getRoot());
    }

    // ========================================================================
    // g() - create and append group
    // ========================================================================

    public function testGCreatesGroupAndAppendsToRoot(): void
    {
        $doc = Document::create();

        $group = $doc->g();

        $this->assertInstanceOf(GroupElement::class, $group);
        $this->assertContains($group, $doc->getRootElement()->getChildren());
    }

    public function testGWithNoRoot(): void
    {
        $doc = new Document();

        $group = $doc->g();

        $this->assertInstanceOf(GroupElement::class, $group);
    }

    // ========================================================================
    // rect() - create and append rect
    // ========================================================================

    public function testRectCreatesRectAndAppendsToRoot(): void
    {
        $doc = Document::create();

        $rect = $doc->rect(10, 20, 30, 40);

        $this->assertInstanceOf(RectElement::class, $rect);
        $this->assertSame('10', $rect->getAttribute('x'));
        $this->assertSame('20', $rect->getAttribute('y'));
        $this->assertSame('30', $rect->getAttribute('width'));
        $this->assertSame('40', $rect->getAttribute('height'));
        $this->assertContains($rect, $doc->getRootElement()->getChildren());
    }

    public function testRectWithAttributes(): void
    {
        $doc = Document::create();

        $rect = $doc->rect(0, 0, 100, 100, ['fill' => 'red', 'id' => 'myRect']);

        $this->assertSame('red', $rect->getAttribute('fill'));
        $this->assertSame('myRect', $rect->getAttribute('id'));
    }

    public function testRectWithNoRoot(): void
    {
        $doc = new Document();

        $rect = $doc->rect(0, 0, 50, 50);

        $this->assertInstanceOf(RectElement::class, $rect);
    }

    // ========================================================================
    // circle() - create and append circle
    // ========================================================================

    public function testCircleCreatesCircleAndAppendsToRoot(): void
    {
        $doc = Document::create();

        $circle = $doc->circle(10, 20, 30);

        $this->assertInstanceOf(CircleElement::class, $circle);
        $this->assertSame('10', $circle->getAttribute('cx'));
        $this->assertSame('20', $circle->getAttribute('cy'));
        $this->assertSame('30', $circle->getAttribute('r'));
        $this->assertContains($circle, $doc->getRootElement()->getChildren());
    }

    public function testCircleWithAttributes(): void
    {
        $doc = Document::create();

        $circle = $doc->circle(0, 0, 50, ['fill' => 'blue', 'id' => 'myCircle']);

        $this->assertSame('blue', $circle->getAttribute('fill'));
        $this->assertSame('myCircle', $circle->getAttribute('id'));
    }

    public function testCircleWithNoRoot(): void
    {
        $doc = new Document();

        $circle = $doc->circle(0, 0, 50);

        $this->assertInstanceOf(CircleElement::class, $circle);
    }

    // ========================================================================
    // groupElements
    // ========================================================================

    public function testGroupElements(): void
    {
        $doc = Document::create();
        $r1 = $doc->rect(0, 0, 10, 10);
        $r2 = $doc->rect(0, 0, 20, 20);

        $group = $doc->groupElements([$r1, $r2], 'grp', ['class' => 'shapes']);

        $this->assertInstanceOf(GroupElement::class, $group);
        $this->assertSame('grp', $group->getAttribute('id'));
        $this->assertSame('shapes', $group->getAttribute('class'));
        $this->assertContains($r1, $group->getChildren());
        $this->assertContains($r2, $group->getChildren());
    }

    public function testGroupElementsWithoutId(): void
    {
        $doc = Document::create();
        $r1 = $doc->rect(0, 0, 10, 10);

        $group = $doc->groupElements([$r1]);

        $this->assertNull($group->getAttribute('id'));
        $this->assertContains($r1, $group->getChildren());
    }

    // ========================================================================
    // ungroup
    // ========================================================================

    public function testUngroup(): void
    {
        $doc = Document::create();
        $group = $doc->g();
        $rect = new RectElement();
        $rect->setAttribute('x', '0');
        $group->appendChild($rect);

        $root = $doc->getRootElement();
        $result = $doc->ungroup($group);

        $this->assertSame($doc, $result);
        $this->assertContains($rect, $root->getChildren());
        $this->assertNotContains($group, $root->getChildren());
    }

    public function testUngroupWithNoParent(): void
    {
        $doc = Document::create();
        $group = new GroupElement();

        $result = $doc->ungroup($group);

        $this->assertSame($doc, $result);
    }

    // ========================================================================
    // flattenGroups
    // ========================================================================

    public function testFlattenGroupsOnEmptyDocument(): void
    {
        $doc = new Document();

        $result = $doc->flattenGroups();

        $this->assertSame($doc, $result);
    }

    public function testFlattenGroupsRemovesBareBoneGroups(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        // Create a bare group (no id, class, transform etc.) with a rect child
        $group = new GroupElement();
        $rect = new RectElement();
        $group->appendChild($rect);
        $root->appendChild($group);

        $doc->flattenGroups();

        // The rect should be promoted to root and the empty group removed
        $this->assertContains($rect, $root->getChildren());
    }

    // ========================================================================
    // append
    // ========================================================================

    public function testAppend(): void
    {
        $doc1 = Document::create();
        $doc1->rect(0, 0, 10, 10, ['id' => 'r1']);

        $doc2 = Document::create();
        $doc2->circle(5, 5, 5, ['id' => 'c1']);

        $result = $doc1->append($doc2);

        $this->assertSame($doc1, $result);
        $children = $doc1->getRootElement()->getChildren();
        $this->assertGreaterThanOrEqual(2, count($children));
    }

    public function testAppendWithNullRoots(): void
    {
        $doc1 = new Document();
        $doc2 = new Document();

        $result = $doc1->append($doc2);

        $this->assertSame($doc1, $result);
    }

    // ========================================================================
    // importElement
    // ========================================================================

    public function testImportElementClonesElement(): void
    {
        $doc = Document::create();
        $original = new RectElement();
        $original->setAttribute('id', 'orig');
        $original->setAttribute('width', '50');

        $imported = $doc->importElement($original);

        $this->assertNotSame($original, $imported);
        $this->assertSame('50', $imported->getAttribute('width'));
    }

    public function testImportElementWithStringPrefix(): void
    {
        $doc = Document::create();
        $original = new RectElement();
        $original->setAttribute('id', 'box');

        $imported = $doc->importElement($original, options: ['prefix_ids' => 'imp-']);

        $this->assertSame('imp-box', $imported->getAttribute('id'));
    }

    // ========================================================================
    // indexElement via setRootElement (IDs from children are indexed)
    // ========================================================================

    public function testSetRootElementIndexesChildIds(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'indexed-rect');
        $svg->appendChild($rect);

        $doc->setRootElement($svg);

        $this->assertSame($rect, $doc->getElementById('indexed-rect'));
    }

    // ========================================================================
    // indexElement: duplicate ID silently ignored during indexing
    // ========================================================================

    public function testIndexElementSilentlyIgnoresDuplicateIds(): void
    {
        $svg = new SvgElement();
        $r1 = new RectElement();
        $r1->setAttribute('id', 'dup');
        $r2 = new RectElement();
        $r2->setAttribute('id', 'dup');
        $svg->appendChild($r1);
        $svg->appendChild($r2);

        // setRootElement calls indexElement which should silently ignore the duplicate
        $doc = new Document($svg);

        // First registered element wins
        $this->assertSame($r1, $doc->getElementById('dup'));
    }

    // ========================================================================
    // hasDuplicateIds / getDuplicateIds with actual duplicates
    // ========================================================================

    public function testHasDuplicateIdsReturnsTrueWhenDuplicatesExist(): void
    {
        $svg = new SvgElement();
        $r1 = new RectElement();
        $r1->setAttribute('id', 'dup');
        $r2 = new RectElement();
        $r2->setAttribute('id', 'dup');
        $svg->appendChild($r1);
        $svg->appendChild($r2);
        $doc = new Document($svg);

        $this->assertTrue($doc->hasDuplicateIds());
        $dups = $doc->getDuplicateIds();
        $this->assertArrayHasKey('dup', $dups);
        $this->assertSame(2, $dups['dup']);
    }

    // ========================================================================
    // updateIdsAndReferences: href and url() references
    // ========================================================================

    public function testPrefixAllIdsUpdatesHrefReferences(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'grad1');
        $svg->appendChild($rect);
        $use = new RectElement();
        $use->setAttribute('href', '#grad1');
        $svg->appendChild($use);
        $doc = new Document($svg);

        $doc->prefixAllIds('p-');

        $this->assertSame('#p-grad1', $use->getAttribute('href'));
    }

    public function testPrefixAllIdsUpdatesXlinkHrefReferences(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'g1');
        $svg->appendChild($rect);
        $ref = new RectElement();
        $ref->setAttribute('xlink:href', '#g1');
        $svg->appendChild($ref);
        $doc = new Document($svg);

        $doc->prefixAllIds('x-');

        $this->assertSame('#x-g1', $ref->getAttribute('xlink:href'));
    }

    public function testPrefixAllIdsUpdatesUrlReferences(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'myGrad');
        $svg->appendChild($rect);
        $target = new RectElement();
        $target->setAttribute('fill', 'url(#myGrad)');
        $svg->appendChild($target);
        $doc = new Document($svg);

        $doc->prefixAllIds('z-');

        $this->assertSame('url(#z-myGrad)', $target->getAttribute('fill'));
    }

    // ========================================================================
    // importElement: shallow clone and importElements
    // ========================================================================

    public function testImportElementShallowCloneNonContainer(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('width', '42');

        $imported = $doc->importElement($rect, deep: false);

        $this->assertNotSame($rect, $imported);
        $this->assertSame('42', $imported->getAttribute('width'));
    }

    public function testImportElements(): void
    {
        $doc = Document::create();
        $r1 = new RectElement();
        $r1->setAttribute('id', 'a');
        $r2 = new RectElement();
        $r2->setAttribute('id', 'b');

        $imported = $doc->importElements([$r1, $r2]);

        $this->assertCount(2, $imported);
        $this->assertNotSame($r1, $imported[0]);
        $this->assertNotSame($r2, $imported[1]);
    }

    // ========================================================================
    // importElement: auto-resolve ID conflicts
    // ========================================================================

    public function testImportElementResolvesIdConflicts(): void
    {
        $svg = new SvgElement();
        $existing = new RectElement();
        $existing->setAttribute('id', 'box');
        $svg->appendChild($existing);
        $doc = new Document($svg);

        $external = new RectElement();
        $external->setAttribute('id', 'box');
        $external->setAttribute('width', '99');

        $imported = $doc->importElement($external, deep: false, options: ['resolve_conflicts' => true]);

        // ID should be renamed to avoid conflict
        $this->assertNotSame('box', $imported->getAttribute('id'));
    }

    // ========================================================================
    // merge: all strategies
    // ========================================================================

    public function testMergeEmptyArray(): void
    {
        $merged = Document::merge([]);

        $this->assertNull($merged->getRootElement());
    }

    public function testMergeAppendStrategy(): void
    {
        $d1 = Document::create(100, 100);
        $d1->rect(0, 0, 10, 10);
        $d2 = Document::create(100, 100);
        $d2->circle(5, 5, 5);

        $merged = Document::merge([$d1, $d2], ['strategy' => MergeStrategy::APPEND]);

        $this->assertNotNull($merged->getRootElement());
        $this->assertGreaterThanOrEqual(2, count($merged->getRootElement()->getChildren()));
    }

    public function testMergeAppendWithPrefixIds(): void
    {
        $d1 = Document::create(100, 100);
        $d1->rect(0, 0, 10, 10, ['id' => 'r1']);

        $merged = Document::merge([$d1], ['strategy' => MergeStrategy::APPEND, 'prefix_ids' => true]);

        $this->assertNotNull($merged->getRootElement());
    }

    public function testMergeSideBySideStrategy(): void
    {
        $d1 = Document::create(100, 50);
        $d1->rect(0, 0, 10, 10);
        $d2 = Document::create(200, 80);
        $d2->rect(0, 0, 20, 20);

        $merged = Document::merge([$d1, $d2], ['strategy' => MergeStrategy::SIDE_BY_SIDE, 'spacing' => 10.0]);

        $root = $merged->getRootElement();
        $this->assertNotNull($root);
        $this->assertSame('310', $root->getAttribute('width'));
        $this->assertSame('80', $root->getAttribute('height'));
    }

    public function testMergeStackedStrategy(): void
    {
        $d1 = Document::create(100, 50);
        $d1->rect(0, 0, 10, 10);
        $d2 = Document::create(200, 80);
        $d2->rect(0, 0, 20, 20);

        $merged = Document::merge([$d1, $d2], ['strategy' => MergeStrategy::STACKED, 'spacing' => 5.0]);

        $root = $merged->getRootElement();
        $this->assertNotNull($root);
        $this->assertSame('200', $root->getAttribute('width'));
        $this->assertSame('135', $root->getAttribute('height'));
    }

    public function testMergeSymbolsStrategy(): void
    {
        $d1 = Document::create(100, 100);
        $d1->rect(0, 0, 10, 10);
        $d2 = Document::create(200, 200);
        $d2->circle(5, 5, 5);

        $merged = Document::merge([$d1, $d2], [
            'strategy' => MergeStrategy::SYMBOLS,
            'symbol_ids' => ['icon-a', 'icon-b'],
        ]);

        $root = $merged->getRootElement();
        $this->assertNotNull($root);
        // Should have a defs child with symbols
        $children = $root->getChildren();
        $this->assertGreaterThanOrEqual(1, count($children));
    }

    public function testMergeGridStrategy(): void
    {
        $d1 = Document::create(100, 100);
        $d1->rect(0, 0, 10, 10);
        $d2 = Document::create(100, 100);
        $d2->rect(0, 0, 20, 20);
        $d3 = Document::create(100, 100);
        $d3->circle(5, 5, 5);
        $d4 = Document::create(100, 100);
        $d4->circle(10, 10, 10);

        $merged = Document::merge([$d1, $d2, $d3, $d4], [
            'strategy' => MergeStrategy::GRID,
            'columns' => 2,
            'spacing' => 10,
        ]);

        $root = $merged->getRootElement();
        $this->assertNotNull($root);
        $this->assertCount(4, $root->getChildren());
    }

    public function testMergeSkipsDocumentsWithNoRoot(): void
    {
        $d1 = Document::create(100, 100);
        $d1->rect(0, 0, 10, 10);
        $d2 = new Document();

        $merged = Document::merge([$d1, $d2], ['strategy' => MergeStrategy::SIDE_BY_SIDE]);

        $this->assertNotNull($merged->getRootElement());
    }

    // ========================================================================
    // flattenGroups with maxDepth
    // ========================================================================

    public function testFlattenGroupsRespectsMaxDepth(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();
        $rect = new RectElement();
        $innerGroup->appendChild($rect);
        $outerGroup->appendChild($innerGroup);
        $root->appendChild($outerGroup);

        // maxDepth=0 should do nothing (no flattening)
        $doc->flattenGroups(0);

        // Both groups should still exist
        $this->assertContains($outerGroup, $root->getChildren());
    }

    // ========================================================================
    // shouldFlattenGroup: group with important attribute is NOT flattened
    // ========================================================================

    public function testFlattenGroupsKeepsGroupWithImportantAttributes(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(10,10)');
        $rect = new RectElement();
        $group->appendChild($rect);
        $root->appendChild($group);

        $doc->flattenGroups();

        // Group with transform should NOT be flattened
        $this->assertContains($group, $root->getChildren());
    }

    // ========================================================================
    // Optimizer convenience methods
    // ========================================================================

    public function testOptimizeMethod(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);

        $result = $doc->optimize();

        $this->assertSame($doc, $result);
    }

    public function testCleanupDefs(): void
    {
        $doc = Document::create();

        $result = $doc->cleanupDefs();

        $this->assertSame($doc, $result);
    }

    public function testRoundValues(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);

        $result = $doc->roundValues(2);

        $this->assertSame($doc, $result);
    }

    public function testOptimizeColors(): void
    {
        $doc = Document::create();

        $result = $doc->optimizeColors();

        $this->assertSame($doc, $result);
    }

    public function testInlineStyles(): void
    {
        $doc = Document::create();

        $result = $doc->inlineStyles();

        $this->assertSame($doc, $result);
    }

    public function testSimplifyPaths(): void
    {
        $doc = Document::create();

        $result = $doc->simplifyPaths();

        $this->assertSame($doc, $result);
    }

    public function testRemoveHidden(): void
    {
        $doc = Document::create();

        $result = $doc->removeHidden();

        $this->assertSame($doc, $result);
    }

    // ========================================================================
    // Analysis methods
    // ========================================================================

    public function testAnalyze(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);

        $report = $doc->analyze();

        $this->assertIsArray($report);
    }

    public function testPrintAnalysis(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);

        $output = $doc->printAnalysis();

        $this->assertIsString($output);
    }

    // ========================================================================
    // Theme
    // ========================================================================

    public function testApplyTheme(): void
    {
        $doc = Document::create();
        $doc->rect(0, 0, 10, 10);

        $result = $doc->applyTheme(['rect' => ['fill' => 'red']]);

        $this->assertSame($doc, $result);
    }

    // ========================================================================
    // Accessibility methods
    // ========================================================================

    public function testSetTitle(): void
    {
        $doc = Document::create();

        $result = $doc->setTitle('My SVG');

        $this->assertSame($doc, $result);
    }

    public function testSetDescription(): void
    {
        $doc = Document::create();

        $result = $doc->setDescription('A sample SVG');

        $this->assertSame($doc, $result);
    }

    public function testCheckAccessibility(): void
    {
        $doc = Document::create();

        $issues = $doc->checkAccessibility();

        $this->assertIsArray($issues);
    }

    public function testImproveAccessibility(): void
    {
        $doc = Document::create();

        $result = $doc->improveAccessibility();

        $this->assertSame($doc, $result);
    }

    // ========================================================================
    // Responsive
    // ========================================================================

    public function testMakeResponsive(): void
    {
        $doc = Document::create(800, 600);

        $result = $doc->makeResponsive();

        $this->assertSame($doc, $result);
    }

    // ========================================================================
    // Style manager
    // ========================================================================

    public function testStyleManager(): void
    {
        $doc = Document::create();

        $manager = $doc->styleManager();

        $this->assertInstanceOf(StyleManager::class, $manager);
    }

    // ========================================================================
    // Validation methods
    // ========================================================================

    public function testValidate(): void
    {
        $doc = Document::create();

        $result = $doc->validate();

        $this->assertInstanceOf(ValidationResult::class, $result);
    }

    public function testIsValid(): void
    {
        $doc = Document::create();

        $this->assertIsBool($doc->isValid());
    }

    public function testFindBrokenReferences(): void
    {
        $doc = Document::create();

        $broken = $doc->findBrokenReferences();

        $this->assertIsArray($broken);
    }

    public function testFindCircularReferences(): void
    {
        $doc = Document::create();

        $circular = $doc->findCircularReferences();

        $this->assertIsArray($circular);
    }

    public function testAutoFix(): void
    {
        $doc = Document::create();

        $result = $doc->autoFix();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('broken_references', $result);
        $this->assertArrayHasKey('duplicate_ids', $result);
    }

    public function testFixBrokenReferences(): void
    {
        $doc = Document::create();

        $count = $doc->fixBrokenReferences();

        $this->assertIsInt($count);
    }

    public function testFixDuplicateIds(): void
    {
        $doc = Document::create();

        $count = $doc->fixDuplicateIds();

        $this->assertIsInt($count);
    }

    // ========================================================================
    // countIds: null root branch (getDuplicateIds on empty document)
    // ========================================================================

    public function testGetDuplicateIdsOnEmptyDocument(): void
    {
        $doc = new Document();

        $this->assertSame([], $doc->getDuplicateIds());
        $this->assertFalse($doc->hasDuplicateIds());
    }

    // ========================================================================
    // importElement: prefix_ids on container with children
    // ========================================================================

    public function testImportElementPrefixIdsOnContainerWithChildren(): void
    {
        $doc = Document::create();
        $group = new GroupElement();
        $child = new RectElement();
        $child->setAttribute('id', 'inner');
        $group->appendChild($child);
        $group->setAttribute('id', 'outer');

        $imported = $doc->importElement($group, deep: true, options: ['prefix_ids' => 'imp-']);

        $this->assertSame('imp-outer', $imported->getAttribute('id'));
    }

    // ========================================================================
    // importElement: resolve conflicts on container with children
    // ========================================================================

    public function testImportElementResolvesConflictsOnContainerChildren(): void
    {
        $svg = new SvgElement();
        $existing = new RectElement();
        $existing->setAttribute('id', 'child1');
        $svg->appendChild($existing);
        $doc = new Document($svg);

        $group = new GroupElement();
        $conflicting = new RectElement();
        $conflicting->setAttribute('id', 'child1');
        $group->appendChild($conflicting);

        $imported = $doc->importElement($group, deep: true, options: ['resolve_conflicts' => true]);

        // The child's conflicting ID should have been renamed
        $children = $imported->getChildren();
        $this->assertNotSame('child1', $children[0]->getAttribute('id'));
    }

    // ========================================================================
    // merge strategies: skip documents with no root
    // ========================================================================

    public function testMergeStackedSkipsDocumentsWithNoRoot(): void
    {
        $d1 = Document::create(100, 50);
        $d1->rect(0, 0, 10, 10);
        $d2 = new Document(); // no root

        $merged = Document::merge([$d1, $d2], ['strategy' => MergeStrategy::STACKED]);

        $this->assertNotNull($merged->getRootElement());
    }

    public function testMergeSymbolsSkipsDocumentsWithNoRoot(): void
    {
        $d1 = Document::create(100, 100);
        $d1->rect(0, 0, 10, 10);
        $d2 = new Document(); // no root

        $merged = Document::merge([$d1, $d2], ['strategy' => MergeStrategy::SYMBOLS]);

        $this->assertNotNull($merged->getRootElement());
    }

    public function testMergeGridSkipsDocumentsWithNoRoot(): void
    {
        $d1 = Document::create(100, 100);
        $d1->rect(0, 0, 10, 10);
        $d2 = new Document(); // no root

        $merged = Document::merge([$d1, $d2], ['strategy' => MergeStrategy::GRID, 'columns' => 2]);

        $this->assertNotNull($merged->getRootElement());
    }

    // ========================================================================
    // mergeAsSymbols: viewBox copy
    // ========================================================================

    public function testMergeSymbolsCopiesViewBox(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('width', '100');
        $svg->setAttribute('height', '100');
        $svg->setAttribute('viewBox', '0 0 100 100');
        $rect = new RectElement();
        $svg->appendChild($rect);
        $doc = new Document($svg);

        $merged = Document::merge([$doc], ['strategy' => MergeStrategy::SYMBOLS]);

        $root = $merged->getRootElement();
        $this->assertNotNull($root);
        // The defs child should contain a symbol with viewBox
        $defs = $root->getChildren()[0];
        $symbol = $defs->getChildren()[0];
        $this->assertInstanceOf(SymbolElement::class, $symbol);
        $this->assertSame('0 0 100 100', $symbol->getAttribute('viewBox'));
    }
}
