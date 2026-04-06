<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Document;

use Atelier\Svg\Document;
use Atelier\Svg\Document\MergeStrategy;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
final class DocumentMergeTest extends TestCase
{
    private function createTestDocument(string $id, int $width = 100, int $height = 100): Document
    {
        $doc = Document::create($width, $height);
        $rect = new RectElement();
        $rect->setAttribute('id', $id);
        $rect->setAttribute('fill', 'blue');
        $doc->getRootElement()->appendChild($rect);

        return $doc;
    }

    public function testMergeAppendStrategy(): void
    {
        $doc1 = $this->createTestDocument('rect1');
        $doc2 = $this->createTestDocument('rect2');
        $doc3 = $this->createTestDocument('rect3');

        $merged = Document::merge([$doc1, $doc2, $doc3], [
            'strategy' => MergeStrategy::APPEND,
        ]);

        $this->assertNotNull($merged->getRootElement());
        $this->assertCount(3, $merged->getRootElement()->getChildren());
    }

    public function testMergeWithIdPrefixing(): void
    {
        $doc1 = $this->createTestDocument('rect');
        $doc2 = $this->createTestDocument('rect');
        $doc3 = $this->createTestDocument('rect');

        $merged = Document::merge([$doc1, $doc2, $doc3], [
            'strategy' => MergeStrategy::APPEND,
            'prefix_ids' => true,
        ]);

        // All should have prefixed IDs
        $elements = $merged->querySelectorAll('rect');
        $this->assertCount(3, $elements);

        $ids = $elements->pluck('id');
        $this->assertEquals('doc0-rect', $ids[0]);
        $this->assertEquals('doc1-rect', $ids[1]);
        $this->assertEquals('doc2-rect', $ids[2]);
    }

    public function testMergeWithCustomIdPrefix(): void
    {
        $doc1 = $this->createTestDocument('rect1');
        $doc2 = $this->createTestDocument('rect2');

        $merged = Document::merge([$doc1, $doc2], [
            'strategy' => MergeStrategy::APPEND,
            'prefix_ids' => 'custom-',
        ]);

        $elements = $merged->querySelectorAll('rect');
        $this->assertCount(2, $elements);

        $ids = $elements->pluck('id');
        $this->assertEquals('custom-rect1', $ids[0]);
        $this->assertEquals('custom-rect2', $ids[1]);
    }

    public function testMergeSideBySide(): void
    {
        $doc1 = $this->createTestDocument('rect1', 100, 50);
        $doc2 = $this->createTestDocument('rect2', 150, 75);

        $merged = Document::merge([$doc1, $doc2], [
            'strategy' => MergeStrategy::SIDE_BY_SIDE,
            'spacing' => 20,
        ]);

        $root = $merged->getRootElement();
        $this->assertNotNull($root);

        // Width should be sum of widths + spacing
        $this->assertEquals('270', $root->getAttribute('width')); // 100 + 20 + 150

        // Height should be max height
        $this->assertEquals('75', $root->getAttribute('height'));

        // Should have groups with transforms
        $groups = $merged->querySelectorAll('g');
        $this->assertCount(2, $groups);
    }

    public function testMergeStacked(): void
    {
        $doc1 = $this->createTestDocument('rect1', 100, 50);
        $doc2 = $this->createTestDocument('rect2', 150, 75);

        $merged = Document::merge([$doc1, $doc2], [
            'strategy' => MergeStrategy::STACKED,
            'spacing' => 10,
        ]);

        $root = $merged->getRootElement();
        $this->assertNotNull($root);

        // Width should be max width
        $this->assertEquals('150', $root->getAttribute('width'));

        // Height should be sum of heights + spacing
        $this->assertEquals('135', $root->getAttribute('height')); // 50 + 10 + 75

        // Should have groups with transforms
        $groups = $merged->querySelectorAll('g');
        $this->assertCount(2, $groups);
    }

    public function testMergeAsSymbols(): void
    {
        $doc1 = $this->createTestDocument('icon1');
        $doc2 = $this->createTestDocument('icon2');
        $doc3 = $this->createTestDocument('icon3');

        $merged = Document::merge([$doc1, $doc2, $doc3], [
            'strategy' => MergeStrategy::SYMBOLS,
            'symbol_ids' => ['home', 'user', 'settings'],
        ]);

        $root = $merged->getRootElement();
        $this->assertNotNull($root);

        // Should have a defs element
        $defs = $merged->querySelector('defs');
        $this->assertNotNull($defs);

        // Should have 3 symbols
        $symbols = $merged->querySelectorAll('symbol');
        $this->assertCount(3, $symbols);

        // Check symbol IDs
        $symbolIds = $symbols->pluck('id');
        $this->assertEquals('home', $symbolIds[0]);
        $this->assertEquals('user', $symbolIds[1]);
        $this->assertEquals('settings', $symbolIds[2]);
    }

    public function testMergeAsSymbolsWithDefaultIds(): void
    {
        $doc1 = $this->createTestDocument('icon1');
        $doc2 = $this->createTestDocument('icon2');

        $merged = Document::merge([$doc1, $doc2], [
            'strategy' => MergeStrategy::SYMBOLS,
        ]);

        $symbols = $merged->querySelectorAll('symbol');
        $this->assertCount(2, $symbols);

        // Should have default IDs
        $symbolIds = $symbols->pluck('id');
        $this->assertEquals('symbol-0', $symbolIds[0]);
        $this->assertEquals('symbol-1', $symbolIds[1]);
    }

    public function testMergeAsGrid(): void
    {
        $docs = [];
        for ($i = 0; $i < 9; ++$i) {
            $docs[] = $this->createTestDocument("rect{$i}", 50, 50);
        }

        $merged = Document::merge($docs, [
            'strategy' => MergeStrategy::GRID,
            'columns' => 3,
            'spacing' => 10,
        ]);

        $root = $merged->getRootElement();
        $this->assertNotNull($root);

        // Should have 9 groups (one for each doc)
        $groups = $merged->querySelectorAll('g');
        $this->assertCount(9, $groups);

        // Check some transforms
        $this->assertEquals('translate(0, 0)', $groups->get(0)->getAttribute('transform'));
        $this->assertEquals('translate(60, 0)', $groups->get(1)->getAttribute('transform'));
        $this->assertEquals('translate(120, 0)', $groups->get(2)->getAttribute('transform'));
        $this->assertEquals('translate(0, 60)', $groups->get(3)->getAttribute('transform'));
    }

    public function testMergeEmptyDocuments(): void
    {
        $merged = Document::merge([]);

        $this->assertInstanceOf(Document::class, $merged);
        $this->assertNull($merged->getRootElement());
    }

    public function testAppendMethod(): void
    {
        $doc1 = $this->createTestDocument('rect1');
        $doc2 = $this->createTestDocument('rect2');

        $doc1->append($doc2);

        $rects = $doc1->querySelectorAll('rect');
        $this->assertCount(2, $rects);
    }

    public function testMergePreservesAttributes(): void
    {
        $doc1 = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('id', 'test');
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('fill', 'red');
        $rect->addClass('shape highlighted');
        $doc1->getRootElement()->appendChild($rect);

        $doc2 = Document::create();

        $merged = Document::merge([$doc1, $doc2], [
            'strategy' => MergeStrategy::APPEND,
        ]);

        $importedRect = $merged->querySelector('#test');
        $this->assertNotNull($importedRect);
        $this->assertEquals('10', $importedRect->getAttribute('x'));
        $this->assertEquals('20', $importedRect->getAttribute('y'));
        $this->assertEquals('100', $importedRect->getAttribute('width'));
        $this->assertEquals('red', $importedRect->getAttribute('fill'));
        $this->assertTrue($importedRect->hasClass('shape'));
        $this->assertTrue($importedRect->hasClass('highlighted'));
    }

    public function testMergeWithViewBox(): void
    {
        $doc1 = Document::create(100, 100);
        $doc1->getRootElement()->setAttribute('viewBox', '0 0 100 100');
        $rect = new RectElement();
        $rect->setAttribute('id', 'rect1');
        $doc1->getRootElement()->appendChild($rect);

        $doc2 = Document::create(200, 200);
        $doc2->getRootElement()->setAttribute('viewBox', '0 0 200 200');
        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');
        $doc2->getRootElement()->appendChild($circle);

        $merged = Document::merge([$doc1, $doc2], [
            'strategy' => MergeStrategy::SYMBOLS,
            'symbol_ids' => ['icon1', 'icon2'],
        ]);

        $symbols = $merged->querySelectorAll('symbol');
        $this->assertCount(2, $symbols);

        // ViewBox should be preserved in symbols
        $this->assertEquals('0 0 100 100', $symbols->get(0)->getAttribute('viewBox'));
        $this->assertEquals('0 0 200 200', $symbols->get(1)->getAttribute('viewBox'));
    }
}
