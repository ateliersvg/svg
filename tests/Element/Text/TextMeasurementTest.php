<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Text;

use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TextMeasurement;
use Atelier\Svg\Geometry\BoundingBox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextMeasurement::class)]
final class TextMeasurementTest extends TestCase
{
    public function testCalculateTextWidth(): void
    {
        $width = TextMeasurement::calculateTextWidth('Hello', 'Arial', 16);

        // Should be non-zero and reasonable
        $this->assertGreaterThan(0, $width);
        $this->assertLessThan(100, $width); // 5 chars shouldn't be > 100px at 16px
    }

    public function testCalculateTextWidthWithSpace(): void
    {
        $widthWithSpace = TextMeasurement::calculateTextWidth('Hello World', 'Arial', 16);
        $widthWithoutSpace = TextMeasurement::calculateTextWidth('HelloWorld', 'Arial', 16);

        // Space should make it slightly wider (space has width 0.3)
        $this->assertGreaterThan($widthWithoutSpace, $widthWithSpace);
    }

    public function testCalculateTextWidthScalesWithFontSize(): void
    {
        $width16 = TextMeasurement::calculateTextWidth('Hello', 'Arial', 16);
        $width32 = TextMeasurement::calculateTextWidth('Hello', 'Arial', 32);

        // Double font size should roughly double width
        $this->assertEqualsWithDelta($width16 * 2, $width32, 1.0);
    }

    public function testCalculateTextWidthDifferentFonts(): void
    {
        $widthArial = TextMeasurement::calculateTextWidth('Hello', 'Arial', 16);
        $widthMonospace = TextMeasurement::calculateTextWidth('Hello', 'monospace', 16);

        // Different fonts should have different widths
        $this->assertNotEquals($widthArial, $widthMonospace);
    }

    public function testMeasureTextElement(): void
    {
        $text = new TextElement();
        $text->setTextContent('Hello');
        $text->setAttribute('font-size', '16');
        $text->setAttribute('x', '10');
        $text->setAttribute('y', '20');

        $metrics = TextMeasurement::measure($text);

        $this->assertGreaterThan(0, $metrics->width);
        $this->assertEquals(16, $metrics->height);
        $this->assertEquals(1, $metrics->lines);
        $this->assertEquals(10, $metrics->x);
        $this->assertEquals(20, $metrics->y);
        $this->assertInstanceOf(BoundingBox::class, $metrics->boundingBox);
    }

    public function testMeasureWithOverrides(): void
    {
        $text = new TextElement();
        $text->setTextContent('Hello');

        $metrics = TextMeasurement::measure($text, 'Arial', 24);

        $this->assertEquals(24, $metrics->height);
    }

    public function testBreakLines(): void
    {
        $text = 'The quick brown fox jumps over the lazy dog';
        $lines = TextMeasurement::breakLines($text, 100, 'Arial', 16);

        $this->assertIsArray($lines);
        $this->assertGreaterThan(1, count($lines)); // Should wrap into multiple lines
        $this->assertNotEmpty($lines[0]);
    }

    public function testBreakLinesShortText(): void
    {
        $text = 'Hello';
        $lines = TextMeasurement::breakLines($text, 1000, 'Arial', 16);

        $this->assertCount(1, $lines); // Should fit on one line
        $this->assertEquals('Hello', $lines[0]);
    }

    public function testBreakLinesWithBreakWords(): void
    {
        // Test breaking words when they don't fit after other content
        $text = 'Short Supercalifragilisticexpialidocious';
        $lines = TextMeasurement::breakLines($text, 60, 'Arial', 12, breakWords: true);

        // First word fits, second word gets broken across lines
        $this->assertGreaterThan(1, count($lines));
    }

    public function testBreakLinesWithoutBreakWords(): void
    {
        $text = 'Supercalifragilisticexpialidocious';
        $lines = TextMeasurement::breakLines($text, 100, 'Arial', 16, breakWords: false);

        // Word should not be broken, just overflow
        $this->assertCount(1, $lines);
    }

    public function testCalculateFitSize(): void
    {
        $text = 'Hello World';
        $fontSize = TextMeasurement::calculateFitSize($text, 200, 100, 'Arial');

        $this->assertGreaterThan(0, $fontSize);
        $this->assertLessThanOrEqual(72, $fontSize); // Should not exceed max
        $this->assertGreaterThanOrEqual(8, $fontSize); // Should not be below min
    }

    public function testCalculateFitSizeSmallBox(): void
    {
        $text = 'This is a very long text that needs to fit in a small box';
        $fontSize = TextMeasurement::calculateFitSize($text, 100, 50, 'Arial');

        // Should find a small font size that fits
        $this->assertLessThan(20, $fontSize);
    }

    public function testCalculateFitSizeLargeBox(): void
    {
        $text = 'Hi';
        $fontSize = TextMeasurement::calculateFitSize($text, 500, 500, 'Arial');

        // Should use larger font for short text in large box
        $this->assertGreaterThan(30, $fontSize);
    }

    public function testFitToBox(): void
    {
        $text = new TextElement();
        $text->setTextContent('Hello World Test');
        $text->setAttribute('font-family', 'Arial');
        $text->setAttribute('font-size', '72'); // Start with large size

        TextMeasurement::fitToBox($text, 200, 100);

        $resultSize = (float) $text->getAttribute('font-size');

        // Font size should be adjusted to fit
        $this->assertLessThan(72, $resultSize);
        $this->assertGreaterThan(0, $resultSize);
    }

    public function testCalculateJustification(): void
    {
        $spacing = TextMeasurement::calculateJustification('Hello', 100, 'Arial', 16);

        // Should calculate some spacing value
        $this->assertIsFloat($spacing);
    }

    public function testCalculateJustificationWiderTarget(): void
    {
        $currentWidth = TextMeasurement::calculateTextWidth('Hello', 'Arial', 16);
        $spacing = TextMeasurement::calculateJustification('Hello', $currentWidth + 20, 'Arial', 16);

        // Positive spacing needed to stretch to wider target
        $this->assertGreaterThan(0, $spacing);
    }

    public function testMeasureMultiLine(): void
    {
        $lines = ['Hello', 'World', 'Test'];
        $metrics = TextMeasurement::measureMultiLine($lines, 'Arial', 16, 1.2, 10, 20);

        $this->assertGreaterThan(0, $metrics->width);
        $this->assertEquals(3, $metrics->lines);
        $this->assertEquals(10, $metrics->x);
        $this->assertEquals(20, $metrics->y);

        // Height should account for 3 lines with line height
        $expectedHeight = 3 * 16 * 1.2;
        $this->assertEqualsWithDelta($expectedHeight, $metrics->height, 0.1);
    }

    public function testMeasureMultiLineMaxWidth(): void
    {
        $lines = ['Hello', 'World Wide', 'Hi'];
        $metrics = TextMeasurement::measureMultiLine($lines, 'Arial', 16);

        // Width should be the widest line (World Wide)
        $wideLineWidth = TextMeasurement::calculateTextWidth('World Wide', 'Arial', 16);
        $this->assertEqualsWithDelta($wideLineWidth, $metrics->width, 0.1);
    }

    public function testCalculateBaselineOffset(): void
    {
        $offset = TextMeasurement::calculateBaselineOffset('top', 16, 100);
        $this->assertGreaterThan(0, $offset);

        $offsetMiddle = TextMeasurement::calculateBaselineOffset('middle', 16, 100);
        $this->assertGreaterThan($offset, $offsetMiddle);

        $offsetBottom = TextMeasurement::calculateBaselineOffset('bottom', 16, 100);
        $this->assertGreaterThan($offsetMiddle, $offsetBottom);
    }

    public function testFitToBoxWrapsLongText(): void
    {
        $text = new TextElement();
        $text->setTextContent('The quick brown fox jumps over the lazy dog and keeps running');
        $text->setAttribute('font-family', 'Arial');
        $text->setAttribute('font-size', '72');

        TextMeasurement::fitToBox($text, 100, 200);

        $resultSize = (float) $text->getAttribute('font-size');
        $this->assertGreaterThan(0, $resultSize);
        $this->assertLessThan(72, $resultSize);
    }

    public function testCalculateBaselineOffsetBaseline(): void
    {
        $offset = TextMeasurement::calculateBaselineOffset('baseline', 16, 100);

        $this->assertEqualsWithDelta(16 * 0.8, $offset, 0.01);
    }

    public function testCalculateBaselineOffsetDefault(): void
    {
        $offset = TextMeasurement::calculateBaselineOffset('unknown-value', 16, 100);

        $this->assertEqualsWithDelta(16 * 0.8, $offset, 0.01);
    }

    public function testEstimateWidth(): void
    {
        $width = TextMeasurement::estimateWidth('Hello', 16);

        $this->assertGreaterThan(0, $width);
        $this->assertIsFloat($width);
    }

    public function testEstimateWidthScales(): void
    {
        $width16 = TextMeasurement::estimateWidth('Hello', 16);
        $width32 = TextMeasurement::estimateWidth('Hello', 32);

        // Should scale linearly
        $this->assertEqualsWithDelta($width16 * 2, $width32, 0.1);
    }

    public function testFitsInWidth(): void
    {
        // Short text should fit
        $this->assertTrue(
            TextMeasurement::fitsInWidth('Hi', 1000, 'Arial', 16)
        );

        // Long text should not fit in small width
        $this->assertFalse(
            TextMeasurement::fitsInWidth('This is a very long text', 50, 'Arial', 16)
        );
    }

    public function testBoundingBoxCalculation(): void
    {
        $text = new TextElement();
        $text->setTextContent('Hello');
        $text->setAttribute('font-size', '16');
        $text->setAttribute('x', '10');
        $text->setAttribute('y', '20');

        $metrics = TextMeasurement::measure($text);
        $bbox = $metrics->boundingBox;

        // Bounding box should start at x
        $this->assertEquals(10, $bbox->minX);

        // Bounding box should extend from x by width
        $this->assertGreaterThan(10, $bbox->maxX);

        // Bounding box y should account for baseline
        $this->assertLessThan(20, $bbox->minY);
    }

    public function testCharacterWidthVariations(): void
    {
        // Narrow character
        $widthI = TextMeasurement::calculateTextWidth('i', 'Arial', 16);

        // Wide character
        $widthM = TextMeasurement::calculateTextWidth('m', 'Arial', 16);

        // Medium character
        $widthN = TextMeasurement::calculateTextWidth('n', 'Arial', 16);

        $this->assertLessThan($widthN, $widthI);
        $this->assertGreaterThan($widthN, $widthM);
    }

    public function testEmptyText(): void
    {
        $width = TextMeasurement::calculateTextWidth('', 'Arial', 16);
        $this->assertEquals(0, $width);
    }

    public function testBreakLinesEmpty(): void
    {
        $lines = TextMeasurement::breakLines('', 200, 'Arial', 16);
        $this->assertEmpty($lines);
    }

    public function testBreakLinesWithNewlines(): void
    {
        $text = "Line 1\nLine 2\nLine 3";
        $lines = TextMeasurement::breakLines($text, 1000, 'Arial', 16);

        // Newlines are treated as word separators, words may stay on one line if width permits
        $this->assertIsArray($lines);
        $this->assertGreaterThan(0, count($lines));
    }

    public function testMeasureWithDifferentLineHeights(): void
    {
        $lines = ['Hello', 'World'];
        $metrics1 = TextMeasurement::measureMultiLine($lines, 'Arial', 16, 1.2);
        $metrics2 = TextMeasurement::measureMultiLine($lines, 'Arial', 16, 1.5);

        // Higher line height should result in greater total height
        $this->assertGreaterThan($metrics1->height, $metrics2->height);
    }
}
