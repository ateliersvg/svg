<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\SortAttributesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SortAttributesPass::class)]
final class SortAttributesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new SortAttributesPass();

        $this->assertSame('sort-attributes', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new SortAttributesPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testSortAttributesAlphabetically(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        // Add attributes in random order
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('x', '10');
        $rect->setAttribute('height', '20');
        $rect->setAttribute('width', '30');
        $rect->setAttribute('y', '15');
        $rect->setAttribute('stroke', 'blue');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Check attributes are in alphabetical order
        $attributes = $rect->getAttributes();
        $attributeNames = array_keys($attributes);

        $this->assertSame(['fill', 'height', 'stroke', 'width', 'x', 'y'], $attributeNames);
    }

    public function testPriorityAttributesComesFirst(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        // Add attributes including id and class
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('x', '10');
        $rect->setAttribute('class', 'my-rect');
        $rect->setAttribute('height', '20');
        $rect->setAttribute('id', 'rect1');
        $rect->setAttribute('width', '30');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Check id and class come first, then alphabetical
        $attributes = $rect->getAttributes();
        $attributeNames = array_keys($attributes);

        $this->assertSame(['id', 'class', 'fill', 'height', 'width', 'x'], $attributeNames);
    }

    public function testCustomPriorityOrder(): void
    {
        $pass = new SortAttributesPass(priorityOrder: ['data-id', 'name', 'id']);
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('id', 'rect1');
        $rect->setAttribute('name', 'my-rect');
        $rect->setAttribute('x', '10');
        $rect->setAttribute('data-id', '123');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $attributes = $rect->getAttributes();
        $attributeNames = array_keys($attributes);

        // Custom priority order: data-id, name, id, then alphabetical
        $this->assertSame(['data-id', 'name', 'id', 'fill', 'x'], $attributeNames);
    }

    public function testEmptyPriorityOrder(): void
    {
        $pass = new SortAttributesPass(priorityOrder: []);
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        $rect->setAttribute('id', 'rect1');
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('x', '10');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $attributes = $rect->getAttributes();
        $attributeNames = array_keys($attributes);

        // Pure alphabetical order (no priority)
        $this->assertSame(['fill', 'id', 'x'], $attributeNames);
    }

    public function testHandleSingleAttribute(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        $rect->setAttribute('fill', 'red');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Single attribute should remain unchanged
        $attributes = $rect->getAttributes();
        $this->assertCount(1, $attributes);
        $this->assertSame('red', $rect->getAttribute('fill'));
    }

    public function testHandleNoAttributes(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // No attributes should remain no attributes
        $attributes = $rect->getAttributes();
        $this->assertCount(0, $attributes);
    }

    public function testSortAttributesInNestedElements(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $group = new GroupElement();
        $group->setAttribute('fill', 'red');
        $group->setAttribute('id', 'group1');
        $group->setAttribute('class', 'my-group');

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('y', '10');
        $rect->setAttribute('x', '5');
        $rect->setAttribute('width', '20');

        $group->appendChild($rect);
        $svg->appendChild($group);

        $document = new Document($svg);

        $pass->optimize($document);

        // Check group attributes are sorted
        $groupAttrs = array_keys($group->getAttributes());
        $this->assertSame(['id', 'class', 'fill'], $groupAttrs);

        // Check rect attributes are sorted
        $rectAttrs = array_keys($rect->getAttributes());
        $this->assertSame(['width', 'x', 'y'], $rectAttrs);
    }

    public function testPreservesAttributeValues(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('x', '10');
        $rect->setAttribute('height', '20');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Values should be preserved
        $this->assertSame('red', $rect->getAttribute('fill'));
        $this->assertSame('10', $rect->getAttribute('x'));
        $this->assertSame('20', $rect->getAttribute('height'));
    }

    public function testDoesNotSortIfAlreadySorted(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        // Add attributes in alphabetical order
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('height', '20');
        $rect->setAttribute('width', '30');
        $rect->setAttribute('x', '10');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $originalAttributes = $rect->getAttributes();

        $pass->optimize($document);

        // Attributes should remain the same (no unnecessary changes)
        $this->assertSame($originalAttributes, $rect->getAttributes());
    }

    public function testSortNumericAndSpecialCharacters(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        $rect->setAttribute('z-index', '10');
        $rect->setAttribute('data-value', 'test');
        $rect->setAttribute('aria-label', 'Rectangle');
        $rect->setAttribute('2d-transform', 'scale');
        $rect->setAttribute('x', '5');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $attributes = $rect->getAttributes();
        $attributeNames = array_keys($attributes);

        // Alphabetical order with special characters
        $this->assertSame(['2d-transform', 'aria-label', 'data-value', 'x', 'z-index'], $attributeNames);
    }

    public function testSortNamespacedAttributes(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $use = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('use');
            }
        };

        $use->setAttribute('xlink:href', '#icon');
        $use->setAttribute('href', '#icon2');
        $use->setAttribute('x', '10');
        $use->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

        $svg->appendChild($use);
        $document = new Document($svg);

        $pass->optimize($document);

        $attributes = $use->getAttributes();
        $attributeNames = array_keys($attributes);

        // Alphabetical order including namespaced attributes
        $this->assertSame(['href', 'x', 'xlink:href', 'xmlns:xlink'], $attributeNames);
    }

    public function testOnlyPriorityAttributesPresent(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        // Only priority attributes
        $rect->setAttribute('class', 'my-rect');
        $rect->setAttribute('id', 'rect1');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $attributes = $rect->getAttributes();
        $attributeNames = array_keys($attributes);

        // Should be in priority order
        $this->assertSame(['id', 'class'], $attributeNames);
    }

    public function testComplexRealWorldScenario(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };

        // Real-world path with many attributes
        $path->setAttribute('d', 'M10 20 L30 40');
        $path->setAttribute('stroke-width', '2');
        $path->setAttribute('fill', 'none');
        $path->setAttribute('stroke', 'black');
        $path->setAttribute('stroke-linecap', 'round');
        $path->setAttribute('stroke-linejoin', 'miter');
        $path->setAttribute('id', 'path1');
        $path->setAttribute('class', 'main-path');
        $path->setAttribute('opacity', '0.8');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $attributes = $path->getAttributes();
        $attributeNames = array_keys($attributes);

        // Priority first, then alphabetical
        $expected = [
            'id',
            'class',
            'd',
            'fill',
            'opacity',
            'stroke',
            'stroke-linecap',
            'stroke-linejoin',
            'stroke-width',
        ];

        $this->assertSame($expected, $attributeNames);
    }

    public function testHandleMultipleElementsWithDifferentAttributeCount(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect1 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect1->setAttribute('x', '10');

        $rect2 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect2->setAttribute('y', '20');
        $rect2->setAttribute('x', '10');
        $rect2->setAttribute('width', '30');
        $rect2->setAttribute('height', '40');
        $rect2->setAttribute('fill', 'red');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);

        $pass->optimize($document);

        // rect1 should have 1 attribute
        $this->assertCount(1, $rect1->getAttributes());

        // rect2 should have sorted attributes
        $rect2Attrs = array_keys($rect2->getAttributes());
        $this->assertSame(['fill', 'height', 'width', 'x', 'y'], $rect2Attrs);
    }

    public function testAttributesWithSamePrefix(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        // Attributes with same prefix
        $rect->setAttribute('stroke', 'black');
        $rect->setAttribute('stroke-width', '2');
        $rect->setAttribute('stroke-opacity', '0.5');
        $rect->setAttribute('stroke-linecap', 'round');
        $rect->setAttribute('stroke-dasharray', '5,5');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $attributes = $rect->getAttributes();
        $attributeNames = array_keys($attributes);

        // Should be sorted alphabetically
        $this->assertSame([
            'stroke',
            'stroke-dasharray',
            'stroke-linecap',
            'stroke-opacity',
            'stroke-width',
        ], $attributeNames);
    }

    public function testPreserveCaseSensitivity(): void
    {
        $pass = new SortAttributesPass();
        $svg = new SvgElement();

        $element = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('foreignObject');
            }
        };

        // Mix of case-sensitive attributes
        $element->setAttribute('viewBox', '0 0 100 100');
        $element->setAttribute('preserveAspectRatio', 'xMidYMid');
        $element->setAttribute('x', '10');

        $svg->appendChild($element);
        $document = new Document($svg);

        $pass->optimize($document);

        // Values should preserve case
        $this->assertSame('xMidYMid', $element->getAttribute('preserveAspectRatio'));
        $this->assertSame('0 0 100 100', $element->getAttribute('viewBox'));
    }
}
