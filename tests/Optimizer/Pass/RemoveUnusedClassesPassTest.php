<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\StyleElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedClassesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveUnusedClassesPass::class)]
final class RemoveUnusedClassesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveUnusedClassesPass();

        $this->assertSame('remove-unused-classes', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveUnusedClassesPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveUnusedClass(): void
    {
        $pass = new RemoveUnusedClassesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.used { fill: red; } .unused { fill: blue; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('class', 'used');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $styleContent = $style->getContent();
        $this->assertNotNull($styleContent);
        $this->assertStringContainsString('.used', $styleContent);
        $this->assertStringNotContainsString('.unused', $styleContent);
    }

    public function testKeepUsedClass(): void
    {
        $pass = new RemoveUnusedClassesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.my-class { fill: red; stroke: black; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('class', 'my-class');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $styleContent = $style->getContent();
        $this->assertNotNull($styleContent);
        $this->assertStringContainsString('.my-class', $styleContent);
        $this->assertStringContainsString('fill: red', $styleContent);
    }

    public function testRemoveMultipleUnusedClasses(): void
    {
        $pass = new RemoveUnusedClassesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $css = '.used1 { fill: red; } .unused1 { fill: blue; } .used2 { stroke: green; } .unused2 { opacity: 0.5; }';
        $style->setContent($css);
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect1 = new RectElement();
        $rect1->setAttribute('class', 'used1');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('class', 'used2');
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        $styleContent = $style->getContent();
        $this->assertNotNull($styleContent);
        $this->assertStringContainsString('.used1', $styleContent);
        $this->assertStringContainsString('.used2', $styleContent);
        $this->assertStringNotContainsString('.unused1', $styleContent);
        $this->assertStringNotContainsString('.unused2', $styleContent);
    }

    public function testMultipleClassesOnElement(): void
    {
        $pass = new RemoveUnusedClassesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.class1 { fill: red; } .class2 { stroke: blue; } .unused { opacity: 0.5; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'class1 class2');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $styleContent = $style->getContent();
        $this->assertNotNull($styleContent);
        $this->assertStringContainsString('.class1', $styleContent);
        $this->assertStringContainsString('.class2', $styleContent);
        $this->assertStringNotContainsString('.unused', $styleContent);
    }

    public function testRemoveEmptyStyleElement(): void
    {
        $pass = new RemoveUnusedClassesPass(removeEmptyStyles: true);
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.unused { fill: red; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->assertCount(1, $defs->getChildren()); // Style element exists

        $pass->optimize($document);

        // Style element should be removed because it's empty after removing unused classes
        $this->assertCount(0, $defs->getChildren());
    }

    public function testKeepEmptyStyleElement(): void
    {
        $pass = new RemoveUnusedClassesPass(removeEmptyStyles: false);
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.unused { fill: red; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Style element should still exist even though it's empty
        $this->assertCount(1, $defs->getChildren());
    }

    public function testNoStyleElements(): void
    {
        $pass = new RemoveUnusedClassesPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('class', 'some-class');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should not throw an error
        $this->assertNotNull($document->getRootElement());
    }

    public function testEmptyCssContent(): void
    {
        $pass = new RemoveUnusedClassesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should handle empty CSS gracefully
        $this->assertNotNull($document->getRootElement());
    }

    public function testClassWithDashesAndUnderscores(): void
    {
        $pass = new RemoveUnusedClassesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.my-class_name { fill: red; } .unused-class { fill: blue; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'my-class_name');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $styleContent = $style->getContent();
        $this->assertNotNull($styleContent);
        $this->assertStringContainsString('.my-class_name', $styleContent);
        $this->assertStringNotContainsString('.unused-class', $styleContent);
    }

    public function testRemoveAllClassBasedStylesWhenNoClassesUsed(): void
    {
        $pass = new RemoveUnusedClassesPass(removeEmptyStyles: true);
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.unused1 { fill: red; } .unused2 { stroke: blue; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        // No elements have any class attribute
        $rect = new RectElement();
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Style should be removed since all classes are unused and it becomes empty
        $this->assertCount(0, $defs->getChildren());
    }

    public function testRemoveAllClassBasedStylesWithNonClassRulesRemaining(): void
    {
        $pass = new RemoveUnusedClassesPass(removeEmptyStyles: true);
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        // Mix of class rules and non-class rules (e.g., element selector)
        $style->setContent('.unused { fill: red; } rect { stroke: blue; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        // No elements with class attribute
        $rect = new RectElement();
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Style should remain because there are non-class rules
        $this->assertCount(1, $defs->getChildren());
        $content = $style->getContent();
        $this->assertNotNull($content);
        $this->assertStringContainsString('rect', $content);
    }

    public function testUsedClassesCleanStyleRemainsNonEmpty(): void
    {
        $pass = new RemoveUnusedClassesPass(removeEmptyStyles: true);
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.used { fill: red; } .unused { fill: blue; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'used');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Style should remain since .used rule is kept
        $this->assertCount(1, $defs->getChildren());
        $content = $style->getContent();
        $this->assertNotNull($content);
        $this->assertStringContainsString('.used', $content);
    }

    public function testRemovesEmptyStyleElementWhenAllClassesUnused(): void
    {
        $pass = new RemoveUnusedClassesPass(removeEmptyStyles: true);

        $svg = new SvgElement();
        $defs = new DefsElement();
        $svg->appendChild($defs);

        $style = new StyleElement();
        $style->setContent('.unused { fill: red; }');
        $defs->appendChild($style);

        // No elements use the .unused class
        $document = new Document($svg);
        $pass->optimize($document);

        // Style element should be removed from defs since it became empty
        $this->assertCount(0, $defs->getChildren());
    }

    public function testRemovesEmptyStyleElementWhenSomeClassesUsedButStyleOnlyHasUnused(): void
    {
        $pass = new RemoveUnusedClassesPass(removeEmptyStyles: true);

        $svg = new SvgElement();
        $defs = new DefsElement();
        $svg->appendChild($defs);

        $style = new StyleElement();
        $style->setContent('.unused { fill: red; }');
        $defs->appendChild($style);

        $rect = new RectElement();
        $rect->setAttribute('class', 'used');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $this->assertCount(0, $defs->getChildren());
    }
}
