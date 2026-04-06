<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\StyleElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\MergeStylesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MergeStylesPass::class)]
final class MergeStylesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new MergeStylesPass();

        $this->assertSame('merge-styles', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new MergeStylesPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testNoMergeWithSingleStyle(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style = new StyleElement();
        $style->setContent('.class1 { fill: red; }');
        $svg->appendChild($style);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should still have one style element
        $this->assertCount(1, $svg->getChildren());
        $this->assertInstanceOf(StyleElement::class, $svg->getChildren()[0]);
    }

    public function testMergeTwoStyles(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style1 = new StyleElement();
        $style1->setContent('.class1 { fill: red; }');
        $style2 = new StyleElement();
        $style2->setContent('.class2 { fill: blue; }');
        $svg->appendChild($style1);
        $svg->appendChild($style2);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should only have one style element
        $this->assertCount(1, $svg->getChildren());
        $this->assertInstanceOf(StyleElement::class, $svg->getChildren()[0]);

        /** @var StyleElement $mergedStyle */
        $mergedStyle = $svg->getChildren()[0];
        $content = $mergedStyle->getContent();

        $this->assertNotNull($content);
        $this->assertStringContainsString('.class1', $content);
        $this->assertStringContainsString('.class2', $content);
        $this->assertStringContainsString('fill: red', $content);
        $this->assertStringContainsString('fill: blue', $content);
    }

    public function testMergeMultipleStyles(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style1 = new StyleElement();
        $style1->setContent('.class1 { fill: red; }');
        $style2 = new StyleElement();
        $style2->setContent('.class2 { fill: blue; }');
        $style3 = new StyleElement();
        $style3->setContent('.class3 { fill: green; }');
        $svg->appendChild($style1);
        $svg->appendChild($style2);
        $svg->appendChild($style3);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should only have one style element
        $this->assertCount(1, $svg->getChildren());
        $this->assertInstanceOf(StyleElement::class, $svg->getChildren()[0]);
    }

    public function testDeduplicateIdenticalSelectors(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style1 = new StyleElement();
        $style1->setContent('.class1 { fill: red; stroke: black; }');
        $style2 = new StyleElement();
        $style2->setContent('.class1 { fill: blue; }');
        $svg->appendChild($style1);
        $svg->appendChild($style2);
        $document = new Document($svg);

        $pass->optimize($document);

        /** @var StyleElement $mergedStyle */
        $mergedStyle = $svg->getChildren()[0];
        $content = $mergedStyle->getContent();

        $this->assertNotNull($content);
        // Later rule should override
        $this->assertStringContainsString('fill: blue', $content);
        $this->assertStringContainsString('stroke: black', $content);
    }

    public function testMinifyCss(): void
    {
        $pass = new MergeStylesPass(true);
        $svg = new SvgElement();
        $style1 = new StyleElement();
        $style1->setContent('  .class1  {  fill:  red;  }  ');
        $style2 = new StyleElement();
        $style2->setContent('  .class2  {  fill:  blue;  }  ');
        $svg->appendChild($style1);
        $svg->appendChild($style2);
        $document = new Document($svg);

        $pass->optimize($document);

        /** @var StyleElement $mergedStyle */
        $mergedStyle = $svg->getChildren()[0];
        $content = $mergedStyle->getContent();

        $this->assertNotNull($content);
        // Should be minified (no extra whitespace)
        $this->assertStringNotContainsString('  ', $content);
        $this->assertStringNotContainsString("\n", $content);
    }

    public function testRemoveComments(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style1 = new StyleElement();
        $style1->setContent('/* This is a comment */ .class1 { fill: red; }');
        $style2 = new StyleElement();
        $style2->setContent('.class2 { fill: blue; }');
        $svg->appendChild($style1);
        $svg->appendChild($style2);
        $document = new Document($svg);

        $pass->optimize($document);

        /** @var StyleElement $mergedStyle */
        $mergedStyle = $svg->getChildren()[0];
        $content = $mergedStyle->getContent();

        $this->assertNotNull($content);
        $this->assertStringNotContainsString('comment', $content);
    }

    public function testIgnoreEmptyStyles(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style1 = new StyleElement();
        $style1->setContent('.class1 { fill: red; }');
        $style2 = new StyleElement();
        $style2->setContent('');
        $svg->appendChild($style1);
        $svg->appendChild($style2);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should still merge into one
        $this->assertCount(1, $svg->getChildren());
    }

    public function testMergeStylesInDifferentContainers(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $style1 = new StyleElement();
        $style1->setContent('.class1 { fill: red; }');
        $style2 = new StyleElement();
        $style2->setContent('.class2 { fill: blue; }');

        $group->appendChild($style1);
        $svg->appendChild($group);
        $svg->appendChild($style2);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should merge both styles into the first one (in defs)
        $styles = $this->findAllStyles($svg);
        $this->assertCount(1, $styles);
    }

    public function testPreserveMultipleProperties(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style = new StyleElement();
        $style->setContent('.class1 { fill: red; stroke: blue; opacity: 0.5; }');
        $svg->appendChild($style);
        $document = new Document($svg);

        $pass->optimize($document);

        /** @var StyleElement $mergedStyle */
        $mergedStyle = $svg->getChildren()[0];
        $content = $mergedStyle->getContent();

        $this->assertNotNull($content);
        $this->assertStringContainsString('fill: red', $content);
        $this->assertStringContainsString('stroke: blue', $content);
        $this->assertStringContainsString('opacity: 0.5', $content);
    }

    public function testComplexSelectors(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style = new StyleElement();
        $style->setContent('.class1 > .class2 { fill: red; }');
        $svg->appendChild($style);
        $document = new Document($svg);

        $pass->optimize($document);

        /** @var StyleElement $mergedStyle */
        $mergedStyle = $svg->getChildren()[0];
        $content = $mergedStyle->getContent();

        $this->assertNotNull($content);
        $this->assertStringContainsString('.class1 > .class2', $content);
    }

    public function testMultilineRules(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style = new StyleElement();
        $style->setContent(".class1 {\n  fill: red;\n  stroke: blue;\n}");
        $svg->appendChild($style);
        $document = new Document($svg);

        $pass->optimize($document);

        /** @var StyleElement $mergedStyle */
        $mergedStyle = $svg->getChildren()[0];
        $content = $mergedStyle->getContent();

        $this->assertNotNull($content);
        $this->assertStringContainsString('fill: red', $content);
        $this->assertStringContainsString('stroke: blue', $content);
    }

    public function testMergeStylesAllEmptyCssContent(): void
    {
        $pass = new MergeStylesPass();
        $svg = new SvgElement();
        $style1 = new StyleElement();
        $style1->setContent('');
        $style2 = new StyleElement();
        $style2->setContent('');
        $svg->appendChild($style1);
        $svg->appendChild($style2);
        $document = new Document($svg);

        $pass->optimize($document);

        // Both styles are empty, so cssContent is empty and the method returns early
        $this->assertCount(2, $svg->getChildren());
    }

    /**
     * Helper to find all style elements recursively.
     *
     * @return array<StyleElement>
     */
    private function findAllStyles(\Atelier\Svg\Element\ElementInterface $element): array
    {
        $styles = [];

        if ($element instanceof StyleElement) {
            $styles[] = $element;
        }

        if ($element instanceof \Atelier\Svg\Element\ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $styles = array_merge($styles, $this->findAllStyles($child));
            }
        }

        return $styles;
    }
}
