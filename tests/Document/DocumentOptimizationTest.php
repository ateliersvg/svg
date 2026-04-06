<?php

namespace Atelier\Svg\Tests\Document;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
final class DocumentOptimizationTest extends TestCase
{
    public function testOptimize(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $result = $doc->optimize();

        $this->assertSame($doc, $result);
    }

    public function testOptimizeWithPreset(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $result = $doc->optimize('aggressive');

        $this->assertSame($doc, $result);
    }

    public function testCleanupDefs(): void
    {
        $doc = Document::create();

        $result = $doc->cleanupDefs();

        $this->assertSame($doc, $result);
    }

    public function testRoundValues(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('x', '10.123456');
        $doc->getRoot()->appendChild($rect);

        $result = $doc->roundValues(2);

        $this->assertSame($doc, $result);
    }

    public function testOptimizeColors(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('fill', '#ffffff');
        $doc->getRoot()->appendChild($rect);

        $result = $doc->optimizeColors();

        $this->assertSame($doc, $result);
    }

    public function testInlineStyles(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('style', 'fill: red');
        $doc->getRoot()->appendChild($rect);

        $result = $doc->inlineStyles();

        $this->assertSame($doc, $result);
    }

    public function testSimplifyPaths(): void
    {
        $doc = Document::create();

        $result = $doc->simplifyPaths(0.5);

        $this->assertSame($doc, $result);
    }

    public function testRemoveHidden(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('display', 'none');
        $doc->getRoot()->appendChild($rect);

        $result = $doc->removeHidden();

        $this->assertSame($doc, $result);
    }

    public function testAnalyze(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $report = $doc->analyze();

        $this->assertIsArray($report);
        $this->assertArrayHasKey('size', $report);
        $this->assertArrayHasKey('structure', $report);
    }

    public function testPrintAnalysis(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $report = $doc->printAnalysis();

        $this->assertIsString($report);
        $this->assertStringContainsString('SVG Document Analysis Report', $report);
    }

    public function testApplyTheme(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->addClass('primary');
        $doc->getRoot()->appendChild($rect);

        $theme = [
            '.primary' => ['fill' => '#3b82f6'],
        ];

        $result = $doc->applyTheme($theme);

        $this->assertSame($doc, $result);
    }

    public function testGetRoot(): void
    {
        $doc = Document::create();

        $root = $doc->getRoot();

        $this->assertSame($doc->getRootElement(), $root);
    }
}
