<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Visitor;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Visitor\Traverser;
use Atelier\Svg\Visitor\VisitorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Traverser::class)]
final class TraverserTest extends TestCase
{
    public function testConstructor(): void
    {
        $visitor = $this->createStub(VisitorInterface::class);
        $traverser = new Traverser($visitor);

        $this->assertInstanceOf(Traverser::class, $traverser);
    }

    public function testTraverseVisitsSingleElement(): void
    {
        $element = new PathElement();
        $visitor = $this->createMock(VisitorInterface::class);

        $visitor->expects($this->once())
            ->method('visit')
            ->with($element);

        $traverser = new Traverser($visitor);
        $traverser->traverse($element);
    }

    public function testTraverseVisitsContainerAndChildren(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $svg->appendChild($group);
        $group->appendChild($path);

        $visitor = $this->createMock(VisitorInterface::class);

        // Expect visitor to be called 3 times: svg, group, path
        $visitor->expects($this->exactly(3))
            ->method('visit')
            ->willReturnCallback(function (ElementInterface $element) use ($svg, $group, $path) {
                static $callCount = 0;
                ++$callCount;

                if (1 === $callCount) {
                    $this->assertSame($svg, $element);
                } elseif (2 === $callCount) {
                    $this->assertSame($group, $element);
                } elseif (3 === $callCount) {
                    $this->assertSame($path, $element);
                }

                return null;
            });

        $traverser = new Traverser($visitor);
        $traverser->traverse($svg);
    }

    public function testTraverseVisitsMultipleChildren(): void
    {
        $group = new GroupElement();
        $path1 = new PathElement();
        $path2 = new PathElement();
        $path3 = new PathElement();

        $group->appendChild($path1);
        $group->appendChild($path2);
        $group->appendChild($path3);

        $visitor = $this->createMock(VisitorInterface::class);

        // Expect visitor to be called 4 times: group and 3 paths
        $visitor->expects($this->exactly(4))
            ->method('visit');

        $traverser = new Traverser($visitor);
        $traverser->traverse($group);
    }

    public function testTraverseDepthFirstOrder(): void
    {
        // Build a tree:
        //   svg
        //     group1
        //       path1
        //     group2
        //       path2
        $svg = new SvgElement();
        $group1 = new GroupElement();
        $group2 = new GroupElement();
        $path1 = new PathElement();
        $path2 = new PathElement();

        $svg->appendChild($group1);
        $svg->appendChild($group2);
        $group1->appendChild($path1);
        $group2->appendChild($path2);

        $visitedElements = [];
        $visitor = $this->createMock(VisitorInterface::class);

        $visitor->expects($this->exactly(5))
            ->method('visit')
            ->willReturnCallback(function (ElementInterface $element) use (&$visitedElements) {
                $visitedElements[] = $element->getTagName();

                return null;
            });

        $traverser = new Traverser($visitor);
        $traverser->traverse($svg);

        // Verify depth-first traversal order
        $this->assertSame(['svg', 'g', 'path', 'g', 'path'], $visitedElements);
    }

    public function testTraverseEmptyContainer(): void
    {
        $group = new GroupElement();
        $visitor = $this->createMock(VisitorInterface::class);

        // Should visit the group itself, even though it has no children
        $visitor->expects($this->once())
            ->method('visit')
            ->with($group);

        $traverser = new Traverser($visitor);
        $traverser->traverse($group);
    }
}
