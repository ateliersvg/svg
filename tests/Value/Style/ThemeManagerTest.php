<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value\Style;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Value\Style\ThemeManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ThemeManager::class)]
final class ThemeManagerTest extends TestCase
{
    public function testApplyThemeToElement(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->addClass('primary');
        $doc->getRoot()->appendChild($rect);

        $theme = [
            '.primary' => ['fill' => '#3b82f6'],
        ];

        ThemeManager::applyTheme($doc, $theme);

        $fill = $rect->getStyleProperty('fill');
        $this->assertEquals('#3b82f6', $fill);
    }

    public function testApplyThemeByElementType(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $theme = [
            'rect' => ['fill' => 'red'],
        ];

        ThemeManager::applyTheme($doc, $theme);

        $fill = $rect->getStyleProperty('fill');
        $this->assertEquals('red', $fill);
    }

    public function testApplyUniversalTheme(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $theme = [
            '*' => ['opacity' => '0.5'],
        ];

        ThemeManager::applyTheme($doc, $theme);

        $opacity = $rect->getStyleProperty('opacity');
        $this->assertEquals('0.5', $opacity);
    }

    public function testCreateThemeFromPalette(): void
    {
        $palette = [
            'primary' => '#3b82f6',
            'secondary' => '#64748b',
        ];

        $theme = ThemeManager::createThemeFromPalette($palette);

        $this->assertArrayHasKey('.primary', $theme);
        $this->assertArrayHasKey('.primary-stroke', $theme);
        $this->assertArrayHasKey('.secondary', $theme);
        $this->assertEquals('#3b82f6', $theme['.primary']['fill']);
    }

    public function testLightTheme(): void
    {
        $theme = ThemeManager::lightTheme();

        $this->assertIsArray($theme);
        $this->assertArrayHasKey('.primary', $theme);
        $this->assertArrayHasKey('.text', $theme);
    }

    public function testDarkTheme(): void
    {
        $theme = ThemeManager::darkTheme();

        $this->assertIsArray($theme);
        $this->assertArrayHasKey('.primary', $theme);
        $this->assertArrayHasKey('.text', $theme);

        // Dark theme should have different colors than light theme
        $this->assertNotEquals(
            ThemeManager::lightTheme()['.primary']['fill'],
            $theme['.primary']['fill']
        );
    }

    public function testExtractTheme(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->addClass('test');
        $rect->setAttribute('fill', 'red');
        $doc->getRoot()->appendChild($rect);

        $theme = ThemeManager::extractTheme($doc);

        $this->assertIsArray($theme);
        // Note: extraction logic is simplified in implementation
    }

    public function testApplyThemeSkipsNonAbstractElement(): void
    {
        $element = $this->createStub(ElementInterface::class);

        ThemeManager::applyTheme($element, ['.primary' => ['fill' => 'red']]);

        $this->assertTrue(true);
    }

    public function testExtractThemeWithNullRootDocument(): void
    {
        $doc = new Document();

        $theme = ThemeManager::extractTheme($doc);

        $this->assertSame([], $theme);
    }

    public function testExtractThemeSkipsNonAbstractElement(): void
    {
        $element = $this->createStub(ElementInterface::class);

        $theme = ThemeManager::extractTheme($element);

        $this->assertSame([], $theme);
    }
}
