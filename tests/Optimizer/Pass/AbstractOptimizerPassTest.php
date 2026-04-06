<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\AbstractOptimizerPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractOptimizerPass::class)]
final class AbstractOptimizerPassTest extends TestCase
{
    public function testOptimizeHandlesNullRootElement(): void
    {
        $pass = new class extends AbstractOptimizerPass {
            /** @var list<string> */
            public array $visited = [];

            public function getName(): string
            {
                return 'test-pass';
            }

            protected function processElement(ElementInterface $element): void
            {
                $this->visited[] = $element->getTagName();
            }
        };

        $document = new Document();

        $pass->optimize($document);

        $this->assertEmpty($pass->visited);
    }

    public function testOptimizeTraversesAllElements(): void
    {
        $pass = new class extends AbstractOptimizerPass {
            /** @var list<string> */
            public array $visited = [];

            public function getName(): string
            {
                return 'test-pass';
            }

            protected function processElement(ElementInterface $element): void
            {
                $this->visited[] = $element->getTagName();
            }
        };

        $svg = new SvgElement();
        $group = new GroupElement();
        $rect1 = new RectElement();
        $rect2 = new RectElement();

        $group->appendChild($rect1);
        $svg->appendChild($group);
        $svg->appendChild($rect2);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(4, $pass->visited);
        $this->assertSame(['svg', 'g', 'rect', 'rect'], $pass->visited);
    }

    public function testTraversesDepthFirst(): void
    {
        $pass = new class extends AbstractOptimizerPass {
            /** @var list<string> */
            public array $visited = [];

            public function getName(): string
            {
                return 'test-pass';
            }

            protected function processElement(ElementInterface $element): void
            {
                $id = $element->getAttribute('id');
                $this->visited[] = $id ?? $element->getTagName();
            }
        };

        $svg = new SvgElement();
        $svg->setAttribute('id', 'root');

        $group1 = new GroupElement();
        $group1->setAttribute('id', 'g1');

        $group2 = new GroupElement();
        $group2->setAttribute('id', 'g2');

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'r1');

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'r2');

        $group1->appendChild($rect1);
        $svg->appendChild($group1);
        $svg->appendChild($group2);
        $group2->appendChild($rect2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Depth-first traversal: root -> g1 -> r1 -> g2 -> r2
        $this->assertSame(['root', 'g1', 'r1', 'g2', 'r2'], $pass->visited);
    }
}
