<?php

namespace Atelier\Svg\Tests\Element\Text;

use Atelier\Svg\Document;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TextProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextProcessor::class)]
final class TextProcessorTest extends TestCase
{
    public function testCreateText(): void
    {
        $helper = TextProcessor::create('Hello World', 10, 20);
        $text = $helper->getText();

        $this->assertEquals('Hello World', $text->getTextContent());
        $this->assertEquals('10', $text->getAttribute('x'));
        $this->assertEquals('20', $text->getAttribute('y'));
    }

    public function testSetFont(): void
    {
        $helper = TextProcessor::create('Test')
            ->setFont('Arial', 16, 'bold', 'italic');

        $text = $helper->getText();
        $this->assertEquals('Arial', $text->getAttribute('font-family'));
        $this->assertEquals('16', $text->getAttribute('font-size'));
        $this->assertEquals('bold', $text->getAttribute('font-weight'));
        $this->assertEquals('italic', $text->getAttribute('font-style'));
    }

    public function testAlignLeft(): void
    {
        $helper = TextProcessor::create('Test')->alignLeft();
        $this->assertEquals('start', $helper->getText()->getAttribute('text-anchor'));
    }

    public function testAlignCenter(): void
    {
        $helper = TextProcessor::create('Test')->alignCenter();
        $this->assertEquals('middle', $helper->getText()->getAttribute('text-anchor'));
    }

    public function testAlignRight(): void
    {
        $helper = TextProcessor::create('Test')->alignRight();
        $this->assertEquals('end', $helper->getText()->getAttribute('text-anchor'));
    }

    public function testBold(): void
    {
        $helper = TextProcessor::create('Test')->bold();
        $this->assertEquals('bold', $helper->getText()->getAttribute('font-weight'));
    }

    public function testItalic(): void
    {
        $helper = TextProcessor::create('Test')->italic();
        $this->assertEquals('italic', $helper->getText()->getAttribute('font-style'));
    }

    public function testUnderline(): void
    {
        $helper = TextProcessor::create('Test')->underline();
        $this->assertEquals('underline', $helper->getText()->getAttribute('text-decoration'));
    }

    public function testSetFill(): void
    {
        $helper = TextProcessor::create('Test')->setFill('#ff0000');
        $this->assertEquals('#ff0000', $helper->getText()->getAttribute('fill'));
    }

    public function testSetStroke(): void
    {
        $helper = TextProcessor::create('Test')->setStroke('#000', 2);
        $text = $helper->getText();
        $this->assertEquals('#000', $text->getAttribute('stroke'));
        $this->assertEquals('2', $text->getAttribute('stroke-width'));
    }

    public function testWrapText(): void
    {
        $text = new TextElement();
        $text->setTextContent('This is a very long text that should be wrapped');
        $text->setAttribute('font-size', '16');

        TextProcessor::wrapText($text, 100);

        // Should have created tspan elements
        $children = iterator_to_array($text->getChildren());
        $this->assertGreaterThan(0, count($children));
    }

    public function testEstimateBoundingBox(): void
    {
        $helper = TextProcessor::create('Hello', 10, 10);
        $helper->getText()->setAttribute('font-size', '16');

        $bbox = $helper->estimateBoundingBox();

        $this->assertNotNull($bbox);
        $this->assertGreaterThan(0, $bbox->getWidth());
        $this->assertGreaterThan(0, $bbox->getHeight());
    }

    public function testFollowPath(): void
    {
        $helper = TextProcessor::create('Follow me');
        $textPath = $helper->followPath('path1', 'New text');

        $this->assertEquals('textPath', $textPath->getTagName());
        $this->assertEquals('#path1', $textPath->getAttribute('href'));
        $this->assertEquals('New text', $textPath->getTextContent());
    }

    // Tests for new text effects features

    public function testAddShadowWithDocument(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $helper = TextProcessor::create('Shadow Text', 100, 100, $doc);
        $helper->addShadow(dx: 3, dy: 3, blur: 5, color: '#000000', opacity: 0.5);

        $text = $helper->getText();
        $filter = $text->getAttribute('filter');

        $this->assertNotNull($filter);
        $this->assertStringStartsWith('url(#text-shadow-', $filter);
        $this->assertStringEndsWith(')', $filter);
    }

    public function testAddShadowWithoutDocumentThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Document is required for adding shadow effects');

        $helper = TextProcessor::create('Shadow Text', 100, 100);
        $helper->addShadow();
    }

    public function testAddShadowWithCustomFilterId(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $helper = TextProcessor::create('Shadow Text', 100, 100, $doc);
        $helper->addShadow(filterId: 'custom-shadow');

        $text = $helper->getText();
        $this->assertEquals('url(#custom-shadow)', $text->getAttribute('filter'));
    }

    public function testAddGlowWithDocument(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $helper = TextProcessor::create('Glow Text', 100, 100, $doc);
        $helper->addGlow(color: '#3b82f6', strength: 3, opacity: 0.8);

        $text = $helper->getText();
        $filter = $text->getAttribute('filter');

        $this->assertNotNull($filter);
        $this->assertStringStartsWith('url(#text-glow-', $filter);
        $this->assertStringEndsWith(')', $filter);
    }

    public function testAddGlowWithoutDocumentThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Document is required for adding glow effects');

        $helper = TextProcessor::create('Glow Text', 100, 100);
        $helper->addGlow();
    }

    public function testAddGlowWithCustomFilterId(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $helper = TextProcessor::create('Glow Text', 100, 100, $doc);
        $helper->addGlow(filterId: 'custom-glow');

        $text = $helper->getText();
        $this->assertEquals('url(#custom-glow)', $text->getAttribute('filter'));
    }

    public function testAddOutline(): void
    {
        $helper = TextProcessor::create('Outline Text', 100, 100);
        $helper->addOutline(color: '#000000', width: 2.5);

        $text = $helper->getText();
        $this->assertEquals('#000000', $text->getAttribute('stroke'));
        $this->assertEquals('2.5', $text->getAttribute('stroke-width'));
        $this->assertEquals('round', $text->getAttribute('stroke-linejoin'));
        $this->assertEquals('round', $text->getAttribute('stroke-linecap'));
        $this->assertEquals('stroke fill', $text->getAttribute('paint-order'));
    }

    public function testAddOutlineWithFillColor(): void
    {
        $helper = TextProcessor::create('Outline Text', 100, 100);
        $helper->addOutline(color: '#000000', width: 2, fillColor: '#ffffff');

        $text = $helper->getText();
        $this->assertEquals('#000000', $text->getAttribute('stroke'));
        $this->assertEquals('#ffffff', $text->getAttribute('fill'));
    }

    public function testSetGradientFillWithDocument(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $stops = [
            ['color' => '#FF6B6B', 'offset' => 0],
            ['color' => '#4ECDC4', 'offset' => 100],
        ];

        $helper = TextProcessor::create('Gradient Text', 100, 100, $doc);
        $helper->setGradientFill($stops);

        $text = $helper->getText();
        $fill = $text->getAttribute('fill');

        $this->assertNotNull($fill);
        $this->assertStringStartsWith('url(#text-gradient-', $fill);
        $this->assertStringEndsWith(')', $fill);
    }

    public function testSetGradientFillWithoutDocumentThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Document is required for gradient fills');

        $stops = [
            ['color' => '#FF6B6B', 'offset' => 0],
            ['color' => '#4ECDC4', 'offset' => 100],
        ];

        $helper = TextProcessor::create('Gradient Text', 100, 100);
        $helper->setGradientFill($stops);
    }

    public function testSetGradientFillWithCustomDirection(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $stops = [
            ['color' => '#667eea', 'offset' => 0],
            ['color' => '#764ba2', 'offset' => 100],
        ];

        $helper = TextProcessor::create('Gradient Text', 100, 100, $doc);
        // Vertical gradient
        $helper->setGradientFill($stops, x1: 0, y1: 0, x2: 0, y2: 100);

        $text = $helper->getText();
        $fill = $text->getAttribute('fill');

        $this->assertNotNull($fill);
        $this->assertStringStartsWith('url(#', $fill);
    }

    public function testSetGradientFillWithOpacity(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $stops = [
            ['color' => '#ffffff', 'offset' => 0, 'opacity' => 1.0],
            ['color' => '#ffffff', 'offset' => 100, 'opacity' => 0.2],
        ];

        $helper = TextProcessor::create('Gradient Text', 100, 100, $doc);
        $helper->setGradientFill($stops);

        $text = $helper->getText();
        $fill = $text->getAttribute('fill');

        $this->assertNotNull($fill);
        $this->assertStringStartsWith('url(#text-gradient-', $fill);
    }

    public function testSetRadialGradientFillWithDocument(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $stops = [
            ['color' => '#ffeaa7', 'offset' => 0],
            ['color' => '#fdcb6e', 'offset' => 50],
            ['color' => '#e17055', 'offset' => 100],
        ];

        $helper = TextProcessor::create('Radial Text', 100, 100, $doc);
        $helper->setRadialGradientFill($stops);

        $text = $helper->getText();
        $fill = $text->getAttribute('fill');

        $this->assertNotNull($fill);
        $this->assertStringStartsWith('url(#text-radial-gradient-', $fill);
        $this->assertStringEndsWith(')', $fill);
    }

    public function testSetRadialGradientFillWithoutDocumentThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Document is required for gradient fills');

        $stops = [
            ['color' => '#ffeaa7', 'offset' => 0],
            ['color' => '#e17055', 'offset' => 100],
        ];

        $helper = TextProcessor::create('Radial Text', 100, 100);
        $helper->setRadialGradientFill($stops);
    }

    public function testSetRadialGradientFillWithCustomParameters(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $stops = [
            ['color' => '#ffeaa7', 'offset' => 0],
            ['color' => '#e17055', 'offset' => 100],
        ];

        $helper = TextProcessor::create('Radial Text', 100, 100, $doc);
        $helper->setRadialGradientFill($stops, cx: 30, cy: 30, r: 70);

        $text = $helper->getText();
        $fill = $text->getAttribute('fill');

        $this->assertNotNull($fill);
        $this->assertStringStartsWith('url(#', $fill);
    }

    public function testSetPatternFill(): void
    {
        $helper = TextProcessor::create('Pattern Text', 100, 100);
        $helper->setPatternFill('my-pattern');

        $text = $helper->getText();
        $this->assertEquals('url(#my-pattern)', $text->getAttribute('fill'));
    }

    public function testSetDocument(): void
    {
        $doc = new Document();
        $helper = TextProcessor::create('Test', 100, 100);

        $result = $helper->setDocument($doc);

        // Should return self for chaining
        $this->assertSame($helper, $result);

        // Should now be able to use shadow without error
        $svg = new SvgElement();
        $doc->setRootElement($svg);
        $helper->addShadow();

        $text = $helper->getText();
        $this->assertNotNull($text->getAttribute('filter'));
    }

    public function testCreateWithDocument(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $helper = TextProcessor::create('Test', 100, 100, $doc);

        // Should be able to use shadow immediately
        $helper->addShadow();

        $text = $helper->getText();
        $this->assertNotNull($text->getAttribute('filter'));
    }

    public function testSetDominantBaseline(): void
    {
        $helper = TextProcessor::create('Test')->setDominantBaseline('middle');
        $this->assertEquals('middle', $helper->getText()->getAttribute('dominant-baseline'));
    }

    public function testSetLetterSpacing(): void
    {
        $helper = TextProcessor::create('Test')->setLetterSpacing(2.5);
        $this->assertEquals('2.5', $helper->getText()->getAttribute('letter-spacing'));
    }

    public function testSetWordSpacing(): void
    {
        $helper = TextProcessor::create('Test')->setWordSpacing(5.0);
        $this->assertEquals('5', $helper->getText()->getAttribute('word-spacing'));
    }

    public function testSetFontWithOnlyFamily(): void
    {
        $helper = TextProcessor::create('Test')->setFont('Helvetica');
        $text = $helper->getText();
        $this->assertEquals('Helvetica', $text->getAttribute('font-family'));
        $this->assertNull($text->getAttribute('font-size'));
        $this->assertNull($text->getAttribute('font-weight'));
        $this->assertNull($text->getAttribute('font-style'));
    }

    public function testSetStrokeWithoutWidth(): void
    {
        $helper = TextProcessor::create('Test')->setStroke('#ff0000');
        $text = $helper->getText();
        $this->assertEquals('#ff0000', $text->getAttribute('stroke'));
        $this->assertNull($text->getAttribute('stroke-width'));
    }

    public function testFollowPathWithoutContent(): void
    {
        $helper = TextProcessor::create('Existing content');
        $textPath = $helper->followPath('mypath');

        $this->assertEquals('#mypath', $textPath->getAttribute('href'));
        $this->assertEquals('Existing content', $textPath->getTextContent());
        $this->assertEquals('', $helper->getText()->getTextContent());
    }

    public function testEstimateBoundingBoxWithMiddleAnchor(): void
    {
        $helper = TextProcessor::create('Hello', 100, 50);
        $helper->getText()->setAttribute('font-size', '20');
        $helper->getText()->setAttribute('text-anchor', 'middle');

        $bbox = $helper->estimateBoundingBox();
        $this->assertLessThan(100, $bbox->getX());
    }

    public function testEstimateBoundingBoxWithEndAnchor(): void
    {
        $helper = TextProcessor::create('Hello', 100, 50);
        $helper->getText()->setAttribute('font-size', '20');
        $helper->getText()->setAttribute('text-anchor', 'end');

        $bbox = $helper->estimateBoundingBox();
        $this->assertLessThan(100, $bbox->getX());
    }

    public function testWrapWords(): void
    {
        $text = new TextElement();
        $text->setTextContent('This is a long text that should be wrapped into multiple lines');
        $text->setAttribute('font-size', '16');

        TextProcessor::wrapWords($text, 100);

        $children = iterator_to_array($text->getChildren());
        $this->assertGreaterThan(0, count($children));
    }

    public function testWrapWordsWithMaxHeight(): void
    {
        $text = new TextElement();
        $text->setTextContent('This is a very long text that should be wrapped and then truncated by max height');
        $text->setAttribute('font-size', '16');

        TextProcessor::wrapWords($text, 80, maxHeight: 20);

        $children = iterator_to_array($text->getChildren());
        // With maxHeight of 20 and fontSize 16, only ~1 line fits
        $this->assertLessThanOrEqual(2, count($children));
    }

    public function testWrapTextWithZeroWidth(): void
    {
        $text = new TextElement();
        $text->setTextContent('Test');
        $text->setAttribute('font-size', '16');

        TextProcessor::wrapText($text, 0);

        // charsPerLine <= 0 causes early return, text unchanged
        $this->assertEquals('Test', $text->getTextContent());
    }

    public function testChainingEffects(): void
    {
        $doc = new Document();
        $svg = new SvgElement();
        $doc->setRootElement($svg);

        $stops = [
            ['color' => '#FF6B6B', 'offset' => 0],
            ['color' => '#4ECDC4', 'offset' => 100],
        ];

        $helper = TextProcessor::create('Multi Effect', 100, 100, $doc)
            ->setFont('Arial', 48, 'bold')
            ->alignCenter()
            ->setGradientFill($stops)
            ->addShadow(dx: 2, dy: 2, blur: 4);

        $text = $helper->getText();

        // Verify all effects are applied
        $this->assertEquals('Arial', $text->getAttribute('font-family'));
        $this->assertEquals('48', $text->getAttribute('font-size'));
        $this->assertEquals('bold', $text->getAttribute('font-weight'));
        $this->assertEquals('middle', $text->getAttribute('text-anchor'));
        $this->assertStringStartsWith('url(#text-gradient-', $text->getAttribute('fill'));
        $this->assertStringStartsWith('url(#text-shadow-', $text->getAttribute('filter'));
    }
}
