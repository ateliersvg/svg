<?php

namespace Atelier\Svg\Tests\Layout;

use Atelier\Svg\Document;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Layout\LayoutBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LayoutBuilder::class)]
final class LayoutBuilderTest extends TestCase
{
    public function testPositionAt(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $circle = $doc->circle(cx: 50, cy: 50, r: 20);
        $group->appendChild($circle);

        $group->layout()->positionAt($circle, x: 100, y: 100, anchor: 'top-left');

        // Check that transform was applied
        $transform = $circle->getAttribute('transform');
        $this->assertNotNull($transform);
    }

    public function testCenter(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $rect = $doc->rect(x: 0, y: 0, width: 50, height: 30);
        $group->appendChild($rect);

        $group->layout()->center($rect);

        // Element should have transform applied
        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
    }

    public function testAlignLeft(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $rect = $doc->rect(x: 100, y: 100, width: 50, height: 30);
        $group->appendChild($rect);

        $group->layout()->align($rect, 'left', offset: 10);

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
    }

    public function testDistributeHorizontal(): void
    {
        $doc = Document::create(width: 600, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 600);
        $group->setAttribute('height', 300);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'horizontal', gap: 10);

        // All elements should have transforms applied
        foreach ($elements as $element) {
            $transform = $element->getAttribute('transform');
            $this->assertNotNull($transform);
        }
    }

    public function testDistributeVertical(): void
    {
        $doc = Document::create(width: 300, height: 600);
        $group = $doc->g();
        $group->setAttribute('width', 300);
        $group->setAttribute('height', 600);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'vertical', gap: 10);

        foreach ($elements as $element) {
            $transform = $element->getAttribute('transform');
            $this->assertNotNull($transform);
        }
    }

    public function testStackVertical(): void
    {
        $doc = Document::create(width: 300, height: 600);
        $group = $doc->g();
        $group->setAttribute('width', 300);
        $group->setAttribute('height', 600);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 30);
        }

        $group->layout()->stack($elements, direction: 'vertical', gap: 10, align: 'center');

        foreach ($elements as $element) {
            $transform = $element->getAttribute('transform');
            $this->assertNotNull($transform);
        }
    }

    public function testStackHorizontal(): void
    {
        $doc = Document::create(width: 600, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 600);
        $group->setAttribute('height', 300);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 30);
        }

        $group->layout()->stack($elements, direction: 'horizontal', gap: 10, align: 'center');

        foreach ($elements as $element) {
            $transform = $element->getAttribute('transform');
            $this->assertNotNull($transform);
        }
    }

    public function testGrid(): void
    {
        $doc = Document::create(width: 400, height: 400);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 400);

        $elements = [];
        for ($i = 0; $i < 9; ++$i) {
            $elements[] = $doc->circle(cx: 0, cy: 0, r: 15);
        }

        $group->layout()->grid($elements, columns: 3, gapX: 10, gapY: 10);

        foreach ($elements as $element) {
            $transform = $element->getAttribute('transform');
            $this->assertNotNull($transform);
        }
    }

    // --- center() edge cases ---

    public function testCenterHorizontalOnly(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $rect = $doc->rect(x: 0, y: 50, width: 50, height: 30);
        $group->appendChild($rect);

        $group->layout()->center($rect, horizontal: true, vertical: false);

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
    }

    public function testCenterVerticalOnly(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $rect = $doc->rect(x: 50, y: 0, width: 50, height: 30);
        $group->appendChild($rect);

        $group->layout()->center($rect, horizontal: false, vertical: true);

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
    }

    // --- align() additional directions ---

    public function testAlignRight(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $rect = $doc->rect(x: 0, y: 0, width: 50, height: 30);
        $group->appendChild($rect);

        $group->layout()->align($rect, 'right', offset: 10);

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
    }

    public function testAlignTop(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $rect = $doc->rect(x: 50, y: 50, width: 50, height: 30);
        $group->appendChild($rect);

        $group->layout()->align($rect, 'top', offset: 5);

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
    }

    public function testAlignBottom(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $rect = $doc->rect(x: 50, y: 50, width: 50, height: 30);
        $group->appendChild($rect);

        $group->layout()->align($rect, 'bottom', offset: 5);

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
    }

    public function testAlignCenter(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $rect = $doc->rect(x: 0, y: 0, width: 50, height: 30);
        $group->appendChild($rect);

        $group->layout()->align($rect, 'center');

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
    }

    public function testAlignInvalidThrowsException(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $rect = $doc->rect(x: 0, y: 0, width: 50, height: 30);
        $group->appendChild($rect);

        $this->expectException(InvalidArgumentException::class);
        $group->layout()->align($rect, 'invalid');
    }

    // --- distribute() edge cases ---

    public function testDistributeEmpty(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $result = $group->layout()->distribute([]);
        $this->assertInstanceOf(LayoutBuilder::class, $result);
    }

    public function testDistributeHorizontalWithAlign(): void
    {
        $doc = Document::create(width: 600, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 600);
        $group->setAttribute('height', 300);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'horizontal', gap: 10, align: 'top');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testDistributeHorizontalWithCenterAlign(): void
    {
        $doc = Document::create(width: 600, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 600);
        $group->setAttribute('height', 300);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'horizontal', gap: 10, align: 'center');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testDistributeHorizontalWithBottomAlign(): void
    {
        $doc = Document::create(width: 600, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 600);
        $group->setAttribute('height', 300);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'horizontal', gap: 10, align: 'bottom');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testDistributeVerticalWithAlign(): void
    {
        $doc = Document::create(width: 300, height: 600);
        $group = $doc->g();
        $group->setAttribute('width', 300);
        $group->setAttribute('height', 600);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'vertical', gap: 10, align: 'center');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testDistributeVerticalWithRightAlign(): void
    {
        $doc = Document::create(width: 300, height: 600);
        $group = $doc->g();
        $group->setAttribute('width', 300);
        $group->setAttribute('height', 600);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'vertical', gap: 10, align: 'right');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    // --- stack() alignment variations ---

    public function testStackVerticalWithLeftAlign(): void
    {
        $doc = Document::create(width: 300, height: 600);
        $group = $doc->g();
        $group->setAttribute('width', 300);
        $group->setAttribute('height', 600);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 30);
        }

        $group->layout()->stack($elements, direction: 'vertical', gap: 10, align: 'left');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testStackVerticalWithRightAlign(): void
    {
        $doc = Document::create(width: 300, height: 600);
        $group = $doc->g();
        $group->setAttribute('width', 300);
        $group->setAttribute('height', 600);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 30);
        }

        $group->layout()->stack($elements, direction: 'vertical', gap: 10, align: 'right');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testStackHorizontalWithTopAlign(): void
    {
        $doc = Document::create(width: 600, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 600);
        $group->setAttribute('height', 300);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 30);
        }

        $group->layout()->stack($elements, direction: 'horizontal', gap: 10, align: 'top');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testStackHorizontalWithBottomAlign(): void
    {
        $doc = Document::create(width: 600, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 600);
        $group->setAttribute('height', 300);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 30);
        }

        $group->layout()->stack($elements, direction: 'horizontal', gap: 10, align: 'bottom');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testStackEmpty(): void
    {
        $doc = Document::create(width: 400, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 300);

        $result = $group->layout()->stack([]);
        $this->assertInstanceOf(LayoutBuilder::class, $result);
    }

    // --- grid() alignment and edge cases ---

    public function testGridWithCenterAlignment(): void
    {
        $doc = Document::create(width: 400, height: 400);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 400);

        $elements = [];
        for ($i = 0; $i < 4; ++$i) {
            $elements[] = $doc->circle(cx: 0, cy: 0, r: 15);
        }

        $group->layout()->grid($elements, columns: 2, gapX: 10, gapY: 10, alignH: 'center', alignV: 'center');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testGridWithRightBottomAlignment(): void
    {
        $doc = Document::create(width: 400, height: 400);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 400);

        $elements = [];
        for ($i = 0; $i < 4; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 20, height: 20);
        }

        $group->layout()->grid($elements, columns: 2, gapX: 5, gapY: 5, alignH: 'right', alignV: 'bottom');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testGridWithEmptyElements(): void
    {
        $doc = Document::create(width: 400, height: 400);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 400);

        $result = $group->layout()->grid([], columns: 3);
        $this->assertInstanceOf(LayoutBuilder::class, $result);
    }

    public function testGridWithZeroColumns(): void
    {
        $doc = Document::create(width: 400, height: 400);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 400);

        $elements = [$doc->rect(x: 0, y: 0, width: 20, height: 20)];

        $result = $group->layout()->grid($elements, columns: 0);
        $this->assertInstanceOf(LayoutBuilder::class, $result);
    }

    public function testStackVerticalWithDefaultAlign(): void
    {
        $doc = Document::create(width: 300, height: 600);
        $group = $doc->g();
        $group->setAttribute('width', 300);
        $group->setAttribute('height', 600);

        $elements = [];
        for ($i = 0; $i < 2; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 30);
        }

        $group->layout()->stack($elements, direction: 'vertical', gap: 5, align: 'unknown');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testStackHorizontalWithDefaultAlign(): void
    {
        $doc = Document::create(width: 600, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 600);
        $group->setAttribute('height', 300);

        $elements = [];
        for ($i = 0; $i < 2; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 30);
        }

        $group->layout()->stack($elements, direction: 'horizontal', gap: 5, align: 'unknown');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testDistributeVerticalWithLeftAlign(): void
    {
        $doc = Document::create(width: 300, height: 600);
        $group = $doc->g();
        $group->setAttribute('width', 300);
        $group->setAttribute('height', 600);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'vertical', gap: 10, align: 'left');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testDistributeHorizontalWithDefaultAlign(): void
    {
        $doc = Document::create(width: 600, height: 300);
        $group = $doc->g();
        $group->setAttribute('width', 600);
        $group->setAttribute('height', 300);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'horizontal', gap: 10, align: 'unknown');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testDistributeVerticalWithDefaultAlign(): void
    {
        $doc = Document::create(width: 300, height: 600);
        $group = $doc->g();
        $group->setAttribute('width', 300);
        $group->setAttribute('height', 600);

        $elements = [];
        for ($i = 0; $i < 3; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 40, height: 40);
        }

        $group->layout()->distribute($elements, direction: 'vertical', gap: 10, align: 'unknown');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testGridWithDefaultAlignment(): void
    {
        $doc = Document::create(width: 400, height: 400);
        $group = $doc->g();
        $group->setAttribute('width', 400);
        $group->setAttribute('height', 400);

        $elements = [];
        for ($i = 0; $i < 4; ++$i) {
            $elements[] = $doc->rect(x: 0, y: 0, width: 20, height: 20);
        }

        $group->layout()->grid($elements, columns: 2, gapX: 5, gapY: 5, alignH: 'unknown', alignV: 'unknown');

        foreach ($elements as $element) {
            $this->assertNotNull($element->getAttribute('transform'));
        }
    }

    public function testGetContainerBBoxThrowsWhenContainerIsNotAbstractElement(): void
    {
        $container = new class implements \Atelier\Svg\Element\ContainerElementInterface {
            public function getTagName(): string
            {
                return 'g';
            }

            public function getAttribute(string $name): ?string
            {
                return null;
            }

            public function setAttribute(string $name, string|int|float $value): static
            {
                return $this;
            }

            public function removeAttribute(string $name): static
            {
                return $this;
            }

            public function hasAttribute(string $name): bool
            {
                return false;
            }

            public function getAttributes(): array
            {
                return [];
            }

            public function getParent(): ?\Atelier\Svg\Element\ElementInterface
            {
                return null;
            }

            public function setParent(?\Atelier\Svg\Element\ElementInterface $parent): static
            {
                return $this;
            }

            public function setId(string $id): static
            {
                return $this;
            }

            public function getId(): ?string
            {
                return null;
            }

            public function addClass(string $className): static
            {
                return $this;
            }

            public function removeClass(string $className): static
            {
                return $this;
            }

            public function hasClass(string $className): bool
            {
                return false;
            }

            public function toggleClass(string $className): static
            {
                return $this;
            }

            public function getClasses(): array
            {
                return [];
            }

            public function clone(): static
            {
                return clone $this;
            }

            public function appendChild(\Atelier\Svg\Element\ElementInterface $child): static
            {
                return $this;
            }

            public function removeChild(\Atelier\Svg\Element\ElementInterface $child): static
            {
                return $this;
            }

            public function getChildren(): array
            {
                return [];
            }

            public function hasChildren(): bool
            {
                return false;
            }

            public function getChildCount(): int
            {
                return 0;
            }

            public function clearChildren(): static
            {
                return $this;
            }

            public function cloneDeep(?callable $transform = null): static
            {
                return clone $this;
            }
        };

        $layout = new LayoutBuilder($container);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Container must be an AbstractElement');

        $doc = Document::create(width: 100, height: 100);
        $rect = $doc->rect(x: 0, y: 0, width: 50, height: 50);
        $layout->center($rect);
    }
}
