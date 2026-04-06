<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Visitor;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Visitor\AbstractVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractVisitor::class)]
final class AbstractVisitorTest extends TestCase
{
    public function testVisitCallsHooksInCorrectOrder(): void
    {
        $element = new PathElement();
        $callOrder = [];

        $visitor = new class($callOrder) extends AbstractVisitor {
            private array $callOrder;

            public function __construct(array &$callOrder)
            {
                $this->callOrder = &$callOrder;
            }

            protected function beforeVisit(ElementInterface $element): void
            {
                $this->callOrder[] = 'before';
            }

            protected function doVisit(ElementInterface $element): mixed
            {
                $this->callOrder[] = 'visit';

                return 'result';
            }

            protected function afterVisit(ElementInterface $element): void
            {
                $this->callOrder[] = 'after';
            }
        };

        $result = $visitor->visit($element);

        $this->assertSame('result', $result);
        $this->assertSame(['before', 'visit', 'after'], $callOrder);
    }

    public function testVisitWithoutOverridingHooks(): void
    {
        $element = new PathElement();

        $visitor = new class extends AbstractVisitor {
            protected function doVisit(ElementInterface $element): mixed
            {
                return $element->getTagName();
            }
        };

        $result = $visitor->visit($element);

        $this->assertSame('path', $result);
    }

    public function testVisitReturnsDoVisitResult(): void
    {
        $element = new PathElement();
        $expectedResult = ['data' => 'test'];

        $visitor = new class($expectedResult) extends AbstractVisitor {
            public function __construct(private readonly mixed $returnValue)
            {
            }

            protected function doVisit(ElementInterface $element): mixed
            {
                return $this->returnValue;
            }
        };

        $result = $visitor->visit($element);

        $this->assertSame($expectedResult, $result);
    }

    public function testBeforeVisitCanAccessElement(): void
    {
        $element = new PathElement();
        $element->setAttribute('test', 'value');

        $capturedAttribute = null;

        $visitor = new class($capturedAttribute) extends AbstractVisitor {
            private ?string $capturedAttribute = null;

            public function __construct(?string &$capturedAttribute)
            {
                $this->capturedAttribute = &$capturedAttribute;
            }

            protected function beforeVisit(ElementInterface $element): void
            {
                $this->capturedAttribute = $element->getAttribute('test');
            }

            protected function doVisit(ElementInterface $element): mixed
            {
                return null;
            }
        };

        $visitor->visit($element);

        $this->assertSame('value', $capturedAttribute);
    }

    public function testAfterVisitCanAccessElement(): void
    {
        $element = new PathElement();
        $modified = false;

        $visitor = new class($modified) extends AbstractVisitor {
            private bool $modified;

            public function __construct(bool &$modified)
            {
                $this->modified = &$modified;
            }

            protected function doVisit(ElementInterface $element): mixed
            {
                return null;
            }

            protected function afterVisit(ElementInterface $element): void
            {
                $this->modified = $element->hasAttribute('test');
            }
        };

        $element->setAttribute('test', 'value');
        $visitor->visit($element);

        $this->assertTrue($modified);
    }

    public function testVisitCanModifyElement(): void
    {
        $element = new PathElement();

        $visitor = new class extends AbstractVisitor {
            protected function doVisit(ElementInterface $element): mixed
            {
                $element->setAttribute('modified', 'true');

                return null;
            }
        };

        $visitor->visit($element);

        $this->assertSame('true', $element->getAttribute('modified'));
    }
}
