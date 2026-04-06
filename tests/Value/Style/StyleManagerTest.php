<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value\Style;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Value\Style;
use Atelier\Svg\Value\Style\StyleManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StyleManager::class)]
final class StyleManagerTest extends TestCase
{
    private function createTestDocument(): Document
    {
        $doc = Document::create(100, 100);
        $svg = $doc->getRootElement();

        // Create rect with ID
        $rect = new RectElement();
        $rect->setId('rect1');
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '10');
        $svg->appendChild($rect);
        $doc->registerElementId('rect1', $rect);

        // Create circle with class
        $circle = new CircleElement();
        $circle->addClass('shape');
        $circle->setAttribute('cx', '50');
        $circle->setAttribute('cy', '50');
        $svg->appendChild($circle);

        // Create another circle with class
        $circle2 = new CircleElement();
        $circle2->addClass('shape');
        $circle2->setAttribute('cx', '30');
        $circle2->setAttribute('cy', '30');
        $svg->appendChild($circle2);

        return $doc;
    }

    public function testStyleManagerCreation(): void
    {
        $doc = Document::create();
        $manager = new StyleManager($doc);

        $this->assertInstanceOf(StyleManager::class, $manager);
    }

    public function testDocumentStyleManager(): void
    {
        $doc = Document::create();
        $manager = $doc->styleManager();

        $this->assertInstanceOf(StyleManager::class, $manager);
    }

    public function testApplyThemeWithIdSelector(): void
    {
        $doc = $this->createTestDocument();
        $manager = $doc->styleManager();

        $theme = [
            '#rect1' => ['fill' => 'red', 'stroke' => 'blue'],
        ];

        $manager->applyTheme($theme);

        $rect = $doc->getElementById('rect1');
        $this->assertNotNull($rect);
        $this->assertEquals('red', $rect->getStyleProperty('fill'));
        $this->assertEquals('blue', $rect->getStyleProperty('stroke'));
    }

    public function testApplyThemeWithClassSelector(): void
    {
        $doc = $this->createTestDocument();
        $manager = $doc->styleManager();

        $theme = [
            '.shape' => ['fill' => 'green', 'opacity' => '0.8'],
        ];

        $manager->applyTheme($theme);

        $circles = $doc->findByClass('shape');
        $this->assertCount(2, $circles);

        foreach ($circles as $circle) {
            $this->assertEquals('green', $circle->getStyleProperty('fill'));
            $this->assertEquals('0.8', $circle->getStyleProperty('opacity'));
        }
    }

    public function testApplyThemeWithTagSelector(): void
    {
        $doc = $this->createTestDocument();
        $manager = $doc->styleManager();

        $theme = [
            'circle' => ['fill' => 'yellow', 'stroke' => 'orange'],
        ];

        $manager->applyTheme($theme);

        $circles = $doc->findByTag('circle');
        $this->assertCount(2, $circles);

        foreach ($circles as $circle) {
            $this->assertEquals('yellow', $circle->getStyleProperty('fill'));
            $this->assertEquals('orange', $circle->getStyleProperty('stroke'));
        }
    }

    public function testExtractInlineStyles(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('style', 'fill: red; stroke: blue');

        $manager = $doc->styleManager();
        $styleMap = $manager->extractInlineStyles();

        $this->assertArrayHasKey('rect1', $styleMap);
        $this->assertEquals('red', $styleMap['rect1']->get('fill'));
        $this->assertEquals('blue', $styleMap['rect1']->get('stroke'));

        // Style should be removed from element
        $this->assertNull($rect->getAttribute('style'));
    }

    public function testInlineAllStyles(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('stroke', 'blue');

        $manager = $doc->styleManager();
        $manager->inlineAllStyles();

        // Presentation attributes should be converted to inline styles
        $style = $rect->getStyle();
        $this->assertEquals('red', $style->get('fill'));
        $this->assertEquals('blue', $style->get('stroke'));
    }

    public function testExtractAllStyles(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('style', 'fill: red; stroke: blue');

        $manager = $doc->styleManager();
        $manager->extractAllStyles();

        // Inline styles should be converted to presentation attributes
        $this->assertEquals('red', $rect->getAttribute('fill'));
        $this->assertEquals('blue', $rect->getAttribute('stroke'));
    }

    public function testExtractCommonStyles(): void
    {
        $doc = Document::create();
        $svg = $doc->getRootElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('style', 'fill: red; stroke: blue; opacity: 0.8');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('style', 'fill: red; stroke: green; opacity: 0.8');
        $svg->appendChild($rect2);

        $manager = $doc->styleManager();
        $commonStyles = $manager->extractCommonStyles([$rect1, $rect2]);

        // Only fill and opacity are common
        $this->assertEquals('red', $commonStyles->get('fill'));
        $this->assertEquals('0.8', $commonStyles->get('opacity'));
        $this->assertNull($commonStyles->get('stroke'));
    }

    public function testTransformColors(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('stroke', 'blue');

        $colorMap = [
            'red' => 'green',
            'blue' => 'yellow',
        ];

        $manager = $doc->styleManager();
        $manager->transformColors($colorMap);

        $this->assertEquals('green', $rect->getAttribute('fill'));
        $this->assertEquals('yellow', $rect->getAttribute('stroke'));
    }

    public function testTransformColorsInlineStyles(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('style', 'fill: red; stroke: blue');

        $colorMap = [
            'red' => 'green',
            'blue' => 'yellow',
        ];

        $manager = $doc->styleManager();
        $manager->transformColors($colorMap);

        $style = $rect->getStyle();
        $this->assertEquals('green', $style->get('fill'));
        $this->assertEquals('yellow', $style->get('stroke'));
    }

    public function testApplyDarkMode(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', '#ffffff');
        $rect->setAttribute('stroke', '#000000');

        $manager = $doc->styleManager();
        $manager->applyDarkMode();

        $this->assertEquals('#1a1a1a', $rect->getAttribute('fill'));
        $this->assertEquals('#ffffff', $rect->getAttribute('stroke'));
    }

    public function testNormalizeColors(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('stroke', '#abc');

        $manager = $doc->styleManager();
        $manager->normalizeColors();

        $this->assertEquals('#ff0000', $rect->getAttribute('fill'));
        $this->assertEquals('#aabbcc', $rect->getAttribute('stroke'));
    }

    public function testMinifyColors(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', '#aabbcc');
        $rect->setAttribute('stroke', '#ff0000');

        $manager = $doc->styleManager();
        $manager->minifyColors();

        $this->assertEquals('#abc', $rect->getAttribute('fill'));
        $this->assertEquals('#f00', $rect->getAttribute('stroke'));
    }

    public function testGetUsedColors(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('stroke', 'blue');

        $circles = $doc->findByClass('shape');
        $circles->get(0)->setAttribute('fill', 'green');
        $circles->get(1)->setAttribute('fill', 'red'); // Duplicate

        $manager = $doc->styleManager();
        $colors = $manager->getUsedColors();

        $this->assertCount(3, $colors);
        $this->assertContains('red', $colors);
        $this->assertContains('blue', $colors);
        $this->assertContains('green', $colors);
    }

    public function testGetUsedColorsIgnoresNone(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', 'none');
        $rect->setAttribute('stroke', 'red');

        $manager = $doc->styleManager();
        $colors = $manager->getUsedColors();

        $this->assertCount(1, $colors);
        $this->assertContains('red', $colors);
        $this->assertNotContains('none', $colors);
    }

    public function testGetUsedColorsFromInlineStyles(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('style', 'fill: purple; stroke: orange; color: pink');

        $manager = $doc->styleManager();
        $colors = $manager->getUsedColors();

        $this->assertContains('purple', $colors);
        $this->assertContains('orange', $colors);
        $this->assertContains('pink', $colors);
    }

    public function testExtractCommonStylesWithEmptyArray(): void
    {
        $doc = Document::create();
        $manager = $doc->styleManager();

        $commonStyles = $manager->extractCommonStyles([]);

        $this->assertTrue($commonStyles->isEmpty());
    }

    public function testExtractCommonStylesWithSingleElement(): void
    {
        $doc = Document::create();
        $svg = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('style', 'fill: red; stroke: blue');
        $svg->appendChild($rect);

        $manager = $doc->styleManager();
        $commonStyles = $manager->extractCommonStyles([$rect]);

        $this->assertEquals('red', $commonStyles->get('fill'));
        $this->assertEquals('blue', $commonStyles->get('stroke'));
    }

    public function testExtractInlineStylesSkipsElementsWithoutId(): void
    {
        $doc = Document::create();
        $svg = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('style', 'fill: red');
        $svg->appendChild($rect);

        $manager = $doc->styleManager();
        $styleMap = $manager->extractInlineStyles();

        $this->assertEmpty($styleMap);
    }

    public function testExtractInlineStylesReturnsStyleObjects(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('style', 'fill: red; stroke: blue');

        $manager = $doc->styleManager();
        $styleMap = $manager->extractInlineStyles();

        $this->assertArrayHasKey('rect1', $styleMap);
        $this->assertInstanceOf(Style::class, $styleMap['rect1']);
    }

    public function testTransformColorsInlineStyleColor(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('style', 'color: red');

        $manager = $doc->styleManager();
        $manager->transformColors(['red' => 'blue']);

        $style = $rect->getStyle();
        $this->assertEquals('blue', $style->get('color'));
    }

    public function testApplyDarkModeWithNamedColors(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', 'white');
        $rect->setAttribute('stroke', 'black');

        $manager = $doc->styleManager();
        $manager->applyDarkMode();

        $this->assertEquals('#1a1a1a', $rect->getAttribute('fill'));
        $this->assertEquals('#ffffff', $rect->getAttribute('stroke'));
    }

    public function testApplyDarkModeWithShortHexColors(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', '#fff');
        $rect->setAttribute('stroke', '#000');

        $manager = $doc->styleManager();
        $manager->applyDarkMode();

        $this->assertEquals('#1a1a1a', $rect->getAttribute('fill'));
        $this->assertEquals('#ffffff', $rect->getAttribute('stroke'));
    }

    public function testNormalizeColorsPreservesNone(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', 'none');

        $manager = $doc->styleManager();
        $manager->normalizeColors();

        $this->assertEquals('none', $rect->getAttribute('fill'));
    }

    public function testNormalizeColorsInlineStyles(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('style', 'fill: red; stroke: #abc; color: blue');

        $manager = $doc->styleManager();
        $manager->normalizeColors();

        $style = $rect->getStyle();
        $this->assertEquals('#ff0000', $style->get('fill'));
        $this->assertEquals('#aabbcc', $style->get('stroke'));
    }

    public function testMinifyColorsPreservesNone(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', 'none');

        $manager = $doc->styleManager();
        $manager->minifyColors();

        $this->assertEquals('none', $rect->getAttribute('fill'));
    }

    public function testMinifyColorsInlineStyles(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('style', 'fill: #aabbcc; stroke: #ff0000; color: #112233');

        $manager = $doc->styleManager();
        $manager->minifyColors();

        $style = $rect->getStyle();
        $this->assertEquals('#abc', $style->get('fill'));
        $this->assertEquals('#f00', $style->get('stroke'));
    }

    public function testApplyThemeWithMultipleSelectors(): void
    {
        $doc = $this->createTestDocument();
        $manager = $doc->styleManager();

        $theme = [
            '#rect1' => ['fill' => 'red'],
            '.shape' => ['stroke' => 'blue'],
            'rect' => ['opacity' => '0.5'],
        ];

        $manager->applyTheme($theme);

        $rect = $doc->getElementById('rect1');
        $this->assertEquals('red', $rect->getStyleProperty('fill'));
        $this->assertEquals('0.5', $rect->getStyleProperty('opacity'));

        $circles = $doc->findByClass('shape');
        foreach ($circles as $circle) {
            $this->assertEquals('blue', $circle->getStyleProperty('stroke'));
        }
    }

    public function testApplyThemeWithNonExistentIdSelector(): void
    {
        $doc = $this->createTestDocument();
        $manager = $doc->styleManager();

        $manager->applyTheme([
            '#nonexistent' => ['fill' => 'red'],
        ]);

        // Should not throw, just a no-op
        $this->assertNull($doc->getElementById('nonexistent'));
    }

    public function testTransformColorsLeavesUnmappedColorsUnchanged(): void
    {
        $doc = $this->createTestDocument();
        $rect = $doc->getElementById('rect1');
        $rect->setAttribute('fill', 'red');
        $rect->setAttribute('stroke', 'green');

        $manager = $doc->styleManager();
        $manager->transformColors(['red' => 'blue']);

        $this->assertEquals('blue', $rect->getAttribute('fill'));
        $this->assertEquals('green', $rect->getAttribute('stroke'));
    }

    public function testWalkTreeWithNullRoot(): void
    {
        $doc = new Document();
        $manager = new StyleManager($doc);

        $this->assertEmpty($manager->extractInlineStyles());
    }

    public function testExtractInlineStylesSkipsNonAbstractElement(): void
    {
        $doc = Document::create();
        $svg = $doc->getRootElement();
        $mockChild = $this->createStub(ElementInterface::class);
        $svg->appendChild($mockChild);

        $manager = $doc->styleManager();
        $this->assertIsArray($manager->extractInlineStyles());
    }

    public function testTransformColorsSkipsNonAbstractElement(): void
    {
        $doc = Document::create();
        $svg = $doc->getRootElement();
        $svg->appendChild($this->createStub(ElementInterface::class));

        $manager = $doc->styleManager();
        $manager->transformColors(['red' => 'blue']);

        $this->assertTrue(true);
    }

    public function testNormalizeColorsSkipsNonAbstractElement(): void
    {
        $doc = Document::create();
        $svg = $doc->getRootElement();
        $svg->appendChild($this->createStub(ElementInterface::class));

        $manager = $doc->styleManager();
        $manager->normalizeColors();

        $this->assertTrue(true);
    }

    public function testMinifyColorsSkipsNonAbstractElement(): void
    {
        $doc = Document::create();
        $svg = $doc->getRootElement();
        $svg->appendChild($this->createStub(ElementInterface::class));

        $manager = $doc->styleManager();
        $manager->minifyColors();

        $this->assertTrue(true);
    }

    public function testGetUsedColorsSkipsNonAbstractElement(): void
    {
        $doc = Document::create();
        $svg = $doc->getRootElement();
        $svg->appendChild($this->createStub(ElementInterface::class));

        $manager = $doc->styleManager();
        $colors = $manager->getUsedColors();

        $this->assertIsArray($colors);
    }
}
