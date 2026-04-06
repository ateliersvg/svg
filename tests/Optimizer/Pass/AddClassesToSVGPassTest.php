<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\StyleElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\AddClassesToSVGPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddClassesToSVGPass::class)]
final class AddClassesToSVGPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new AddClassesToSVGPass();

        $this->assertSame('add-classes-to-svg', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new AddClassesToSVGPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testNoOptimizationWhenNoCommonStyles(): void
    {
        $pass = new AddClassesToSVGPass();
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect1->setAttribute('fill', 'red');

        $rect2 = new RectElement();
        $rect2->setX(100)->setY(100)->setWidth(50)->setHeight(50);
        $rect2->setAttribute('fill', 'blue');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // No common styles, so no style element should be added
        $children = $svg->getChildren();
        $hasStyleElement = false;
        foreach ($children as $child) {
            if ($child instanceof DefsElement) {
                $hasStyleElement = true;
            }
        }

        $this->assertFalse($hasStyleElement);
    }

    public function testExtractCommonStylesToClass(): void
    {
        $pass = new AddClassesToSVGPass();
        $svg = new SvgElement();

        // Create two rectangles with identical styles
        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect1->setAttribute('fill', 'red');
        $rect1->setAttribute('stroke', 'black');
        $rect1->setAttribute('stroke-width', '2');

        $rect2 = new RectElement();
        $rect2->setX(100)->setY(100)->setWidth(50)->setHeight(50);
        $rect2->setAttribute('fill', 'red');
        $rect2->setAttribute('stroke', 'black');
        $rect2->setAttribute('stroke-width', '2');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // Check that a <defs> element with <style> was added
        $children = $svg->getChildren();
        $defsElement = null;
        foreach ($children as $child) {
            if ($child instanceof DefsElement) {
                $defsElement = $child;
                break;
            }
        }

        $this->assertNotNull($defsElement, 'Defs element should be created');

        // Check that style element exists
        $styleElement = null;
        foreach ($defsElement->getChildren() as $child) {
            if ($child instanceof StyleElement) {
                $styleElement = $child;
                break;
            }
        }

        $this->assertNotNull($styleElement, 'Style element should be created');

        // Check that CSS content contains the styles
        $cssContent = $styleElement->getContent();
        $this->assertNotNull($cssContent);
        $this->assertStringContainsString('fill: red;', $cssContent);
        $this->assertStringContainsString('stroke: black;', $cssContent);
        $this->assertStringContainsString('stroke-width: 2;', $cssContent);

        // Check that rectangles now have class attribute
        $this->assertTrue($rect1->hasAttribute('class'));
        $this->assertTrue($rect2->hasAttribute('class'));

        // Check that original style attributes were removed
        $this->assertFalse($rect1->hasAttribute('fill'));
        $this->assertFalse($rect1->hasAttribute('stroke'));
        $this->assertFalse($rect1->hasAttribute('stroke-width'));
        $this->assertFalse($rect2->hasAttribute('fill'));
        $this->assertFalse($rect2->hasAttribute('stroke'));
        $this->assertFalse($rect2->hasAttribute('stroke-width'));

        // Both should have the same class
        $this->assertSame($rect1->getAttribute('class'), $rect2->getAttribute('class'));
    }

    public function testMultipleStyleGroups(): void
    {
        $pass = new AddClassesToSVGPass();
        $svg = new SvgElement();

        // Group 1: Red rectangles
        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect1->setAttribute('fill', 'red');

        $rect2 = new RectElement();
        $rect2->setX(70)->setY(10)->setWidth(50)->setHeight(50);
        $rect2->setAttribute('fill', 'red');

        // Group 2: Blue circles
        $circle1 = new CircleElement();
        $circle1->setCx(50)->setCy(50)->setR(20);
        $circle1->setAttribute('fill', 'blue');
        $circle1->setAttribute('stroke', 'white');

        $circle2 = new CircleElement();
        $circle2->setCx(150)->setCy(50)->setR(20);
        $circle2->setAttribute('fill', 'blue');
        $circle2->setAttribute('stroke', 'white');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);
        $svg->appendChild($circle1);
        $svg->appendChild($circle2);

        $document = new Document($svg);
        $pass->optimize($document);

        // Check that all elements have classes
        $this->assertTrue($rect1->hasAttribute('class'));
        $this->assertTrue($rect2->hasAttribute('class'));
        $this->assertTrue($circle1->hasAttribute('class'));
        $this->assertTrue($circle2->hasAttribute('class'));

        // Rectangles should have the same class
        $this->assertSame($rect1->getAttribute('class'), $rect2->getAttribute('class'));

        // Circles should have the same class
        $this->assertSame($circle1->getAttribute('class'), $circle2->getAttribute('class'));

        // But rectangles and circles should have different classes
        $this->assertNotSame($rect1->getAttribute('class'), $circle1->getAttribute('class'));
    }

    public function testMinOccurrencesParameter(): void
    {
        // Require at least 3 elements with same styles to create a class
        $pass = new AddClassesToSVGPass(minOccurrences: 3);
        $svg = new SvgElement();

        // Only create 2 rectangles with same style
        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect1->setAttribute('fill', 'red');

        $rect2 = new RectElement();
        $rect2->setX(70)->setY(10)->setWidth(50)->setHeight(50);
        $rect2->setAttribute('fill', 'red');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should not create classes because we only have 2 elements (less than minOccurrences: 3)
        $this->assertFalse($rect1->hasAttribute('class'));
        $this->assertFalse($rect2->hasAttribute('class'));
        $this->assertTrue($rect1->hasAttribute('fill'));
        $this->assertTrue($rect2->hasAttribute('fill'));
    }

    public function testPreserveExistingClasses(): void
    {
        $pass = new AddClassesToSVGPass(preserveExistingClasses: true);
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect1->setAttribute('class', 'existing-class');
        $rect1->setAttribute('fill', 'red');

        $rect2 = new RectElement();
        $rect2->setX(70)->setY(10)->setWidth(50)->setHeight(50);
        $rect2->setAttribute('fill', 'red');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // rect1 should have both existing and new class
        $class1 = $rect1->getAttribute('class');
        $this->assertNotNull($class1);
        $this->assertStringContainsString('existing-class', $class1);

        // rect2 should only have the new class
        $class2 = $rect2->getAttribute('class');
        $this->assertNotNull($class2);
        $this->assertStringNotContainsString('existing-class', $class2);
    }

    public function testCustomClassPrefix(): void
    {
        $pass = new AddClassesToSVGPass(classPrefix: 'custom-');
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect1->setAttribute('fill', 'red');

        $rect2 = new RectElement();
        $rect2->setX(70)->setY(10)->setWidth(50)->setHeight(50);
        $rect2->setAttribute('fill', 'red');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // Check that the class name uses the custom prefix
        $className = $rect1->getAttribute('class');
        $this->assertNotNull($className);
        $this->assertStringStartsWith('custom-', $className);
    }

    public function testWorksWithNestedElements(): void
    {
        $pass = new AddClassesToSVGPass();
        $svg = new SvgElement();
        $group = new GroupElement();

        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect1->setAttribute('fill', 'green');
        $rect1->setAttribute('opacity', '0.5');

        $rect2 = new RectElement();
        $rect2->setX(70)->setY(10)->setWidth(50)->setHeight(50);
        $rect2->setAttribute('fill', 'green');
        $rect2->setAttribute('opacity', '0.5');

        $group->appendChild($rect1);
        $svg->appendChild($group);
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // Both rectangles should have classes even though one is nested
        $this->assertTrue($rect1->hasAttribute('class'));
        $this->assertTrue($rect2->hasAttribute('class'));
        $this->assertSame($rect1->getAttribute('class'), $rect2->getAttribute('class'));
    }

    public function testOnlyExtractsStyleableAttributes(): void
    {
        $pass = new AddClassesToSVGPass();
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect1->setAttribute('fill', 'red');
        $rect1->setAttribute('id', 'rect1'); // Not styleable

        $rect2 = new RectElement();
        $rect2->setX(70)->setY(10)->setWidth(50)->setHeight(50);
        $rect2->setAttribute('fill', 'red');
        $rect2->setAttribute('id', 'rect2'); // Not styleable

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // fill should be extracted to class
        $this->assertTrue($rect1->hasAttribute('class'));
        $this->assertFalse($rect1->hasAttribute('fill'));

        // id should be preserved
        $this->assertTrue($rect1->hasAttribute('id'));
        $this->assertSame('rect1', $rect1->getAttribute('id'));
        $this->assertTrue($rect2->hasAttribute('id'));
        $this->assertSame('rect2', $rect2->getAttribute('id'));
    }

    public function testAddStyleToExistingDefs(): void
    {
        $pass = new AddClassesToSVGPass();
        $svg = new SvgElement();

        // Create an existing <defs> element
        $defs = new DefsElement();
        $svg->appendChild($defs);

        $rect1 = new RectElement();
        $rect1->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect1->setAttribute('fill', 'red');

        $rect2 = new RectElement();
        $rect2->setX(70)->setY(10)->setWidth(50)->setHeight(50);
        $rect2->setAttribute('fill', 'red');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should use existing defs element
        $children = $svg->getChildren();
        $defsCount = 0;
        foreach ($children as $child) {
            if ($child instanceof DefsElement) {
                ++$defsCount;
            }
        }

        // Should still only have 1 defs element
        $this->assertSame(1, $defsCount);

        // Style should be in the existing defs
        $styleElement = null;
        foreach ($defs->getChildren() as $child) {
            if ($child instanceof StyleElement) {
                $styleElement = $child;
                break;
            }
        }

        $this->assertNotNull($styleElement);
    }

    public function testNoStyleableElementsReturnsEarly(): void
    {
        $pass = new AddClassesToSVGPass();
        $svg = new SvgElement();

        $document = new Document($svg);
        $pass->optimize($document);

        // No children at all, so no styleable elements
        $this->assertCount(0, $svg->getChildren());
    }

    public function testWorksWithPaths(): void
    {
        $pass = new AddClassesToSVGPass();
        $svg = new SvgElement();

        $path1 = new PathElement();
        $path1->setPathData('M10 10 L20 20');
        $path1->setAttribute('fill', 'none');
        $path1->setAttribute('stroke', 'blue');
        $path1->setAttribute('stroke-width', '3');

        $path2 = new PathElement();
        $path2->setPathData('M30 30 L40 40');
        $path2->setAttribute('fill', 'none');
        $path2->setAttribute('stroke', 'blue');
        $path2->setAttribute('stroke-width', '3');

        $svg->appendChild($path1);
        $svg->appendChild($path2);

        $document = new Document($svg);
        $pass->optimize($document);

        // Paths should have classes
        $this->assertTrue($path1->hasAttribute('class'));
        $this->assertTrue($path2->hasAttribute('class'));
        $this->assertSame($path1->getAttribute('class'), $path2->getAttribute('class'));

        // Original attributes should be removed
        $this->assertFalse($path1->hasAttribute('fill'));
        $this->assertFalse($path1->hasAttribute('stroke'));
        $this->assertFalse($path1->hasAttribute('stroke-width'));
    }
}
