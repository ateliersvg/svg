<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\MergePathsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MergePathsPass::class)]
final class MergePathsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new MergePathsPass();

        $this->assertSame('merge-paths', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new MergePathsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testMergeBasicCompatiblePaths(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill', 'red');
        $path1->setAttribute('stroke', 'black');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'red');
        $path2->setAttribute('stroke', 'black');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $this->assertCount(2, $svg->getChildren());

        $pass->optimize($document);

        // Should be merged into one path
        $this->assertCount(1, $svg->getChildren());
        $mergedPath = $svg->getChildren()[0];
        $this->assertInstanceOf(PathElement::class, $mergedPath);
        $this->assertSame('M10 10 L20 20 M30 30 L40 40', $mergedPath->getPathData());
        $this->assertSame('red', $mergedPath->getAttribute('fill'));
        $this->assertSame('black', $mergedPath->getAttribute('stroke'));
    }

    public function testDoNotMergePathsWithDifferentFill(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill', 'red');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'blue');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $this->assertCount(2, $svg->getChildren());

        $pass->optimize($document);

        // Should NOT be merged due to different fill
        $this->assertCount(2, $svg->getChildren());
    }

    public function testDoNotMergePathsWithDifferentStroke(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('stroke', 'black');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('stroke', 'red');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged due to different stroke
        $this->assertCount(2, $svg->getChildren());
    }

    public function testDoNotMergePathsWithDifferentStrokeWidth(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('stroke-width', '1');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('stroke-width', '2');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged due to different stroke-width
        $this->assertCount(2, $svg->getChildren());
    }

    public function testDoNotMergePathsWithIds(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('id', 'path1');
        $path1->setAttribute('fill', 'red');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'red');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged because path1 has an id
        $this->assertCount(2, $svg->getChildren());
    }

    public function testDoNotMergePathsWithDifferentTransforms(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('transform', 'scale(2)');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('transform', 'scale(3)');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged due to different transforms
        $this->assertCount(2, $svg->getChildren());
    }

    public function testMergePathsWithSameTransform(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('transform', 'scale(2)');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('transform', 'scale(2)');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should be merged because transforms are identical
        $this->assertCount(1, $svg->getChildren());
    }

    public function testMergePathsUnderGroup(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill', 'red');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'red');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $svg->appendChild($group);

        $document = new Document($svg);

        $this->assertCount(2, $group->getChildren());

        $pass->optimize($document);

        // Should be merged under the group
        $this->assertCount(1, $group->getChildren());
        $mergedPath = $group->getChildren()[0];
        $this->assertInstanceOf(PathElement::class, $mergedPath);
        $this->assertSame('M10 10 L20 20 M30 30 L40 40', $mergedPath->getPathData());
    }

    public function testMergeThreeConsecutivePaths(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill', 'red');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'red');

        $path3 = new PathElement();
        $path3->setPathData('M50 50 L60 60');
        $path3->setAttribute('fill', 'red');

        $svg->appendChild($path1);
        $svg->appendChild($path2);
        $svg->appendChild($path3);

        $document = new Document($svg);

        $this->assertCount(3, $svg->getChildren());

        $pass->optimize($document);

        // All three should be merged into one
        $this->assertCount(1, $svg->getChildren());
        $mergedPath = $svg->getChildren()[0];
        $this->assertSame('M10 10 L20 20 M30 30 L40 40 M50 50 L60 60', $mergedPath->getPathData());
    }

    public function testMergeOnlyConsecutivePaths(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill', 'red');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'red');

        $group = new GroupElement();

        $path3 = new PathElement();
        $path3->setPathData('M50 50 L60 60');
        $path3->setAttribute('fill', 'red');

        $svg->appendChild($path1);
        $svg->appendChild($path2);
        $svg->appendChild($group);
        $svg->appendChild($path3);

        $document = new Document($svg);

        $this->assertCount(4, $svg->getChildren());

        $pass->optimize($document);

        // path1 and path2 should be merged, but path3 should remain separate
        $this->assertCount(3, $svg->getChildren());
        $this->assertInstanceOf(PathElement::class, $svg->getChildren()[0]);
        $this->assertInstanceOf(GroupElement::class, $svg->getChildren()[1]);
        $this->assertInstanceOf(PathElement::class, $svg->getChildren()[2]);
    }

    public function testMergePathsWithComplexAttributes(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill', 'red');
        $path1->setAttribute('stroke', 'black');
        $path1->setAttribute('stroke-width', '2');
        $path1->setAttribute('stroke-linecap', 'round');
        $path1->setAttribute('opacity', '0.5');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'red');
        $path2->setAttribute('stroke', 'black');
        $path2->setAttribute('stroke-width', '2');
        $path2->setAttribute('stroke-linecap', 'round');
        $path2->setAttribute('opacity', '0.5');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should be merged because all attributes match
        $this->assertCount(1, $svg->getChildren());
        $mergedPath = $svg->getChildren()[0];
        $this->assertSame('red', $mergedPath->getAttribute('fill'));
        $this->assertSame('black', $mergedPath->getAttribute('stroke'));
        $this->assertSame('2', $mergedPath->getAttribute('stroke-width'));
        $this->assertSame('round', $mergedPath->getAttribute('stroke-linecap'));
        $this->assertSame('0.5', $mergedPath->getAttribute('opacity'));
    }

    public function testDoNotMergePathsWithDifferentOpacity(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('opacity', '0.5');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('opacity', '0.8');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged due to different opacity
        $this->assertCount(2, $svg->getChildren());
    }

    public function testDoNotMergePathsWithEventHandlers(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('onclick', 'alert("click")');
        $path1->setAttribute('fill', 'red');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'red');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged because path1 has an event handler
        $this->assertCount(2, $svg->getChildren());
    }

    public function testMergePathsWithNoAttributes(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should be merged even with no styling attributes
        $this->assertCount(1, $svg->getChildren());
    }

    public function testDoNotMergePathsWithDifferentClasses(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('class', 'class1');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('class', 'class2');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged due to different classes
        $this->assertCount(2, $svg->getChildren());
    }

    public function testMergePathsWithDifferentClassesWhenIgnoreClassEnabled(): void
    {
        $pass = new MergePathsPass(ignoreClass: true);
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('class', 'class1');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('class', 'class2');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should be merged because ignoreClass is true
        $this->assertCount(1, $svg->getChildren());
    }

    public function testDoNotMergePathsWithDifferentStyles(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('style', 'fill: red;');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('style', 'fill: blue;');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged due to different style attributes
        $this->assertCount(2, $svg->getChildren());
    }

    public function testMergePathsInNestedGroups(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();

        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill', 'red');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'red');

        $innerGroup->appendChild($path1);
        $innerGroup->appendChild($path2);
        $outerGroup->appendChild($innerGroup);
        $svg->appendChild($outerGroup);

        $document = new Document($svg);

        $this->assertCount(2, $innerGroup->getChildren());

        $pass->optimize($document);

        // Should be merged in the inner group
        $this->assertCount(1, $innerGroup->getChildren());
    }

    public function testRealWorldScenario(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $group = new GroupElement();

        // First set of paths with same styling
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill', 'red');
        $path1->setAttribute('stroke', 'black');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'red');
        $path2->setAttribute('stroke', 'black');

        // Second set of paths with different styling
        $path3 = new PathElement();
        $path3->setPathData('M50 50 L60 60');
        $path3->setAttribute('fill', 'blue');
        $path3->setAttribute('stroke', 'black');

        $path4 = new PathElement();
        $path4->setPathData('M70 70 L80 80');
        $path4->setAttribute('fill', 'blue');
        $path4->setAttribute('stroke', 'black');

        $group->appendChild($path1);
        $group->appendChild($path2);
        $group->appendChild($path3);
        $group->appendChild($path4);
        $svg->appendChild($group);

        $document = new Document($svg);

        $this->assertCount(4, $group->getChildren());

        $pass->optimize($document);

        // Should be merged into 2 paths
        $this->assertCount(2, $group->getChildren());

        $mergedPath1 = $group->getChildren()[0];
        $this->assertInstanceOf(PathElement::class, $mergedPath1);
        $this->assertSame('M10 10 L20 20 M30 30 L40 40', $mergedPath1->getPathData());
        $this->assertSame('red', $mergedPath1->getAttribute('fill'));

        $mergedPath2 = $group->getChildren()[1];
        $this->assertInstanceOf(PathElement::class, $mergedPath2);
        $this->assertSame('M50 50 L60 60 M70 70 L80 80', $mergedPath2->getPathData());
        $this->assertSame('blue', $mergedPath2->getAttribute('fill'));
    }

    public function testMergePathsWithEmptyPathData(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');

        $path2 = new PathElement();
        $path2->setPathData('');

        $path3 = new PathElement();
        $path3->setPathData('M30 30 L40 40');

        $svg->appendChild($path1);
        $svg->appendChild($path2);
        $svg->appendChild($path3);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should be merged into one path
        $this->assertCount(1, $svg->getChildren());
        $mergedPath = $svg->getChildren()[0];
        $this->assertSame('M10 10 L20 20 M30 30 L40 40', $mergedPath->getPathData());
    }

    public function testDoNotMergeWithSinglePath(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setPathData('M10 10 L20 20');

        $svg->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should still have one path
        $this->assertCount(1, $svg->getChildren());
    }

    public function testMergePathsWithDashArray(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('stroke-dasharray', '5,5');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('stroke-dasharray', '5,5');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should be merged because stroke-dasharray matches
        $this->assertCount(1, $svg->getChildren());
    }

    public function testDoNotMergePathsWithDifferentDashArray(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('stroke-dasharray', '5,5');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('stroke-dasharray', '10,10');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged due to different stroke-dasharray
        $this->assertCount(2, $svg->getChildren());
    }

    public function testDoNotMergePathsWithDifferentVisibility(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('visibility', 'visible');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('visibility', 'hidden');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged due to different visibility
        $this->assertCount(2, $svg->getChildren());
    }

    public function testMergePathsWithSameClass(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('class', 'myclass');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('class', 'myclass');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should be merged because classes match
        $this->assertCount(1, $svg->getChildren());
    }

    public function testDoNotMergePathsWithDifferentFillRule(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill-rule', 'evenodd');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill-rule', 'nonzero');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should NOT be merged due to different fill-rule
        $this->assertCount(2, $svg->getChildren());
    }

    public function testMergePathsSkipsGroupWithFewerThanTwoPaths(): void
    {
        $pass = new MergePathsPass();
        $svg = new SvgElement();
        $group = new GroupElement();

        $path = new PathElement();
        $path->setPathData('M10 10 L20 20');
        $path->setAttribute('fill', 'red');

        $group->appendChild($path);
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass->optimize($document);

        $this->assertCount(1, $group->getChildren());
        $this->assertSame('M10 10 L20 20', $group->getChildren()[0]->getPathData());
    }
}
