<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Visitor;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Visitor\CallbackVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CallbackVisitor::class)]
final class CallbackVisitorTest extends TestCase
{
    public function testVisitExecutesCallback(): void
    {
        $called = false;
        $callback = function (ElementInterface $element) use (&$called): bool {
            $called = true;

            return true;
        };

        $visitor = new CallbackVisitor($callback);
        $element = new PathElement();

        $visitor->visit($element);

        $this->assertTrue($called);
    }

    public function testVisitPassesElementToCallback(): void
    {
        $receivedElement = null;
        $callback = function (ElementInterface $element) use (&$receivedElement): bool {
            $receivedElement = $element;

            return true;
        };

        $visitor = new CallbackVisitor($callback);
        $element = new CircleElement();

        $visitor->visit($element);

        $this->assertSame($element, $receivedElement);
    }

    public function testVisitReturnsCallbackResult(): void
    {
        $callback = (fn (ElementInterface $element): bool => false);

        $visitor = new CallbackVisitor($callback);
        $element = new PathElement();

        $result = $visitor->visit($element);

        $this->assertFalse($result);
    }

    public function testVisitReturnsTrueWhenCallbackReturnsTrue(): void
    {
        $callback = (fn (ElementInterface $element): bool => true);

        $visitor = new CallbackVisitor($callback);
        $element = new PathElement();

        $result = $visitor->visit($element);

        $this->assertTrue($result);
    }

    public function testCallbackCanAccessElementAttributes(): void
    {
        $capturedId = null;
        $callback = function (ElementInterface $element) use (&$capturedId): bool {
            $capturedId = $element->getAttribute('id');

            return true;
        };

        $visitor = new CallbackVisitor($callback);
        $element = new RectElement();
        $element->setAttribute('id', 'test-rect');

        $visitor->visit($element);

        $this->assertSame('test-rect', $capturedId);
    }

    public function testCallbackCanModifyElement(): void
    {
        $callback = function (ElementInterface $element): bool {
            $element->setAttribute('visited', 'true');

            return true;
        };

        $visitor = new CallbackVisitor($callback);
        $element = new PathElement();

        $visitor->visit($element);

        $this->assertSame('true', $element->getAttribute('visited'));
    }

    public function testCallbackCanAccessElementType(): void
    {
        $elementTypes = [];
        $callback = function (ElementInterface $element) use (&$elementTypes): bool {
            $elementTypes[] = $element->getTagName();

            return true;
        };

        $visitor = new CallbackVisitor($callback);

        $visitor->visit(new PathElement());
        $visitor->visit(new CircleElement());
        $visitor->visit(new RectElement());

        $this->assertSame(['path', 'circle', 'rect'], $elementTypes);
    }

    public function testCallbackCalledForEachVisit(): void
    {
        $callCount = 0;
        $callback = function (ElementInterface $element) use (&$callCount): bool {
            ++$callCount;

            return true;
        };

        $visitor = new CallbackVisitor($callback);

        $visitor->visit(new PathElement());
        $visitor->visit(new CircleElement());
        $visitor->visit(new RectElement());

        $this->assertSame(3, $callCount);
    }

    public function testCallbackCanReturnFalseToSignalStop(): void
    {
        $elements = [];
        $callback = function (ElementInterface $element) use (&$elements): bool {
            $elements[] = $element->getTagName();

            // Signal to stop after first element
            return false;
        };

        $visitor = new CallbackVisitor($callback);
        $result = $visitor->visit(new PathElement());

        $this->assertFalse($result);
        $this->assertSame(['path'], $elements);
    }

    public function testCallbackWithComplexLogic(): void
    {
        $processedElements = [];
        $callback = function (ElementInterface $element) use (&$processedElements): bool {
            $tagName = $element->getTagName();

            if ('circle' === $tagName) {
                $processedElements[] = 'circle-found';

                return false; // Stop on circles
            }

            $processedElements[] = $tagName;

            return true;
        };

        $visitor = new CallbackVisitor($callback);

        $visitor->visit(new PathElement());
        $result = $visitor->visit(new CircleElement());

        $this->assertFalse($result);
        $this->assertSame(['path', 'circle-found'], $processedElements);
    }

    public function testCallbackCanCollectData(): void
    {
        $collectedData = [];
        $callback = function (ElementInterface $element) use (&$collectedData): bool {
            $collectedData[] = [
                'tag' => $element->getTagName(),
                'hasId' => $element->hasAttribute('id'),
            ];

            return true;
        };

        $visitor = new CallbackVisitor($callback);

        $rect = new RectElement();
        $rect->setAttribute('id', 'my-rect');

        $circle = new CircleElement();

        $visitor->visit($rect);
        $visitor->visit($circle);

        $this->assertCount(2, $collectedData);
        $this->assertTrue($collectedData[0]['hasId']);
        $this->assertFalse($collectedData[1]['hasId']);
    }

    public function testCallbackCanPerformValidation(): void
    {
        $errors = [];
        $callback = function (ElementInterface $element) use (&$errors): bool {
            if ('rect' === $element->getTagName() && !$element->hasAttribute('width')) {
                $errors[] = 'Rect missing width';
            }

            return true;
        };

        $visitor = new CallbackVisitor($callback);

        $rectWithWidth = new RectElement();
        $rectWithWidth->setAttribute('width', '100');

        $rectWithoutWidth = new RectElement();

        $visitor->visit($rectWithWidth);
        $visitor->visit($rectWithoutWidth);

        $this->assertCount(1, $errors);
        $this->assertSame(['Rect missing width'], $errors);
    }

    public function testMultipleVisitorsWithDifferentCallbacks(): void
    {
        $counter1 = 0;
        $counter2 = 0;

        $visitor1 = new CallbackVisitor(function (ElementInterface $element) use (&$counter1): bool {
            ++$counter1;

            return true;
        });

        $visitor2 = new CallbackVisitor(function (ElementInterface $element) use (&$counter2): bool {
            $counter2 += 2;

            return true;
        });

        $element = new PathElement();

        $visitor1->visit($element);
        $visitor2->visit($element);

        $this->assertSame(1, $counter1);
        $this->assertSame(2, $counter2);
    }

    public function testCallbackWithTypeCasting(): void
    {
        $receivedElementClass = null;
        $callback = function (ElementInterface $element) use (&$receivedElementClass): bool {
            $receivedElementClass = $element::class;

            return true;
        };

        $visitor = new CallbackVisitor($callback);
        $visitor->visit(new CircleElement());

        $this->assertSame(CircleElement::class, $receivedElementClass);
    }

    public function testCallbackCanUseClosureScope(): void
    {
        $prefix = 'element-';
        $suffixes = [];

        $callback = function (ElementInterface $element) use ($prefix, &$suffixes): bool {
            $suffixes[] = $prefix.$element->getTagName();

            return true;
        };

        $visitor = new CallbackVisitor($callback);

        $visitor->visit(new PathElement());
        $visitor->visit(new CircleElement());

        $this->assertSame(['element-path', 'element-circle'], $suffixes);
    }
}
