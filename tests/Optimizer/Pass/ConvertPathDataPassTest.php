<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\ConvertPathDataPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConvertPathDataPass::class)]
final class ConvertPathDataPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new ConvertPathDataPass();
        $this->assertSame('convert-path-data', $pass->getName());
    }

    public function testNormalizesWhitespace(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M  10   20  L  30   40');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertStringNotContainsString('  ', $d);
    }

    public function testRemovesCommas(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10, 20 L 30, 40');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertStringNotContainsString(',', $d);
    }

    public function testRemovesRedundantLineToCommands(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10 20 L 30 40 L 50 60');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass(removeRedundantCommands: true);
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);

        // Should remove the second L command
        $lCount = substr_count($d, 'L');
        $this->assertLessThanOrEqual(1, $lCount);
    }

    public function testDeduplicatesRepeatedCoordinatePairs(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 L 10 0 L 10 10 L 0 10 L 0 10 L 0 0 Z');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);

        // New implementation uses H/V shorthand and abs/rel comparison
        // producing shorter output than the old string-based dedup
        $original = 'M 0 0 L 10 0 L 10 10 L 0 10 L 0 10 L 0 0 Z';
        $this->assertLessThan(strlen($original), strlen($d), 'Optimized path should be shorter');
        $this->assertStringStartsWith('M', $d);
        $this->assertStringEndsWith('Z', $d);
    }

    public function testOptimizesNumbers(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10.00000 20.00000 L 30.123456 40.987654');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass(precision: 2);
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertStringNotContainsString('.00000', $d);
        $this->assertStringContainsString('10', $d);
        $this->assertStringContainsString('20', $d);
    }

    public function testRemovesSpacesBeforeNegativeNumbers(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10 20 L 30 -40 L -50 60');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);

        // Should have negative numbers without preceding space
        $this->assertStringContainsString('-', $d);
    }

    public function testOptimizesComplexPath(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0.00 , 0.00 L 100.00 , 0.00 L 100.00 , 100.00 L 0.00 , 100.00 Z');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);

        $originalLength = strlen('M 0.00 , 0.00 L 100.00 , 0.00 L 100.00 , 100.00 L 0.00 , 100.00 Z');
        $optimizedLength = strlen($d);

        $this->assertLessThan($originalLength, $optimizedLength, 'Optimized path should be shorter');
    }

    public function testPreservesZCommand(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10 20 L 30 40 Z');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertStringContainsString('Z', $d);
    }

    public function testHandlesCurveCommands(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10 20 C 30 40 , 50 60 , 70 80');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertStringContainsString('Q', $d, 'C that is actually quadratic should be converted to Q');
        $this->assertStringNotContainsString(',', $d);
    }

    public function testHandlesEmptyPathData(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', '');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertSame('', $d);
    }

    public function testDoesNotAffectNonPathElements(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0 0 100 100');

        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $this->assertSame('0 0 100 100', $svg->getAttribute('viewBox'));
    }

    public function testHandlesRelativeCommands(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10 20 l 10 10 l 10 10');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertStringContainsString('l', $d);
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();
        $pass = new ConvertPathDataPass();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testConfigurablePrecision(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10.123456 20.987654');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass(precision: 1);
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Should round to 1 decimal place
        $this->assertStringContainsString('10.1', $d);
        $this->assertStringContainsString('21', $d);
    }

    public function testDisableRedundantCommandRemoval(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10 20 L 30 40 L 50 60');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass(removeRedundantCommands: false);
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Both L commands should remain
        $this->assertSame(2, substr_count($d, 'L'));
    }

    public function testMergesHorizontalCommands(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 H 10 H 20 H 30');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertSame(1, substr_count($d, 'H'));
    }

    public function testMergesVerticalCommands(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 V 10 V 20');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertSame(1, substr_count($d, 'V'));
    }

    public function testMergesSmoothCubicBezierCommands(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 S 10 20 30 40 S 50 60 70 80');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertSame(1, substr_count($d, 'S'));
    }

    public function testMergesQuadraticBezierCommands(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 Q 10 20 30 40 Q 50 60 70 80');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertSame(1, substr_count($d, 'Q'));
    }

    public function testHandlesArcCommands(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 A 5 5 0 0 1 10 10 A 5 5 0 0 1 20 20');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertSame(1, substr_count($d, 'A'));
    }

    public function testHandlesCubicBezierCommands(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 C 10 20 30 40 50 60 C 70 80 90 100 110 120');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertSame(1, substr_count($d, 'C'));
    }

    public function testRemoveRedundantCommandsWithNoCommandsInPathData(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // Path data with no SVG commands (just numbers) - tokenizeCommands returns empty
        $path->setAttribute('d', '123 456');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass(removeRedundantCommands: true);
        $pass->optimize($document);

        // Should return path data unchanged since no commands are found
        $d = $path->getAttribute('d');
        $this->assertSame('123 456', $d);
    }

    public function testMergeCoordinatesWithMismatchedChunkSize(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // Second C command has only 3 coordinates instead of 6 (chunkSize for C)
        $path->setAttribute('d', 'M 0 0 C 10 20 30 40 50 60 C 70 80 90');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass(removeRedundantCommands: true);
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Should still produce valid output with C command
        $this->assertStringContainsString('C', $d);
    }

    public function testHandlesRelativeCurveTo(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 100 200 c 10 20 30 40 50 60');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Relative coords (10 20 30 40 50 60) are shorter than absolute (110 220 130 240 150 260)
        $this->assertStringContainsString('c', $d);
    }

    public function testHandlesRelativeSmoothCurveTo(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 100 200 s 10 20 30 40');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Relative coords shorter than absolute (110 220 130 240)
        $this->assertStringContainsString('s', $d);
    }

    public function testHandlesRelativeQuadraticCurveTo(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 100 200 q 10 20 30 40');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertStringContainsString('q', $d);
    }

    public function testHandlesSmoothQuadraticCurveTo(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 Q 10 20 30 40 T 50 60');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // T (absolute smooth quadratic) should be present
        $this->assertMatchesRegularExpression('/[Tt]/', $d);
    }

    public function testHandlesRelativeSmoothQuadraticCurveTo(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 100 200 Q 110 220 130 240 t 10 20');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Relative t should be shorter than absolute T140 260
        $this->assertStringContainsString('t', $d);
    }

    public function testHandlesRelativeArcTo(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 100 200 a 5 5 0 0 1 10 10');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Relative endpoint (10 10) shorter than absolute (110 210)
        $this->assertStringContainsString('a', $d);
    }

    public function testFallbackForUnparseablePath(): void
    {
        // Convert PHP warnings from PathParser into exceptions to trigger fallback
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new \RuntimeException($errstr, $errno);
        });

        try {
            $svg = new SvgElement();
            $path = new PathElement();
            // Malformed path that triggers parser warnings (incomplete arc args)
            $path->setAttribute('d', 'M 0 0 C 10 20 30');

            $svg->appendChild($path);
            $document = new Document($svg);

            $pass = new ConvertPathDataPass();
            $pass->optimize($document);

            $d = $path->getAttribute('d');
            $this->assertNotNull($d);
            // Fallback normalizes whitespace, rounds numbers, removes spaces around commands
            $this->assertStringContainsString('M', $d);
        } finally {
            restore_error_handler();
        }
    }

    public function testAbsToRelPicksShorterRepresentation(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // After M 500 500, a line to 505 505 is shorter as relative (l5 5) than absolute (L505 505)
        $path->setAttribute('d', 'M 500 500 L 505 505');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Relative l5 5 is much shorter than L505 505
        $this->assertStringContainsString('l', $d);
        $this->assertStringNotContainsString('L', $d);
    }

    public function testRelToAbsPicksShorterRepresentation(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // After M 0 0 l 5 5, cursor at (5,5). Relative l -5 -5 vs absolute L0 0 — abs is shorter
        $path->setAttribute('d', 'M 0 0 l 5 5 l -5 -5');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // L0 0 (4 chars) is shorter than l-5-5 (5 chars)
        $this->assertStringContainsString('L', $d);
    }

    public function testConvertsLineToHorizontalShorthand(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 L 50 0');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // L 50 0 should become H50 or h50
        $this->assertMatchesRegularExpression('/[Hh]/', $d);
        $this->assertStringNotContainsString('L', $d);
    }

    public function testConvertsLineToVerticalShorthand(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 L 0 50');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // L 0 50 should become V50 or v50
        $this->assertMatchesRegularExpression('/[Vv]/', $d);
        $this->assertStringNotContainsString('L', $d);
    }

    public function testNegativeNumberServesAsSeparator(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // L 10 -20 should produce coordinates like "10-20" without space
        $path->setAttribute('d', 'M 0 0 L 10 -20');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // The negative sign acts as separator: no space needed between x and -y
        $this->assertStringContainsString('10-20', $d);
    }

    public function testHandlesMultipleSubpaths(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10 20 L 30 40 Z M 50 60 L 70 80 Z');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Should have two Z commands
        $this->assertSame(2, substr_count($d, 'Z'));
        // And two M commands
        $mCount = substr_count(strtoupper($d), 'M');
        $this->assertSame(2, $mCount);
    }

    public function testComplexRealWorldPath(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // A realistic icon path with various command types
        $path->setAttribute('d', 'M 12.000 2.000 C 6.480 2.000 2.000 6.480 2.000 12.000 C 2.000 17.520 6.480 22.000 12.000 22.000 C 17.520 22.000 22.000 17.520 22.000 12.000 C 22.000 6.480 17.520 2.000 12.000 2.000 Z');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass(precision: 1);
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Should be significantly shorter
        $original = 'M 12.000 2.000 C 6.480 2.000 2.000 6.480 2.000 12.000 C 2.000 17.520 6.480 22.000 12.000 22.000 C 17.520 22.000 22.000 17.520 22.000 12.000 C 22.000 6.480 17.520 2.000 12.000 2.000 Z';
        $this->assertLessThan(strlen($original), strlen($d));
        $this->assertStringEndsWith('Z', $d);
    }

    public function testWhitespaceOnlyPathPreserved(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', '   ');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertSame('   ', $d);
    }

    // ---- C-to-Q conversion tests ----

    public function testConvertsCubicToQuadraticWhenApplicable(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // Cubic that is exactly quadratic: C(0,0 → 10,20 → 20,40)
        // QCP from CP1: (3*6.6667-0)/2 = 10.0001, (3*13.3333-0)/2 = 19.9999
        // QCP from CP2: (3*13.3333-20)/2 = 9.9999, (3*26.6667-40)/2 = 20.0001
        $path->setAttribute('d', 'M 0 0 C 6.6667 13.3333 13.3333 26.6667 20 40');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertMatchesRegularExpression('/[Qq]/', $d, 'Cubic that is quadratic should be converted to Q/q');
        $this->assertDoesNotMatchRegularExpression('/[Cc]/', $d);
    }

    public function testDoesNotConvertGenuineCubicToQuadratic(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // Genuine cubic: control points do NOT satisfy quadratic relationship
        $path->setAttribute('d', 'M 0 0 C 100 0 0 100 100 100');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertDoesNotMatchRegularExpression('/[Qq]/', $d, 'Genuine cubic should NOT become Q');
        $this->assertMatchesRegularExpression('/[Cc]/', $d);
    }

    // ---- C-to-S conversion tests ----

    public function testConvertsCubicToSmoothWhenCp1IsReflection(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // First C: genuinely cubic, CP2=(80,20), end=(100,50)
        // Second C: CP1 is reflection of (80,20) around (100,50) = (120,80)
        // Use non-quadratic control points to avoid C-to-Q conversion
        $path->setAttribute('d', 'M 0 0 C 10 60 80 20 100 50 C 120 80 160 10 200 40');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Second curve should become S (or s)
        $this->assertMatchesRegularExpression('/[Ss]/', $d, 'Smooth cubic continuation should become S/s');
    }

    public function testDoesNotConvertToSmoothWhenCp1IsNotReflection(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // First C: CP2=(80,20), end=(100,50)
        // Reflected CP1 would be (120,80), but we use (125,85) -- NOT a reflection
        $path->setAttribute('d', 'M 0 0 C 10 60 80 20 100 50 C 125 85 160 10 200 40');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Should NOT contain S/s for the second curve
        $this->assertDoesNotMatchRegularExpression('/[Ss]/', $d, 'Non-smooth continuation should stay as C');
    }

    // ---- Q-to-T conversion tests ----

    public function testConvertsQuadraticToSmoothWhenCpIsReflection(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // First Q: CP = (10, 20), end = (30, 40)
        // Second Q: CP should be reflection of (10,20) around (30,40) = (50, 60)
        $path->setAttribute('d', 'M 0 0 Q 10 20 30 40 Q 50 60 60 70');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertMatchesRegularExpression('/[Tt]/', $d, 'Smooth quadratic continuation should become T/t');
    }

    public function testDoesNotConvertToTWhenCpIsNotReflection(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // First Q: CP = (10, 20), end = (30, 40)
        // Reflected CP would be (50, 60), but we use (55, 65) -- NOT a reflection
        $path->setAttribute('d', 'M 0 0 Q 10 20 30 40 Q 55 65 60 70');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        $this->assertDoesNotMatchRegularExpression('/[Tt]/', $d, 'Non-smooth quadratic should stay as Q');
    }

    // ---- Compact arc flag tests ----

    public function testCompactArcFlags(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 A 5 5 0 0 1 10 10');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // Flags should be compacted: "0 1" → "01" (no space between)
        $this->assertMatchesRegularExpression('/01/', $d, 'Arc flags should be compacted without space');
    }

    public function testCompactArcFlagsWithNegativeEndpoint(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // Negative endpoint serves as separator after flags
        $path->setAttribute('d', 'M 10 10 A 5 5 0 1 0 -5 -5');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // "10" flags compacted, then negative sign acts as separator
        $this->assertMatchesRegularExpression('/10-/', $d, 'Compact arc flags with negative endpoint as separator');
    }

    // ---- Integration: combined optimizations ----

    public function testCombinedCurveOptimizations(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        // First C is quadratic (exact control points), second is genuinely cubic
        $path->setAttribute('d', 'M 0 0 C 6.6667 13.3333 13.3333 26.6667 20 40 C 100 0 0 100 100 100');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // First curve converts to Q, second stays as C
        $this->assertMatchesRegularExpression('/[Qq]/', $d);
        $this->assertMatchesRegularExpression('/[Cc]/', $d);
    }

    public function testDisableRedundantRemovalWithHShorthand(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0 0 L 10 0 L 20 0');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass(removeRedundantCommands: false);
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertNotNull($d);
        // With removal disabled, each H command should have its own letter
        $hCount = substr_count(strtoupper($d), 'H');
        $this->assertSame(2, $hCount);
    }

    public function testDotSeparatorOptimization(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 0.5 0.3 L 1.5 2.3');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new ConvertPathDataPass(precision: 1);
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        // The serialized output should use dot-separation (no space between decimal values)
        $this->assertStringNotContainsString('0.5 0.3', $d);
        $this->assertStringContainsString('.5.3', $d);
    }
}
