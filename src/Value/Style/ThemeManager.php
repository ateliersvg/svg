<?php

declare(strict_types=1);

namespace Atelier\Svg\Value\Style;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Manages theme application to SVG documents.
 *
 * Allows applying consistent color schemes and styles across elements
 * based on class names or element types.
 */
final class ThemeManager
{
    /**
     * Applies a theme to a document or element.
     *
     * Theme format:
     * [
     *   '.class-name' => ['fill' => '#color', 'stroke' => '#color'],
     *   'rect' => ['fill' => '#color'],
     *   '*' => ['font-family' => 'Arial']
     * ]
     *
     * @param Document|ElementInterface            $root  The document or element to apply the theme to
     * @param array<string, array<string, string>> $theme
     */
    public static function applyTheme(Document|ElementInterface $root, array $theme): void
    {
        // If Document is passed, start from root element
        if ($root instanceof Document) {
            $element = $root->getRoot();
            // $root->getRoot() returns ?ElementInterface, but if it is null, we can't do anything
            if (null !== $element) {
                self::applyTheme($element, $theme);
            }

            return;
        }

        self::applyThemeToElement($root, $theme);

        if ($root instanceof ContainerElementInterface) {
            foreach ($root->getChildren() as $child) {
                self::applyTheme($child, $theme);
            }
        }
    }

    /**
     * Applies theme styles to a single element.
     *
     * @param array<string, array<string, string>> $theme
     */
    private static function applyThemeToElement(ElementInterface $element, array $theme): void
    {
        if (!$element instanceof AbstractElement) {
            return;
        }

        $stylesToApply = [];

        // Apply universal styles (*)
        if (isset($theme['*'])) {
            $stylesToApply = array_merge($stylesToApply, $theme['*']);
        }

        // Apply element type styles (e.g., 'rect', 'circle')
        $tagName = $element->getTagName();
        if (isset($theme[$tagName])) {
            $stylesToApply = array_merge($stylesToApply, $theme[$tagName]);
        }

        // Apply class-based styles (e.g., '.primary')
        foreach ($element->getClasses() as $className) {
            $selector = '.'.$className;
            if (isset($theme[$selector])) {
                $stylesToApply = array_merge($stylesToApply, $theme[$selector]);
            }
        }

        // Apply the accumulated styles
        if (!empty($stylesToApply)) {
            StyleUtils::setStyles($element, $stylesToApply);
        }
    }

    /**
     * Creates a theme from a color palette.
     *
     * @param array<string, string> $colors Color palette (e.g., ['primary' => '#3b82f6', 'secondary' => '#1e40af'])
     *
     * @return array<string, array<string, string>>
     */
    public static function createThemeFromPalette(array $colors): array
    {
        $theme = [];

        foreach ($colors as $name => $color) {
            $theme[".{$name}"] = ['fill' => $color];
            $theme[".{$name}-stroke"] = ['stroke' => $color];
            $theme[".{$name}-text"] = ['fill' => $color];
        }

        return $theme;
    }

    /**
     * Extracts the current theme from a document.
     *
     * Analyzes all elements and their styles to generate a theme definition.
     *
     * @return array<string, array<string, string>>
     */
    public static function extractTheme(Document|ElementInterface $root): array
    {
        // If Document is passed, start from root element
        if ($root instanceof Document) {
            $element = $root->getRoot();
            if (null !== $element) {
                return self::extractTheme($element);
            }

            return [];
        }

        $theme = [];

        self::extractThemeFromElement($root, $theme);

        if ($root instanceof ContainerElementInterface) {
            foreach ($root->getChildren() as $child) {
                self::extractThemeFromElement($child, $theme);
            }
        }

        return $theme;
    }

    /**
     * Extracts theme information from a single element.
     *
     * @param array<string, array<string, string>> $theme
     */
    private static function extractThemeFromElement(ElementInterface $element, array &$theme): void
    {
        if (!$element instanceof AbstractElement) {
            return;
        }

        $styles = StyleUtils::getAllStyles($element);

        if ($styles->isEmpty()) {
            return;
        }

        // Group by class
        foreach ($element->getClasses() as $className) {
            $selector = '.'.$className;

            if (!isset($theme[$selector])) {
                $theme[$selector] = [];
            }

            // Merge styles (this is simplified - real extraction would be more sophisticated)
            $theme[$selector] = array_merge($theme[$selector], $styles->getAll());
        }
    }

    /**
     * Predefined themes.
     */

    /**
     * Gets a light theme.
     *
     * @return array<string, array<string, string>>
     */
    public static function lightTheme(): array
    {
        return [
            '*' => [
                'font-family' => 'system-ui, sans-serif',
            ],
            '.primary' => [
                'fill' => '#3b82f6',
            ],
            '.secondary' => [
                'fill' => '#64748b',
            ],
            '.success' => [
                'fill' => '#10b981',
            ],
            '.warning' => [
                'fill' => '#f59e0b',
            ],
            '.danger' => [
                'fill' => '#ef4444',
            ],
            '.text' => [
                'fill' => '#1f2937',
            ],
            '.background' => [
                'fill' => '#ffffff',
            ],
        ];
    }

    /**
     * Gets a dark theme.
     *
     * @return array<string, array<string, string>>
     */
    public static function darkTheme(): array
    {
        return [
            '*' => [
                'font-family' => 'system-ui, sans-serif',
            ],
            '.primary' => [
                'fill' => '#60a5fa',
            ],
            '.secondary' => [
                'fill' => '#94a3b8',
            ],
            '.success' => [
                'fill' => '#34d399',
            ],
            '.warning' => [
                'fill' => '#fbbf24',
            ],
            '.danger' => [
                'fill' => '#f87171',
            ],
            '.text' => [
                'fill' => '#f9fafb',
            ],
            '.background' => [
                'fill' => '#111827',
            ],
        ];
    }
}
