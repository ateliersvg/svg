<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Selector;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
final class QuerySelectorTest extends TestCase
{
    private function createTestDocument(): Document
    {
        $svg = new SvgElement();
        $svg->setAttribute('width', '800');
        $svg->setAttribute('height', '600');

        // Create test structure
        $group1 = new GroupElement();
        $group1->setAttribute('id', 'layer1');
        $group1->addClass('layer');

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'rect1');
        $rect1->setAttribute('fill', 'red');
        $rect1->addClass('shape highlighted');

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'rect2');
        $rect2->setAttribute('fill', 'blue');
        $rect2->addClass('shape');

        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');
        $circle->setAttribute('fill', 'red');
        $circle->addClass('shape highlighted');

        $group1->appendChild($rect1);
        $group1->appendChild($rect2);

        $group2 = new GroupElement();
        $group2->setAttribute('id', 'layer2');
        $group2->addClass('layer');
        $group2->appendChild($circle);

        $svg->appendChild($group1);
        $svg->appendChild($group2);

        $doc = new Document($svg);

        return $doc;
    }

    public function testQuerySelectorById(): void
    {
        $doc = $this->createTestDocument();

        $element = $doc->querySelector('#rect1');

        $this->assertNotNull($element);
        $this->assertEquals('rect', $element->getTagName());
        $this->assertEquals('rect1', $element->getAttribute('id'));
    }

    public function testQuerySelectorByIdNotFound(): void
    {
        $doc = $this->createTestDocument();

        $element = $doc->querySelector('#nonexistent');

        $this->assertNull($element);
    }

    public function testQuerySelectorByTag(): void
    {
        $doc = $this->createTestDocument();

        $element = $doc->querySelector('rect');

        $this->assertNotNull($element);
        $this->assertEquals('rect', $element->getTagName());
        $this->assertEquals('rect1', $element->getAttribute('id')); // First rect
    }

    public function testQuerySelectorByClass(): void
    {
        $doc = $this->createTestDocument();

        $element = $doc->querySelector('.highlighted');

        $this->assertNotNull($element);
        $this->assertTrue($element->hasClass('highlighted'));
    }

    public function testQuerySelectorByAttribute(): void
    {
        $doc = $this->createTestDocument();

        $element = $doc->querySelector('[fill="blue"]');

        $this->assertNotNull($element);
        $this->assertEquals('blue', $element->getAttribute('fill'));
        $this->assertEquals('rect2', $element->getAttribute('id'));
    }

    public function testQuerySelectorUniversal(): void
    {
        $doc = $this->createTestDocument();

        $element = $doc->querySelector('*');

        $this->assertNotNull($element);
        // Should match the SVG root or first child
    }

    public function testQuerySelectorAllById(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('#rect1');

        $this->assertCount(1, $elements);
        $this->assertEquals('rect1', $elements->first()->getAttribute('id'));
    }

    public function testQuerySelectorAllByTag(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('rect');

        $this->assertCount(2, $elements);
        $this->assertEquals('rect', $elements->get(0)->getTagName());
        $this->assertEquals('rect', $elements->get(1)->getTagName());
    }

    public function testQuerySelectorAllByClass(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('.shape');

        $this->assertCount(3, $elements); // 2 rects + 1 circle
    }

    public function testQuerySelectorAllByClassHighlighted(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('.highlighted');

        $this->assertCount(2, $elements); // rect1 and circle1
    }

    public function testQuerySelectorAllByAttribute(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('[fill="red"]');

        $this->assertCount(2, $elements); // rect1 and circle1
    }

    public function testQuerySelectorAllAttributeExists(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('[fill]');

        $this->assertCount(3, $elements); // All shapes have fill
    }

    public function testQuerySelectorAllUniversal(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('*');

        $this->assertGreaterThan(0, $elements->count());
    }

    public function testQuerySelectorAllEmpty(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->querySelectorAll('.nonexistent');

        $this->assertCount(0, $elements);
        $this->assertTrue($elements->isEmpty());
    }

    public function testFindByTag(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->findByTag('circle');

        $this->assertCount(1, $elements);
        $this->assertEquals('circle', $elements->first()->getTagName());
    }

    public function testFindByClass(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->findByClass('layer');

        $this->assertCount(2, $elements);
    }

    public function testSelect(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->select('rect');

        $this->assertCount(2, $elements);
    }

    public function testSelectWithChaining(): void
    {
        $doc = $this->createTestDocument();

        $elements = $doc->select('.shape')
            ->withClass('highlighted')
            ->fill('yellow');

        $this->assertCount(2, $elements);

        foreach ($elements as $element) {
            $this->assertEquals('yellow', $element->getAttribute('fill'));
        }
    }

    public function testQuerySelectorOnEmptyDocument(): void
    {
        $doc = new Document();

        $element = $doc->querySelector('#test');
        $this->assertNull($element);

        $elements = $doc->querySelectorAll('*');
        $this->assertCount(0, $elements);
    }
}
