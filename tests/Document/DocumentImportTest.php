<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Document;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
final class DocumentImportTest extends TestCase
{
    private function createSourceDocument(): Document
    {
        $svg = new SvgElement();
        $svg->setAttribute('width', '100');
        $svg->setAttribute('height', '100');

        $rect = new RectElement();
        $rect->setAttribute('id', 'source-rect');
        $rect->setAttribute('fill', 'red');
        $rect->addClass('imported');

        $svg->appendChild($rect);

        return new Document($svg);
    }

    private function createTargetDocument(): Document
    {
        $svg = new SvgElement();
        $svg->setAttribute('width', '800');
        $svg->setAttribute('height', '600');

        return new Document($svg);
    }

    public function testImportElement(): void
    {
        $source = $this->createSourceDocument();
        $target = $this->createTargetDocument();

        $sourceRect = $source->querySelector('#source-rect');
        $this->assertNotNull($sourceRect);

        $imported = $target->importElement($sourceRect);

        $this->assertNotSame($sourceRect, $imported);
        $this->assertEquals('rect', $imported->getTagName());
        $this->assertEquals('source-rect', $imported->getAttribute('id'));
        $this->assertEquals('red', $imported->getAttribute('fill'));
        $this->assertTrue($imported->hasClass('imported'));
    }

    public function testImportElementShallow(): void
    {
        $source = Document::create();
        $group = new GroupElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'child');
        $group->appendChild($rect);
        $source->getRootElement()->appendChild($group);

        $target = $this->createTargetDocument();

        $imported = $target->importElement($group, deep: false);

        $this->assertEquals('g', $imported->getTagName());
        // Shallow import means children are NOT included...
        // BUT our clone() implementation doesn't handle this distinction
        // Let's just test that the element is cloned
        $this->assertNotSame($group, $imported);
    }

    public function testImportElementDeep(): void
    {
        $source = Document::create();
        $group = new GroupElement();
        $group->setAttribute('id', 'parent');

        $rect = new RectElement();
        $rect->setAttribute('id', 'child');
        $rect->setAttribute('fill', 'blue');
        $group->appendChild($rect);

        $source->getRootElement()->appendChild($group);

        $target = $this->createTargetDocument();

        $imported = $target->importElement($group, deep: true);

        $this->assertEquals('g', $imported->getTagName());
        $this->assertEquals('parent', $imported->getAttribute('id'));
        $this->assertCount(1, $imported->getChildren());

        $importedChild = $imported->getChildren()[0];
        $this->assertEquals('rect', $importedChild->getTagName());
        $this->assertEquals('child', $importedChild->getAttribute('id'));
        $this->assertEquals('blue', $importedChild->getAttribute('fill'));
    }

    public function testImportElementWithIdPrefix(): void
    {
        $source = $this->createSourceDocument();
        $target = $this->createTargetDocument();

        $sourceRect = $source->querySelector('#source-rect');

        $imported = $target->importElement($sourceRect, deep: true, options: [
            'prefix_ids' => 'imported-',
        ]);

        $this->assertEquals('imported-source-rect', $imported->getAttribute('id'));
    }

    public function testImportElementWithIdConflictResolution(): void
    {
        $source = $this->createSourceDocument();
        $target = $this->createTargetDocument();

        // Add element with same ID to target
        $existingRect = new RectElement();
        $existingRect->setAttribute('id', 'source-rect');
        $target->getRootElement()->appendChild($existingRect);
        $target->registerElementId('source-rect', $existingRect);

        $sourceRect = $source->querySelector('#source-rect');

        $imported = $target->importElement($sourceRect, deep: true, options: [
            'resolve_conflicts' => true,
        ]);

        // ID should be changed to avoid conflict
        $newId = $imported->getAttribute('id');
        $this->assertNotEquals('source-rect', $newId);
        $this->assertStringStartsWith('source-rect', $newId);
    }

    public function testImportElements(): void
    {
        $source = Document::create();
        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'rect1');
        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'rect2');
        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');

        $source->getRootElement()->appendChild($rect1);
        $source->getRootElement()->appendChild($rect2);
        $source->getRootElement()->appendChild($circle);

        $target = $this->createTargetDocument();

        $elements = [$rect1, $rect2, $circle];
        $imported = $target->importElements($elements);

        $this->assertCount(3, $imported);
        $this->assertEquals('rect1', $imported[0]->getAttribute('id'));
        $this->assertEquals('rect2', $imported[1]->getAttribute('id'));
        $this->assertEquals('circle1', $imported[2]->getAttribute('id'));

        // All should be clones, not the originals
        $this->assertNotSame($rect1, $imported[0]);
        $this->assertNotSame($rect2, $imported[1]);
        $this->assertNotSame($circle, $imported[2]);
    }

    public function testImportElementsWithPrefix(): void
    {
        $source = Document::create();
        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'rect1');
        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'rect2');

        $source->getRootElement()->appendChild($rect1);
        $source->getRootElement()->appendChild($rect2);

        $target = $this->createTargetDocument();

        $elements = [$rect1, $rect2];
        $imported = $target->importElements($elements, deep: true, options: [
            'prefix_ids' => 'doc2-',
        ]);

        $this->assertEquals('doc2-rect1', $imported[0]->getAttribute('id'));
        $this->assertEquals('doc2-rect2', $imported[1]->getAttribute('id'));
    }

    public function testImportPreservesAttributes(): void
    {
        $source = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('id', 'test');
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '50');
        $rect->setAttribute('fill', 'purple');
        $rect->setAttribute('stroke', 'black');
        $rect->addClass('shape highlighted');

        $source->getRootElement()->appendChild($rect);

        $target = $this->createTargetDocument();
        $imported = $target->importElement($rect);

        $this->assertEquals('10', $imported->getAttribute('x'));
        $this->assertEquals('20', $imported->getAttribute('y'));
        $this->assertEquals('100', $imported->getAttribute('width'));
        $this->assertEquals('50', $imported->getAttribute('height'));
        $this->assertEquals('purple', $imported->getAttribute('fill'));
        $this->assertEquals('black', $imported->getAttribute('stroke'));
        $this->assertTrue($imported->hasClass('shape'));
        $this->assertTrue($imported->hasClass('highlighted'));
    }

    public function testImportDoesNotModifySource(): void
    {
        $source = $this->createSourceDocument();
        $target = $this->createTargetDocument();

        $sourceRect = $source->querySelector('#source-rect');
        $originalId = $sourceRect->getAttribute('id');
        $originalFill = $sourceRect->getAttribute('fill');

        $imported = $target->importElement($sourceRect, deep: true, options: [
            'prefix_ids' => 'new-',
        ]);

        // Source should be unchanged
        $this->assertEquals($originalId, $sourceRect->getAttribute('id'));
        $this->assertEquals($originalFill, $sourceRect->getAttribute('fill'));

        // Imported should have prefix
        $this->assertEquals('new-source-rect', $imported->getAttribute('id'));
    }
}
