<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\StyleElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\InlineStylesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InlineStylesPass::class)]
final class InlineStylesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new InlineStylesPass();

        $this->assertSame('inline-styles', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new InlineStylesPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testInlineBasicStyle(): void
    {
        $pass = new InlineStylesPass();
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

        // Styles should be inlined
        $this->assertSame('red', $rect->getAttribute('fill'));
        $this->assertSame('black', $rect->getAttribute('stroke'));

        // Class attribute should be removed
        $this->assertFalse($rect->hasAttribute('class'));

        // Style element should be removed
        $this->assertCount(0, $defs->getChildren());
    }

    public function testInlineMultipleProperties(): void
    {
        $pass = new InlineStylesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.test { fill: blue; stroke: green; stroke-width: 2; opacity: 0.5; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'test');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // All properties should be inlined
        $this->assertSame('blue', $rect->getAttribute('fill'));
        $this->assertSame('green', $rect->getAttribute('stroke'));
        $this->assertSame('2', $rect->getAttribute('stroke-width'));
        $this->assertSame('0.5', $rect->getAttribute('opacity'));
    }

    public function testInlineMultipleClasses(): void
    {
        $pass = new InlineStylesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.class1 { fill: red; } .class2 { stroke: blue; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'class1 class2');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Both classes' styles should be inlined
        $this->assertSame('red', $rect->getAttribute('fill'));
        $this->assertSame('blue', $rect->getAttribute('stroke'));
    }

    public function testPreserveExistingAttributes(): void
    {
        $pass = new InlineStylesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.my-class { fill: red; stroke: black; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'my-class');
        $rect->setAttribute('fill', 'blue'); // Existing attribute
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Existing attribute should take precedence
        $this->assertSame('blue', $rect->getAttribute('fill'));

        // Other style should still be applied
        $this->assertSame('black', $rect->getAttribute('stroke'));
    }

    public function testKeepStyleElement(): void
    {
        $pass = new InlineStylesPass(removeStyleElements: false);
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.my-class { fill: red; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'my-class');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Style element should be kept
        $this->assertCount(1, $defs->getChildren());
        $this->assertInstanceOf(StyleElement::class, $defs->getChildren()[0]);
    }

    public function testKeepClassAttribute(): void
    {
        $pass = new InlineStylesPass(removeClassAttributes: false);
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.my-class { fill: red; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'my-class');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Class attribute should be kept
        $this->assertTrue($rect->hasAttribute('class'));
        $this->assertSame('my-class', $rect->getAttribute('class'));

        // Styles should still be inlined
        $this->assertSame('red', $rect->getAttribute('fill'));
    }

    public function testMultipleElements(): void
    {
        $pass = new InlineStylesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.red-fill { fill: red; } .blue-stroke { stroke: blue; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect1 = new RectElement();
        $rect1->setAttribute('class', 'red-fill');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('class', 'blue-stroke');
        $svg->appendChild($rect2);

        $circle = new CircleElement();
        $circle->setCx(50)->setCy(50)->setR(25);
        $circle->setAttribute('class', 'red-fill blue-stroke');
        $svg->appendChild($circle);

        $document = new Document($svg);
        $pass->optimize($document);

        // rect1 should have red fill
        $this->assertSame('red', $rect1->getAttribute('fill'));
        $this->assertFalse($rect1->hasAttribute('stroke'));

        // rect2 should have blue stroke
        $this->assertSame('blue', $rect2->getAttribute('stroke'));
        $this->assertFalse($rect2->hasAttribute('fill'));

        // circle should have both
        $this->assertSame('red', $circle->getAttribute('fill'));
        $this->assertSame('blue', $circle->getAttribute('stroke'));
    }

    public function testNoStyleElements(): void
    {
        $pass = new InlineStylesPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('class', 'some-class');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should not throw an error
        // Class attribute should be kept since no styles were inlined
        $this->assertTrue($rect->hasAttribute('class'));
    }

    public function testEmptyStyleContent(): void
    {
        $pass = new InlineStylesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'my-class');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should handle empty CSS gracefully
        $this->assertNotNull($document->getRootElement());
    }

    public function testNoMatchingClass(): void
    {
        $pass = new InlineStylesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.other-class { fill: red; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'my-class'); // Different class
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // No styles should be inlined (class doesn't match)
        $this->assertFalse($rect->hasAttribute('fill'));

        // Class attribute should be kept since no styles were inlined
        $this->assertTrue($rect->hasAttribute('class'));
    }

    public function testPropertyWithSemicolons(): void
    {
        $pass = new InlineStylesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $style->setContent('.test { fill: red; stroke: black; stroke-width: 2; }');
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'test');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // All properties should be parsed correctly
        $this->assertSame('red', $rect->getAttribute('fill'));
        $this->assertSame('black', $rect->getAttribute('stroke'));
        $this->assertSame('2', $rect->getAttribute('stroke-width'));
    }

    public function testWhitespaceInCss(): void
    {
        $pass = new InlineStylesPass();
        $svg = new SvgElement();

        $defs = new DefsElement();
        $style = new StyleElement();
        $css = <<<CSS
        .test {
            fill: red;
            stroke: black;
        }
        CSS;
        $style->setContent($css);
        $defs->appendChild($style);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('class', 'test');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should parse CSS with whitespace correctly
        $this->assertSame('red', $rect->getAttribute('fill'));
        $this->assertSame('black', $rect->getAttribute('stroke'));
    }
}
