<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\StyleElement;
use Atelier\Svg\Optimizer\Analyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Analyzer::class)]
final class AnalyzerTest extends TestCase
{
    public function testAnalyze(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $report = Analyzer::analyze($doc);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('size', $report);
        $this->assertArrayHasKey('structure', $report);
        $this->assertArrayHasKey('styles', $report);
        $this->assertArrayHasKey('optimization', $report);
    }

    public function testAnalyzeSize(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $size = Analyzer::analyzeSize($doc);

        $this->assertArrayHasKey('bytes', $size);
        $this->assertArrayHasKey('formatted', $size);
        $this->assertArrayHasKey('compressed', $size);
        $this->assertArrayHasKey('compression_ratio', $size);
        $this->assertIsInt($size['bytes']);
        $this->assertIsString($size['formatted']);
        $this->assertGreaterThan(0, $size['bytes']);
    }

    public function testAnalyzeStructure(): void
    {
        $doc = Document::create();
        $rect1 = new RectElement();
        $rect2 = new RectElement();
        $doc->getRoot()->appendChild($rect1);
        $doc->getRoot()->appendChild($rect2);

        $structure = Analyzer::analyzeStructure($doc);

        $this->assertArrayHasKey('total_elements', $structure);
        $this->assertArrayHasKey('elements_by_type', $structure);
        $this->assertArrayHasKey('max_depth', $structure);
        $this->assertArrayHasKey('total_attributes', $structure);

        // Should count svg root + 2 rects = 3 elements
        $this->assertGreaterThanOrEqual(3, $structure['total_elements']);
        $this->assertArrayHasKey('rect', $structure['elements_by_type']);
        $this->assertEquals(2, $structure['elements_by_type']['rect']);
    }

    public function testAnalyzeStructureDepth(): void
    {
        $doc = Document::create();
        $group = new GroupElement();
        $rect = new RectElement();
        $group->appendChild($rect);
        $doc->getRoot()->appendChild($group);

        $structure = Analyzer::analyzeStructure($doc);

        $this->assertGreaterThanOrEqual(2, $structure['max_depth']);
    }

    public function testAnalyzeStyles(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('style', 'fill: red');
        $rect->setAttribute('stroke', 'blue');
        $rect->addClass('test');
        $doc->getRoot()->appendChild($rect);

        $styles = Analyzer::analyzeStyles($doc);

        $this->assertArrayHasKey('inline_styles', $styles);
        $this->assertArrayHasKey('presentation_attributes', $styles);
        $this->assertArrayHasKey('style_elements', $styles);
        $this->assertArrayHasKey('classes', $styles);
        $this->assertArrayHasKey('unique_color_count', $styles);

        $this->assertEquals(1, $styles['inline_styles']);
        $this->assertGreaterThan(0, $styles['classes']);
    }

    public function testAnalyzeOptimization(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('fill', 'black'); // Default value
        $doc->getRoot()->appendChild($rect);

        $optimization = Analyzer::analyzeOptimization($doc);

        $this->assertArrayHasKey('opportunities', $optimization);
        $this->assertArrayHasKey('potential_savings', $optimization);
        $this->assertIsArray($optimization['opportunities']);
    }

    public function testPrintReport(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $report = Analyzer::printReport($doc);

        $this->assertIsString($report);
        $this->assertStringContainsString('SVG Document Analysis Report', $report);
        $this->assertStringContainsString('Size:', $report);
        $this->assertStringContainsString('Structure:', $report);
        $this->assertStringContainsString('Styles:', $report);
        $this->assertStringContainsString('Optimization', $report);
    }

    public function testAnalyzeSizeFormatting(): void
    {
        $doc = Document::create();

        $size = Analyzer::analyzeSize($doc);

        // Should format bytes properly (e.g., "123 B" or "1.5 KB")
        $this->assertMatchesRegularExpression('/\d+(\.\d+)?\s+(B|KB|MB|GB)/', $size['formatted']);
        $this->assertMatchesRegularExpression('/\d+(\.\d+)?\s+(B|KB|MB|GB)/', $size['compressed']);
    }

    public function testAnalyzeStylesCountsColors(): void
    {
        $doc = Document::create();
        $rect1 = new RectElement();
        $rect1->setAttribute('fill', 'red');
        $rect2 = new RectElement();
        $rect2->setAttribute('fill', 'blue');
        $rect3 = new RectElement();
        $rect3->setAttribute('fill', 'red');

        $doc->getRoot()->appendChild($rect1);
        $doc->getRoot()->appendChild($rect2);
        $doc->getRoot()->appendChild($rect3);

        $styles = Analyzer::analyzeStyles($doc);

        // Should count unique colors (red and blue = 2)
        $this->assertEquals(2, $styles['unique_color_count']);
    }

    public function testAnalyzeStylesCountsStyleElements(): void
    {
        $doc = Document::create();
        $style = new StyleElement();
        $style->setContent('.cls { fill: red; }');
        $doc->getRoot()->appendChild($style);

        $styles = Analyzer::analyzeStyles($doc);

        $this->assertEquals(1, $styles['style_elements']);
    }

    public function testAnalyzeOptimizationReportsRedundantGroups(): void
    {
        $doc = Document::create();
        $group = new GroupElement();
        $rect = new RectElement();
        $group->appendChild($rect);
        $doc->getRoot()->appendChild($group);

        $optimization = Analyzer::analyzeOptimization($doc);

        $found = false;
        foreach ($optimization['opportunities'] as $opp) {
            if (str_contains($opp, 'redundant groups')) {
                $found = true;
            }
        }
        $this->assertTrue($found);
        $this->assertSame('medium', $optimization['potential_savings']);
    }

    public function testAnalyzeOptimizationReportsEmptyElements(): void
    {
        $doc = Document::create();
        $group = new GroupElement();
        $doc->getRoot()->appendChild($group);

        $optimization = Analyzer::analyzeOptimization($doc);

        $found = false;
        foreach ($optimization['opportunities'] as $opp) {
            if (str_contains($opp, 'empty elements')) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    public function testFormatBytesKiloByteBranch(): void
    {
        $method = new \ReflectionMethod(Analyzer::class, 'formatBytes');

        $this->assertSame('1 KB', $method->invoke(null, 1024));
        $this->assertSame('1.5 KB', $method->invoke(null, 1536));
        $this->assertSame('1 MB', $method->invoke(null, 1024 * 1024));
        $this->assertSame('0 B', $method->invoke(null, 0));
    }

    public function testPrintReportShowsNoOpportunitiesForMinimalDoc(): void
    {
        $doc = Document::create();
        $element = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('line');
            }
        };
        $element->setAttribute('fill', 'red');
        $doc->getRoot()->appendChild($element);

        $report = Analyzer::printReport($doc);

        $this->assertStringContainsString('No major opportunities found.', $report);
    }
}
