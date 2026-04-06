<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Visitor;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Visitor\Traverser;
use Atelier\Svg\Visitor\TypedVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for TypedVisitor.
 */
#[CoversClass(TypedVisitor::class)]
final class TypedVisitorTest extends TestCase
{
    private Document $document;

    protected function setUp(): void
    {
        $this->document = Document::create(800, 600);
        $root = $this->document->getRootElement();

        // Add test elements
        $group = new GroupElement();
        $group->setAttribute('id', 'test-group');

        $rect = new RectElement();
        $rect->setAttribute('id', 'rect1');
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '10');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '80');

        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');
        $circle->setAttribute('cx', '200');
        $circle->setAttribute('cy', '50');
        $circle->setAttribute('r', '40');

        $path = new PathElement();
        $path->setAttribute('id', 'path1');
        $path->setAttribute('d', 'M 300 10 L 350 50 L 300 90 Z');

        $group->appendChild($rect);
        $group->appendChild($circle);
        $group->appendChild($path);
        $root->appendChild($group);
    }

    public function testVisitCircle(): void
    {
        $visitor = new class extends TypedVisitor {
            public array $visited = [];

            protected function visitCircle(CircleElement $circle): mixed
            {
                $this->visited[] = 'circle:'.$circle->getAttribute('id');

                return null;
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                return null;
            }
        };

        $traverser = new Traverser($visitor);
        $traverser->traverse($this->document->getRootElement());

        $this->assertContains('circle:circle1', $visitor->visited);
    }

    public function testVisitRect(): void
    {
        $visitor = new class extends TypedVisitor {
            public array $visited = [];

            protected function visitRect(RectElement $rect): mixed
            {
                $this->visited[] = 'rect:'.$rect->getAttribute('id');

                return null;
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                return null;
            }
        };

        $traverser = new Traverser($visitor);
        $traverser->traverse($this->document->getRootElement());

        $this->assertContains('rect:rect1', $visitor->visited);
    }

    public function testVisitPath(): void
    {
        $visitor = new class extends TypedVisitor {
            public array $visited = [];

            protected function visitPath(PathElement $path): mixed
            {
                $this->visited[] = 'path:'.$path->getAttribute('id');

                return null;
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                return null;
            }
        };

        $traverser = new Traverser($visitor);
        $traverser->traverse($this->document->getRootElement());

        $this->assertContains('path:path1', $visitor->visited);
    }

    public function testVisitMultipleTypes(): void
    {
        $visitor = new class extends TypedVisitor {
            public array $visited = [];

            protected function visitCircle(CircleElement $circle): mixed
            {
                $this->visited[] = 'circle';

                return null;
            }

            protected function visitRect(RectElement $rect): mixed
            {
                $this->visited[] = 'rect';

                return null;
            }

            protected function visitPath(PathElement $path): mixed
            {
                $this->visited[] = 'path';

                return null;
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                $this->visited[] = 'default';

                return null;
            }
        };

        $traverser = new Traverser($visitor);
        $traverser->traverse($this->document->getRootElement());

        $this->assertContains('circle', $visitor->visited);
        $this->assertContains('rect', $visitor->visited);
        $this->assertContains('path', $visitor->visited);
    }

    public function testVisitDefaultCalled(): void
    {
        $visitor = new class extends TypedVisitor {
            public array $visitedDefaults = [];

            protected function visitDefault(ElementInterface $element): mixed
            {
                $this->visitedDefaults[] = $element->getTagName();

                return null;
            }
        };

        $traverser = new Traverser($visitor);
        $traverser->traverse($this->document->getRootElement());

        // svg and g elements should go to visitDefault
        $this->assertContains('svg', $visitor->visitedDefaults);
        $this->assertContains('g', $visitor->visitedDefaults);
    }

    public function testModifyElementsWithTypedVisitor(): void
    {
        $visitor = new class extends TypedVisitor {
            protected function visitCircle(CircleElement $circle): mixed
            {
                $circle->setAttribute('fill', '#3b82f6');

                return null;
            }

            protected function visitRect(RectElement $rect): mixed
            {
                $rect->setAttribute('fill', '#10b981');

                return null;
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                return null;
            }
        };

        $traverser = new Traverser($visitor);
        $traverser->traverse($this->document->getRootElement());

        // Verify modifications
        $circle = $this->document->querySelector('#circle1');
        $this->assertEquals('#3b82f6', $circle->getAttribute('fill'));

        $rect = $this->document->querySelector('#rect1');
        $this->assertEquals('#10b981', $rect->getAttribute('fill'));
    }

    public function testStatisticsGathering(): void
    {
        $visitor = new class extends TypedVisitor {
            public array $stats = [
                'circles' => 0,
                'rectangles' => 0,
                'paths' => 0,
                'groups' => 0,
            ];

            protected function visitCircle(CircleElement $circle): mixed
            {
                ++$this->stats['circles'];

                return null;
            }

            protected function visitRect(RectElement $rect): mixed
            {
                ++$this->stats['rectangles'];

                return null;
            }

            protected function visitPath(PathElement $path): mixed
            {
                ++$this->stats['paths'];

                return null;
            }

            protected function visitGroup(GroupElement $group): mixed
            {
                ++$this->stats['groups'];

                return null;
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                return null;
            }
        };

        $traverser = new Traverser($visitor);
        $traverser->traverse($this->document->getRootElement());

        $this->assertEquals(1, $visitor->stats['circles']);
        $this->assertEquals(1, $visitor->stats['rectangles']);
        $this->assertEquals(1, $visitor->stats['paths']);
        $this->assertEquals(1, $visitor->stats['groups']);
    }

    public function testReturnValuePropagation(): void
    {
        $visitor = new class extends TypedVisitor {
            protected function visitCircle(CircleElement $circle): mixed
            {
                return 'circle-result';
            }

            protected function visitRect(RectElement $rect): mixed
            {
                return 'rect-result';
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                return 'default-result';
            }
        };

        $circle = new CircleElement();
        $result = $visitor->visit($circle);
        $this->assertEquals('circle-result', $result);

        $rect = new RectElement();
        $result = $visitor->visit($rect);
        $this->assertEquals('rect-result', $result);

        $group = new GroupElement();
        $result = $visitor->visit($group);
        $this->assertEquals('default-result', $result);
    }

    public function testMethodCaching(): void
    {
        // Create two visitors of the same anonymous class
        $visitorClass = new class extends TypedVisitor {
            protected function visitCircle(CircleElement $circle): mixed
            {
                return 'circle';
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                return 'default';
            }
        };

        $visitor1 = clone $visitorClass;
        $visitor2 = clone $visitorClass;

        $circle = new CircleElement();

        // Both visitors should work correctly with the cached method lookup
        $result1 = $visitor1->visit($circle);
        $result2 = $visitor2->visit($circle);

        $this->assertEquals('circle', $result1);
        $this->assertEquals('circle', $result2);
    }

    public function testVisitGroup(): void
    {
        $visitor = new class extends TypedVisitor {
            public array $visited = [];

            protected function visitGroup(GroupElement $group): mixed
            {
                $this->visited[] = 'group:'.$group->getAttribute('id');

                return null;
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                return null;
            }
        };

        $traverser = new Traverser($visitor);
        $traverser->traverse($this->document->getRootElement());

        $this->assertContains('group:test-group', $visitor->visited);
    }

    public function testBeforeAndAfterHooks(): void
    {
        $visitor = new class extends TypedVisitor {
            public array $log = [];

            protected function beforeVisit(ElementInterface $element): void
            {
                $this->log[] = 'before:'.$element->getTagName();
            }

            protected function visitCircle(CircleElement $circle): mixed
            {
                $this->log[] = 'visit:circle';

                return null;
            }

            protected function visitDefault(ElementInterface $element): mixed
            {
                $this->log[] = 'visit:default';

                return null;
            }

            protected function afterVisit(ElementInterface $element): void
            {
                $this->log[] = 'after:'.$element->getTagName();
            }
        };

        $circle = new CircleElement();
        $visitor->visit($circle);

        $this->assertContains('before:circle', $visitor->log);
        $this->assertContains('visit:circle', $visitor->log);
        $this->assertContains('after:circle', $visitor->log);

        // Check order
        $beforeIndex = array_search('before:circle', $visitor->log, true);
        $visitIndex = array_search('visit:circle', $visitor->log, true);
        $afterIndex = array_search('after:circle', $visitor->log, true);

        $this->assertTrue($beforeIndex < $visitIndex);
        $this->assertTrue($visitIndex < $afterIndex);
    }
}
