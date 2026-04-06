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
        $this->assertSame('M0 0 L10 0 10 10 0 10 0 0 Z', $d);
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
        $this->assertStringContainsString('C', $d);
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
}
