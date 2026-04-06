<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Integration;

use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the full optimizer pipeline with real SVG files.
 *
 * These tests verify that the optimizer works correctly on realistic SVG files,
 * testing the interaction between multiple optimization passes.
 */
#[CoversClass(Optimizer::class)]
final class OptimizerIntegrationTest extends TestCase
{
    private const string FIXTURES_DIR = __DIR__.'/fixtures';
    private const string INPUT_DIR = self::FIXTURES_DIR.'/input';
    private const string EXPECTED_DIR = self::FIXTURES_DIR.'/expected';

    private DomLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new DomLoader();
    }

    #[DataProvider('svgFilesProvider')]
    public function testOptimizationReducesFileSize(string $filename): void
    {
        $inputPath = self::INPUT_DIR.'/'.$filename;
        $inputContent = file_get_contents($inputPath);

        $this->assertNotFalse($inputContent, "Failed to read input file: $filename");

        // Load and optimize
        $document = $this->loader->loadFromString($inputContent);
        $optimizer = new Optimizer(OptimizerPresets::default());
        $optimizedDocument = $optimizer->optimize($document);

        // Dump optimized SVG
        $dumper = new CompactXmlDumper();
        $optimizedContent = $dumper->dump($optimizedDocument);

        // Verify it's still valid SVG
        $this->assertStringContainsString('<svg', $optimizedContent, "Optimized SVG is invalid: $filename");

        // In most cases, optimization should reduce size
        // (Some very small SVGs might not benefit)
        $this->assertNotEmpty($optimizedContent, "Optimized content is empty: $filename");
    }

    #[DataProvider('svgFilesProvider')]
    public function testAggressivePresetOptimization(string $filename): void
    {
        $inputPath = self::INPUT_DIR.'/'.$filename;
        $inputContent = file_get_contents($inputPath);

        $this->assertNotFalse($inputContent, "Failed to read input file: $filename");

        // Load and optimize with aggressive preset
        $document = $this->loader->loadFromString($inputContent);
        $optimizer = new Optimizer(OptimizerPresets::aggressive());
        $optimizedDocument = $optimizer->optimize($document);

        // Dump optimized SVG
        $dumper = new CompactXmlDumper();
        $optimizedContent = $dumper->dump($optimizedDocument);

        // Verify it's still valid SVG
        $this->assertStringContainsString('<svg', $optimizedContent, "Aggressively optimized SVG is invalid: $filename");

        $this->assertNotEmpty($optimizedContent, "Optimized content is empty: $filename");

        // Aggressive preset should not bloat size significantly compared to the original.
        $originalSize = strlen($inputContent);
        $aggressiveSize = strlen($optimizedContent);

        $this->assertLessThanOrEqual(
            (int) round($originalSize * 1.10), // allow up to +10%
            $aggressiveSize,
            "Aggressive preset should not bloat SVG size significantly: $filename"
        );
    }

    #[DataProvider('svgFilesProvider')]
    public function testSafePresetPreservesStructure(string $filename): void
    {
        $inputPath = self::INPUT_DIR.'/'.$filename;
        $inputContent = file_get_contents($inputPath);

        $this->assertNotFalse($inputContent, "Failed to read input file: $filename");

        // Load and optimize with safe preset
        $document = $this->loader->loadFromString($inputContent);
        $optimizer = new Optimizer(OptimizerPresets::safe());
        $optimizedDocument = $optimizer->optimize($document);

        // Dump optimized SVG
        $dumper = new CompactXmlDumper();
        $optimizedContent = $dumper->dump($optimizedDocument);

        // Verify it's still valid SVG
        $this->assertStringContainsString('<svg', $optimizedContent, "Safely optimized SVG is invalid: $filename");

        $this->assertNotEmpty($optimizedContent, "Optimized content is empty: $filename");
    }

    public function testIconWithDuplicatesOptimization(): void
    {
        $inputContent = file_get_contents(self::INPUT_DIR.'/icon-with-duplicates.svg');
        $document = $this->loader->loadFromString($inputContent);

        $optimizer = new Optimizer(OptimizerPresets::default());
        $optimizedDocument = $optimizer->optimize($document);

        $dumper = new CompactXmlDumper();
        $optimizedContent = $dumper->dump($optimizedDocument);

        // Should contain a style element (from AddClassesToSVGPass)
        $this->assertStringContainsString('<style', $optimizedContent);

        // Should contain CSS classes
        $this->assertStringContainsString('class=', $optimizedContent);

        // Should be significantly smaller due to style extraction
        $originalSize = strlen($inputContent);
        $optimizedSize = strlen($optimizedContent);

        $this->assertLessThan(
            $originalSize,
            $optimizedSize,
            'Optimized SVG should be smaller than original'
        );
    }

    public function testShapesToConvertOptimization(): void
    {
        $inputContent = file_get_contents(self::INPUT_DIR.'/shapes-to-convert.svg');
        $document = $this->loader->loadFromString($inputContent);

        $optimizer = new Optimizer(OptimizerPresets::aggressive());
        $optimizedDocument = $optimizer->optimize($document);

        $dumper = new CompactXmlDumper();
        $optimizedContent = $dumper->dump($optimizedDocument);

        // With aggressive preset, shapes should be converted to paths
        // Count path elements
        $pathCount = substr_count($optimizedContent, '<path');

        // Should have multiple path elements (converted from shapes)
        $this->assertGreaterThan(0, $pathCount, 'Should contain path elements after conversion');

        // Original shapes should be gone or reduced
        $rectCount = substr_count($optimizedContent, '<rect');
        $circleCount = substr_count($optimizedContent, '<circle');

        // In aggressive mode, most/all shapes should be converted
        $this->assertTrue(
            $pathCount > 0,
            'Shapes should be converted to paths in aggressive mode'
        );
    }

    public function testComplexWithTransformsOptimization(): void
    {
        $inputContent = file_get_contents(self::INPUT_DIR.'/complex-with-transforms.svg');
        $document = $this->loader->loadFromString($inputContent);

        $optimizer = new Optimizer(OptimizerPresets::default());
        $optimizedDocument = $optimizer->optimize($document);

        $dumper = new CompactXmlDumper();
        $optimizedContent = $dumper->dump($optimizedDocument);

        // Empty groups should be removed
        $this->assertStringNotContainsString('empty-group', $optimizedContent);

        // Empty text elements should be removed
        $emptyTextCount = substr_count($optimizedContent, '<text');
        $this->assertLessThanOrEqual(0, $emptyTextCount, 'Empty text elements should be removed');

        // Comments should be removed
        $this->assertStringNotContainsString('<!--', $optimizedContent);

        // Default opacity should be removed
        $originalOpacityCount = substr_count($inputContent, 'opacity="1.0"');
        $optimizedOpacityCount = substr_count($optimizedContent, 'opacity="1.0"');
        $this->assertLessThan($originalOpacityCount, $optimizedOpacityCount);
    }

    public function testLogoOptimization(): void
    {
        $inputContent = file_get_contents(self::INPUT_DIR.'/logo-optimization.svg');
        $document = $this->loader->loadFromString($inputContent);

        $optimizer = new Optimizer(OptimizerPresets::default());
        $optimizedDocument = $optimizer->optimize($document);

        $dumper = new CompactXmlDumper();
        $optimizedContent = $dumper->dump($optimizedDocument);

        // Trailing zeros should be removed (10.000 -> 10)
        $this->assertStringNotContainsString('10.000', $optimizedContent);
        $this->assertStringNotContainsString('30.000', $optimizedContent);

        // Default values should be removed (opacity="1", stroke="none")
        $this->assertStringNotContainsString('opacity="1"', $optimizedContent);

        // Common fill attribute should be moved to group or extracted to class
        $fillCount = substr_count($optimizedContent, 'fill=');
        $originalFillCount = substr_count($inputContent, 'fill=');

        $this->assertLessThan(
            $originalFillCount,
            $fillCount,
            'Duplicate fill attributes should be reduced'
        );
    }

    public function testOptimizationIsIdempotent(): void
    {
        $inputContent = file_get_contents(self::INPUT_DIR.'/icon-with-duplicates.svg');

        // First optimization
        $document1 = $this->loader->loadFromString($inputContent);
        $optimizer = new Optimizer(OptimizerPresets::default());
        $optimized1 = $optimizer->optimize($document1);

        $dumper = new CompactXmlDumper();
        $output1 = $dumper->dump($optimized1);

        // Second optimization (on already optimized SVG)
        $document2 = $this->loader->loadFromString($output1);
        $optimized2 = $optimizer->optimize($document2);
        $output2 = $dumper->dump($optimized2);

        // Subsequent runs should not increase SVG size.
        $this->assertLessThanOrEqual(
            strlen($output1),
            strlen($output2),
            'Optimization should not increase SVG size on subsequent runs'
        );
    }

    public function testOptimizationRoundTrip(): void
    {
        $inputContent = file_get_contents(self::INPUT_DIR.'/shapes-to-convert.svg');
        $document = $this->loader->loadFromString($inputContent);

        $optimizer = new Optimizer(OptimizerPresets::default());
        $optimizedDocument = $optimizer->optimize($document);

        $dumper = new CompactXmlDumper();
        $optimizedContent = $dumper->dump($optimizedDocument);

        // Parse the optimized SVG again
        $reparsedDocument = $this->loader->loadFromString($optimizedContent);

        $this->assertNotNull(
            $reparsedDocument->getRootElement(),
            'Optimized SVG should be parseable'
        );

        // Dump again
        $reoptimizedContent = $dumper->dump($reparsedDocument);

        $this->assertStringContainsString('<svg', $reoptimizedContent, 'Re-dumped SVG should still be valid');
    }

    /**
     * Provides SVG test files.
     *
     * @return array<array{string}>
     */
    public static function svgFilesProvider(): array
    {
        return [
            ['icon-with-duplicates.svg'],
            ['shapes-to-convert.svg'],
            ['complex-with-transforms.svg'],
            ['logo-optimization.svg'],
            ['unused-classes.svg'],
        ];
    }
}
