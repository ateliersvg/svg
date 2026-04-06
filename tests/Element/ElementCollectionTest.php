<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\ElementCollection;
use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ElementCollection::class)]
final class ElementCollectionTest extends TestCase
{
    private function createTestElements(): array
    {
        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'rect1');
        $rect1->setAttribute('width', '100');
        $rect1->setAttribute('fill', 'red');
        $rect1->addClass('shape large');

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'rect2');
        $rect2->setAttribute('width', '50');
        $rect2->setAttribute('fill', 'blue');
        $rect2->addClass('shape small');

        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');
        $circle->setAttribute('r', '25');
        $circle->setAttribute('fill', 'green');
        $circle->addClass('shape medium');

        return [$rect1, $rect2, $circle];
    }

    public function testConstructor(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $this->assertCount(3, $collection);
        $this->assertFalse($collection->isEmpty());
    }

    public function testEmptyCollection(): void
    {
        $collection = new ElementCollection([]);

        $this->assertCount(0, $collection);
        $this->assertTrue($collection->isEmpty());
    }

    public function testGet(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $this->assertSame($elements[0], $collection->get(0));
        $this->assertSame($elements[1], $collection->get(1));
        $this->assertSame($elements[2], $collection->get(2));
        $this->assertNull($collection->get(999));
    }

    public function testFirstAndLast(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $this->assertSame($elements[0], $collection->first());
        $this->assertSame($elements[2], $collection->last());
    }

    public function testFirstAndLastOnEmpty(): void
    {
        $collection = new ElementCollection([]);

        $this->assertNull($collection->first());
        $this->assertNull($collection->last());
    }

    public function testToArray(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $this->assertSame($elements, $collection->toArray());
    }

    public function testIteration(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $iterated = [];
        foreach ($collection as $element) {
            $iterated[] = $element;
        }

        $this->assertSame($elements, $iterated);
    }

    public function testFilter(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $filtered = $collection->filter(fn ($el) => 'rect' === $el->getTagName());

        $this->assertCount(2, $filtered);
        $this->assertSame($elements[0], $filtered->get(0));
        $this->assertSame($elements[1], $filtered->get(1));
    }

    public function testReject(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $rejected = $collection->reject(fn ($el) => 'rect' === $el->getTagName());

        $this->assertCount(1, $rejected);
        $this->assertSame($elements[2], $rejected->get(0));
    }

    public function testWhereEquals(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $filtered = $collection->where('fill', '=', 'red');

        $this->assertCount(1, $filtered);
        $this->assertSame($elements[0], $filtered->first());
    }

    public function testWhereNotEquals(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $filtered = $collection->where('fill', '!=', 'red');

        $this->assertCount(2, $filtered);
    }

    public function testWhereGreaterThan(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $filtered = $collection->where('width', '>', '75');

        $this->assertCount(1, $filtered);
        $this->assertSame($elements[0], $filtered->first());
    }

    public function testWhereLessThan(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $filtered = $collection->where('width', '<', '75');

        $this->assertCount(1, $filtered);
        $this->assertSame($elements[1], $filtered->first());
    }

    public function testWhereContains(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $filtered = $collection->where('fill', 'contains', 'e');

        $this->assertCount(3, $filtered); // red, blue, green all contain 'e'
    }

    public function testOfType(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $rects = $collection->ofType('rect');

        $this->assertCount(2, $rects);
    }

    public function testWithClass(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $large = $collection->withClass('large');

        $this->assertCount(1, $large);
        $this->assertSame($elements[0], $large->first());
    }

    public function testWithAttribute(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $withWidth = $collection->withAttribute('width');

        $this->assertCount(2, $withWidth); // Only rects have width
    }

    public function testMap(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $ids = $collection->map(fn ($el) => $el->getAttribute('id'));

        $this->assertEquals(['rect1', 'rect2', 'circle1'], $ids);
    }

    public function testPluck(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $fills = $collection->pluck('fill');

        $this->assertEquals(['red', 'blue', 'green'], $fills);
    }

    public function testReduce(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $sum = $collection->reduce(function ($carry, $el) {
            $width = $el->getAttribute('width');

            return $carry + ($width ? (float) $width : 0);
        }, 0);

        $this->assertEquals(150, $sum); // 100 + 50
    }

    public function testEach(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $count = 0;
        $collection->each(function ($el, $index) use (&$count) {
            ++$count;
        });

        $this->assertEquals(3, $count);
    }

    public function testSetAttribute(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->setAttribute('opacity', '0.5');

        foreach ($elements as $element) {
            $this->assertEquals('0.5', $element->getAttribute('opacity'));
        }
    }

    public function testAttr(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->attr('opacity', '0.8');

        foreach ($elements as $element) {
            $this->assertEquals('0.8', $element->getAttribute('opacity'));
        }
    }

    public function testRemoveAttribute(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->removeAttribute('fill');

        foreach ($elements as $element) {
            $this->assertNull($element->getAttribute('fill'));
        }
    }

    public function testAddClass(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->addClass('highlighted');

        foreach ($elements as $element) {
            $this->assertTrue($element->hasClass('highlighted'));
        }
    }

    public function testRemoveClass(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->removeClass('shape');

        foreach ($elements as $element) {
            $this->assertFalse($element->hasClass('shape'));
        }
    }

    public function testToggleClass(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        // All have 'shape', so toggle removes it
        $collection->toggleClass('shape');
        foreach ($elements as $element) {
            $this->assertFalse($element->hasClass('shape'));
        }

        // Toggle again adds it back
        $collection->toggleClass('shape');
        foreach ($elements as $element) {
            $this->assertTrue($element->hasClass('shape'));
        }
    }

    public function testFill(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->fill('yellow');

        foreach ($elements as $element) {
            $this->assertEquals('yellow', $element->getAttribute('fill'));
        }
    }

    public function testStroke(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->stroke('black');

        foreach ($elements as $element) {
            $this->assertEquals('black', $element->getAttribute('stroke'));
        }
    }

    public function testStrokeWidth(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->strokeWidth(2);

        foreach ($elements as $element) {
            $this->assertEquals('2', $element->getAttribute('stroke-width'));
        }
    }

    public function testOpacity(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->opacity(0.75);

        foreach ($elements as $element) {
            $this->assertEquals('0.75', $element->getAttribute('opacity'));
        }
    }

    public function testTransform(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $collection->transform('translate(10, 20)');

        foreach ($elements as $element) {
            $this->assertEquals('translate(10, 20)', $element->getAttribute('transform'));
        }
    }

    public function testRemove(): void
    {
        $group = new GroupElement();
        $elements = $this->createTestElements();

        foreach ($elements as $element) {
            $group->appendChild($element);
        }

        $this->assertCount(3, $group->getChildren());

        $collection = new ElementCollection($elements);
        $collection->remove();

        $this->assertCount(0, $group->getChildren());
    }

    public function testClone(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $cloned = $collection->clone();

        $this->assertCount(3, $cloned);
        $this->assertNotSame($elements[0], $cloned->get(0));
        $this->assertEquals($elements[0]->getAttribute('id'), $cloned->get(0)->getAttribute('id'));
    }

    public function testCloneDeep(): void
    {
        $group = new GroupElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'child');
        $group->appendChild($rect);

        $collection = new ElementCollection([$group]);
        $cloned = $collection->cloneDeep();

        $this->assertCount(1, $cloned);
        $clonedGroup = $cloned->first();
        $this->assertNotSame($group, $clonedGroup);
        $this->assertCount(1, $clonedGroup->getChildren());
    }

    public function testWhereGreaterThanOrEqual(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $filtered = $collection->where('width', '>=', '100');

        $this->assertCount(1, $filtered);
        $this->assertSame($elements[0], $filtered->first());
    }

    public function testWhereLessThanOrEqual(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $filtered = $collection->where('width', '<=', '50');

        $this->assertCount(1, $filtered);
        $this->assertSame($elements[1], $filtered->first());
    }

    public function testCloneDeepWithNonContainerElements(): void
    {
        $image = new ImageElement();
        $image->setAttribute('id', 'simple');

        $collection = new ElementCollection([$image]);
        $cloned = $collection->cloneDeep();

        $this->assertCount(1, $cloned);
        $clonedImage = $cloned->first();
        $this->assertInstanceOf(ImageElement::class, $clonedImage);
        $this->assertNotSame($image, $clonedImage);
        $this->assertSame('simple', $clonedImage->getAttribute('id'));
    }

    public function testFillOpacity(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $result = $collection->fillOpacity(0.5);

        $this->assertSame($collection, $result);
        foreach ($elements as $element) {
            $this->assertEquals('0.5', $element->getAttribute('fill-opacity'));
        }
    }

    public function testStrokeOpacity(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $result = $collection->strokeOpacity(0.3);

        $this->assertSame($collection, $result);
        foreach ($elements as $element) {
            $this->assertEquals('0.3', $element->getAttribute('stroke-opacity'));
        }
    }

    public function testDisplay(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $result = $collection->display('none');

        $this->assertSame($collection, $result);
        foreach ($elements as $element) {
            $this->assertEquals('none', $element->getAttribute('display'));
        }
    }

    public function testVisibility(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $result = $collection->visibility('hidden');

        $this->assertSame($collection, $result);
        foreach ($elements as $element) {
            $this->assertEquals('hidden', $element->getAttribute('visibility'));
        }
    }

    public function testCursor(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $result = $collection->cursor('pointer');

        $this->assertSame($collection, $result);
        foreach ($elements as $element) {
            $this->assertEquals('pointer', $element->getAttribute('cursor'));
        }
    }

    public function testPointerEvents(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $result = $collection->pointerEvents('none');

        $this->assertSame($collection, $result);
        foreach ($elements as $element) {
            $this->assertEquals('none', $element->getAttribute('pointer-events'));
        }
    }

    public function testChaining(): void
    {
        $elements = $this->createTestElements();
        $collection = new ElementCollection($elements);

        $result = $collection
            ->ofType('rect')
            ->where('width', '>', '50')
            ->addClass('large-rect')
            ->fill('purple')
            ->opacity(0.9);

        $this->assertCount(1, $result);
        $element = $result->first();
        $this->assertTrue($element->hasClass('large-rect'));
        $this->assertEquals('purple', $element->getAttribute('fill'));
        $this->assertEquals('0.9', $element->getAttribute('opacity'));
    }
}
