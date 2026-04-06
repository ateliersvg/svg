<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Structural\UseElement;
use Atelier\Svg\Element\SvgElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
final class DocumentIdUtilitiesTest extends TestCase
{
    public function testGenerateUniqueIdWithDefaultPrefix(): void
    {
        $doc = new Document();

        $id = $doc->generateUniqueId();

        $this->assertSame('el-1', $id);
    }

    public function testGenerateUniqueIdWithCustomPrefix(): void
    {
        $doc = new Document();

        $id = $doc->generateUniqueId('icon');

        $this->assertSame('icon-1', $id);
    }

    public function testGenerateUniqueIdIncrementsWhenConflict(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'shape-1');
        $svg->appendChild($rect);

        $doc = new Document($svg);

        $id = $doc->generateUniqueId('shape');

        $this->assertSame('shape-2', $id);
    }

    public function testGenerateUniqueIdSkipsMultipleConflicts(): void
    {
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'item-1');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'item-2');
        $svg->appendChild($rect2);

        $rect3 = new RectElement();
        $rect3->setAttribute('id', 'item-3');
        $svg->appendChild($rect3);

        $doc = new Document($svg);

        $id = $doc->generateUniqueId('item');

        $this->assertSame('item-4', $id);
    }

    public function testGenerateMultipleUniqueIds(): void
    {
        $doc = new Document();

        $id1 = $doc->generateUniqueId('test');
        $id2 = $doc->generateUniqueId('test');
        $id3 = $doc->generateUniqueId('test');

        // Each call generates a new ID but doesn't register it
        $this->assertSame('test-1', $id1);
        $this->assertSame('test-1', $id2); // Same because not registered
        $this->assertSame('test-1', $id3);
    }

    public function testHasDuplicateIdsReturnsFalseWhenNoDuplicates(): void
    {
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'rect1');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'rect2');
        $svg->appendChild($rect2);

        $doc = new Document($svg);

        $this->assertFalse($doc->hasDuplicateIds());
    }

    public function testHasDuplicateIdsReturnsTrueWhenDuplicatesExist(): void
    {
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'duplicate');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'duplicate');
        $svg->appendChild($rect2);

        $doc = new Document($svg);

        $this->assertTrue($doc->hasDuplicateIds());
    }

    public function testHasDuplicateIdsReturnsFalseForEmptyDocument(): void
    {
        $doc = new Document();

        $this->assertFalse($doc->hasDuplicateIds());
    }

    public function testGetDuplicateIdsReturnsEmptyArrayWhenNoDuplicates(): void
    {
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'rect1');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'rect2');
        $svg->appendChild($rect2);

        $doc = new Document($svg);

        $this->assertSame([], $doc->getDuplicateIds());
    }

    public function testGetDuplicateIdsReturnsDuplicatesWithCounts(): void
    {
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'duplicate');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'duplicate');
        $svg->appendChild($rect2);

        $doc = new Document($svg);

        $duplicates = $doc->getDuplicateIds();

        $this->assertArrayHasKey('duplicate', $duplicates);
        $this->assertSame(2, $duplicates['duplicate']);
    }

    public function testGetDuplicateIdsWithMultipleDuplicates(): void
    {
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'dup1');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'dup1');
        $svg->appendChild($rect2);

        $rect3 = new RectElement();
        $rect3->setAttribute('id', 'dup2');
        $svg->appendChild($rect3);

        $rect4 = new RectElement();
        $rect4->setAttribute('id', 'dup2');
        $svg->appendChild($rect4);

        $rect5 = new RectElement();
        $rect5->setAttribute('id', 'dup2');
        $svg->appendChild($rect5);

        $doc = new Document($svg);

        $duplicates = $doc->getDuplicateIds();

        $this->assertCount(2, $duplicates);
        $this->assertSame(2, $duplicates['dup1']);
        $this->assertSame(3, $duplicates['dup2']);
    }

    public function testGetDuplicateIdsInNestedElements(): void
    {
        $svg = new SvgElement();

        $group = new GroupElement();
        $group->setAttribute('id', 'dup');

        $rect = new RectElement();
        $rect->setAttribute('id', 'dup');

        $svg->appendChild($group);
        $group->appendChild($rect);

        $doc = new Document($svg);

        $duplicates = $doc->getDuplicateIds();

        $this->assertArrayHasKey('dup', $duplicates);
        $this->assertSame(2, $duplicates['dup']);
    }

    public function testPrefixAllIdsAddsPrefix(): void
    {
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'rect1');
        $svg->appendChild($rect);

        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');
        $svg->appendChild($circle);

        $doc = new Document($svg);
        $doc->prefixAllIds('prefix-');

        $this->assertSame('prefix-rect1', $rect->getAttribute('id'));
        $this->assertSame('prefix-circle1', $circle->getAttribute('id'));
    }

    public function testPrefixAllIdsUpdatesReferences(): void
    {
        $svg = new SvgElement();

        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'myGradient');
        $svg->appendChild($gradient);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#myGradient)');
        $svg->appendChild($rect);

        $doc = new Document($svg);
        $doc->prefixAllIds('test-');

        $this->assertSame('test-myGradient', $gradient->getAttribute('id'));
        $this->assertSame('url(#test-myGradient)', $rect->getAttribute('fill'));
    }

    public function testPrefixAllIdsUpdatesHrefReferences(): void
    {
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'myRect');
        $svg->appendChild($rect);

        $use = new UseElement();
        $use->setAttribute('href', '#myRect');
        $svg->appendChild($use);

        $doc = new Document($svg);
        $doc->prefixAllIds('sprite-');

        $this->assertSame('sprite-myRect', $rect->getAttribute('id'));
        $this->assertSame('#sprite-myRect', $use->getAttribute('href'));
    }

    public function testPrefixAllIdsUpdatesXlinkHrefReferences(): void
    {
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'myRect');
        $svg->appendChild($rect);

        $use = new UseElement();
        $use->setAttribute('xlink:href', '#myRect');
        $svg->appendChild($use);

        $doc = new Document($svg);
        $doc->prefixAllIds('icon-');

        $this->assertSame('icon-myRect', $rect->getAttribute('id'));
        $this->assertSame('#icon-myRect', $use->getAttribute('xlink:href'));
    }

    public function testPrefixAllIdsUpdatesMultipleReferenceTypes(): void
    {
        $svg = new SvgElement();

        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'grad');
        $svg->appendChild($gradient);

        $rect = new RectElement();
        $rect->setAttribute('id', 'shape');
        $rect->setAttribute('fill', 'url(#grad)');
        $rect->setAttribute('stroke', 'url(#grad)');
        $svg->appendChild($rect);

        $use = new UseElement();
        $use->setAttribute('href', '#shape');
        $svg->appendChild($use);

        $doc = new Document($svg);
        $doc->prefixAllIds('doc-');

        $this->assertSame('doc-grad', $gradient->getAttribute('id'));
        $this->assertSame('doc-shape', $rect->getAttribute('id'));
        $this->assertSame('url(#doc-grad)', $rect->getAttribute('fill'));
        $this->assertSame('url(#doc-grad)', $rect->getAttribute('stroke'));
        $this->assertSame('#doc-shape', $use->getAttribute('href'));
    }

    public function testPrefixAllIdsUpdatesClipPathReferences(): void
    {
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'clip');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('clip-path', 'url(#clip)');
        $svg->appendChild($rect2);

        $doc = new Document($svg);
        $doc->prefixAllIds('clipped-');

        $this->assertSame('clipped-clip', $rect1->getAttribute('id'));
        $this->assertSame('url(#clipped-clip)', $rect2->getAttribute('clip-path'));
    }

    public function testPrefixAllIdsUpdatesFilterReferences(): void
    {
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'blur');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('filter', 'url(#blur)');
        $svg->appendChild($rect2);

        $doc = new Document($svg);
        $doc->prefixAllIds('fx-');

        $this->assertSame('fx-blur', $rect1->getAttribute('id'));
        $this->assertSame('url(#fx-blur)', $rect2->getAttribute('filter'));
    }

    public function testPrefixAllIdsRebuildsIdRegistry(): void
    {
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'myRect');
        $svg->appendChild($rect);

        $doc = new Document($svg);

        // Before prefixing
        $this->assertSame($rect, $doc->getElementById('myRect'));
        $this->assertNull($doc->getElementById('test-myRect'));

        $doc->prefixAllIds('test-');

        // After prefixing
        $this->assertNull($doc->getElementById('myRect'));
        $this->assertSame($rect, $doc->getElementById('test-myRect'));
    }

    public function testPrefixAllIdsWithNestedElements(): void
    {
        $svg = new SvgElement();

        $group = new GroupElement();
        $group->setAttribute('id', 'layer1');
        $svg->appendChild($group);

        $rect = new RectElement();
        $rect->setAttribute('id', 'rect1');
        $group->appendChild($rect);

        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');
        $group->appendChild($circle);

        $doc = new Document($svg);
        $doc->prefixAllIds('nested-');

        $this->assertSame('nested-layer1', $group->getAttribute('id'));
        $this->assertSame('nested-rect1', $rect->getAttribute('id'));
        $this->assertSame('nested-circle1', $circle->getAttribute('id'));
    }

    public function testPrefixAllIdsReturnsDocument(): void
    {
        $svg = new SvgElement();
        $doc = new Document($svg);

        $result = $doc->prefixAllIds('test-');

        $this->assertSame($doc, $result);
    }

    public function testPrefixAllIdsWithEmptyDocument(): void
    {
        $doc = new Document();
        $result = $doc->prefixAllIds('test-');

        $this->assertSame($doc, $result);
    }

    public function testPrefixAllIdsIgnoresElementsWithoutIds(): void
    {
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'hasId');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        // No ID
        $svg->appendChild($rect2);

        $doc = new Document($svg);
        $doc->prefixAllIds('prefix-');

        $this->assertSame('prefix-hasId', $rect1->getAttribute('id'));
        $this->assertNull($rect2->getAttribute('id'));
    }

    public function testComplexIdManagement(): void
    {
        $svg = new SvgElement();

        // Create some elements with IDs
        $grad = new LinearGradientElement();
        $grad->setAttribute('id', 'gradient');
        $svg->appendChild($grad);

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'rect');
        $rect1->setAttribute('fill', 'url(#gradient)');
        $svg->appendChild($rect1);

        $use = new UseElement();
        $use->setAttribute('href', '#rect');
        $svg->appendChild($use);

        $doc = new Document($svg);

        // Generate unique IDs
        $newId1 = $doc->generateUniqueId('shape');
        $newId2 = $doc->generateUniqueId('shape');
        $this->assertSame('shape-1', $newId1);
        $this->assertSame('shape-1', $newId2); // Same because not registered

        // Check for duplicates
        $this->assertFalse($doc->hasDuplicateIds());

        // Prefix all IDs
        $doc->prefixAllIds('svg-');

        // Verify prefixing worked
        $this->assertSame('svg-gradient', $grad->getAttribute('id'));
        $this->assertSame('svg-rect', $rect1->getAttribute('id'));
        $this->assertSame('url(#svg-gradient)', $rect1->getAttribute('fill'));
        $this->assertSame('#svg-rect', $use->getAttribute('href'));

        // Verify registry updated
        $this->assertSame($grad, $doc->getElementById('svg-gradient'));
        $this->assertSame($rect1, $doc->getElementById('svg-rect'));
    }
}
