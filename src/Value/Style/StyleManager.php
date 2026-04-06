<?php

declare(strict_types=1);

namespace Atelier\Svg\Value\Style;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Value\Style;

/**
 * Document-level style management utilities.
 *
 * Provides functionality for:
 * - Applying themes to documents
 * - Extracting common styles to CSS
 * - Inlining CSS rules to inline styles
 * - Color scheme transformations
 */
final readonly class StyleManager
{
    public function __construct(
        private Document $document,
    ) {
    }

    /**
     * Applies a theme to the document.
     *
     * Theme format:
     * [
     *     '.class-name' => ['fill' => '#color', 'stroke' => '#color'],
     *     '#element-id' => ['opacity' => '0.8'],
     *     'rect' => ['rx' => '5'],
     * ]
     *
     * @param array<string, array<string, string>> $theme
     */
    public function applyTheme(array $theme): void
    {
        foreach ($theme as $selector => $styles) {
            $this->applyStylesToSelector($selector, $styles);
        }
    }

    /**
     * Applies styles to elements matching a selector.
     *
     * @param array<string, string> $styles
     */
    private function applyStylesToSelector(string $selector, array $styles): void
    {
        $elements = [];

        // Handle different selector types
        if (str_starts_with($selector, '#')) {
            // ID selector
            $id = substr($selector, 1);
            $element = $this->document->getElementById($id);
            if ($element) {
                $elements = [$element];
            }
        } elseif (str_starts_with($selector, '.')) {
            // Class selector
            $className = substr($selector, 1);
            $elements = $this->document->findByClass($className)->toArray();
        } else {
            // Tag selector
            $elements = $this->document->findByTag($selector)->toArray();
        }

        // Apply styles to matched elements
        foreach ($elements as $element) {
            if ($element instanceof AbstractElement) {
                $element->setStyles($styles);
            }
        }
    }

    /**
     * Extracts all inline styles to a style element.
     * Useful for creating a CSS stylesheet from inline styles.
     *
     * @return array<string, Style> Map of element IDs to their styles
     */
    public function extractInlineStyles(): array
    {
        $styleMap = [];

        $this->walkTree($this->document->getRootElement(), function (ElementInterface $element) use (&$styleMap) {
            if (!$element instanceof AbstractElement) {
                return;
            }

            $style = $element->getStyle();
            if (!$style->isEmpty()) {
                $id = $element->getId();
                if ($id) {
                    $styleMap[$id] = $style;
                    $element->removeAttribute('style');
                }
            }
        });

        return $styleMap;
    }

    /**
     * Inlines all styles to elements.
     * Converts presentation attributes to inline styles.
     */
    public function inlineAllStyles(): void
    {
        $this->walkTree($this->document->getRootElement(), function (ElementInterface $element) {
            if ($element instanceof AbstractElement) {
                $element->extractStyles();
            }
        });
    }

    /**
     * Extracts all styles to presentation attributes.
     * Converts inline styles to presentation attributes where possible.
     */
    public function extractAllStyles(): void
    {
        $this->walkTree($this->document->getRootElement(), function (ElementInterface $element) {
            if ($element instanceof AbstractElement) {
                $element->inlineStyles();
            }
        });
    }

    /**
     * Extracts common styles from multiple elements.
     * Returns styles that appear in all elements.
     *
     * @param array<AbstractElement> $elements
     */
    public function extractCommonStyles(array $elements): Style
    {
        if (empty($elements)) {
            return Style::fromArray([]);
        }

        // Get styles from first element
        $commonStyles = $elements[0]->getStyle()->toArray();

        // Intersect with other elements
        foreach (array_slice($elements, 1) as $element) {
            $elementStyles = $element->getStyle()->toArray();

            foreach ($commonStyles as $property => $value) {
                if (!isset($elementStyles[$property]) || $elementStyles[$property] !== $value) {
                    unset($commonStyles[$property]);
                }
            }
        }

        return Style::fromArray($commonStyles);
    }

    /**
     * Transforms colors in the document using a color map.
     * Useful for theme switching and color scheme transformations.
     *
     * @param array<string, string> $colorMap Map of old colors to new colors
     */
    public function transformColors(array $colorMap): void
    {
        $this->walkTree($this->document->getRootElement(), function (ElementInterface $element) use ($colorMap) {
            if (!$element instanceof AbstractElement) {
                return;
            }

            // Transform fill
            $fill = $element->getAttribute('fill');
            if ($fill && isset($colorMap[$fill])) {
                $element->setAttribute('fill', $colorMap[$fill]);
            }

            // Transform stroke
            $stroke = $element->getAttribute('stroke');
            if ($stroke && isset($colorMap[$stroke])) {
                $element->setAttribute('stroke', $colorMap[$stroke]);
            }

            // Transform inline style colors
            $style = $element->getStyle();
            $modified = false;

            $fillStyle = $style->get('fill');
            if (null !== $fillStyle && isset($colorMap[$fillStyle])) {
                $style->set('fill', $colorMap[$fillStyle]);
                $modified = true;
            }

            $strokeStyle = $style->get('stroke');
            if (null !== $strokeStyle && isset($colorMap[$strokeStyle])) {
                $style->set('stroke', $colorMap[$strokeStyle]);
                $modified = true;
            }

            $colorStyle = $style->get('color');
            if (null !== $colorStyle && isset($colorMap[$colorStyle])) {
                $style->set('color', $colorMap[$colorStyle]);
                $modified = true;
            }

            if ($modified) {
                $element->setAttribute('style', $style->toString());
            }
        });
    }

    /**
     * Applies a dark mode transformation to the document.
     * Inverts common colors for dark mode display.
     */
    public function applyDarkMode(): void
    {
        $colorMap = [
            '#ffffff' => '#1a1a1a',
            '#fff' => '#1a1a1a',
            'white' => '#1a1a1a',
            '#000000' => '#ffffff',
            '#000' => '#ffffff',
            'black' => '#ffffff',
            '#f5f5f5' => '#2a2a2a',
            '#e5e5e5' => '#3a3a3a',
            '#d4d4d4' => '#4a4a4a',
        ];

        $this->transformColors($colorMap);
    }

    /**
     * Normalizes all colors in the document to a consistent format.
     */
    public function normalizeColors(): void
    {
        $this->walkTree($this->document->getRootElement(), function (ElementInterface $element) {
            if (!$element instanceof AbstractElement) {
                return;
            }

            // Normalize fill
            $fill = $element->getAttribute('fill');
            if ($fill && 'none' !== $fill) {
                $element->setAttribute('fill', StyleUtils::normalizeColor($fill));
            }

            // Normalize stroke
            $stroke = $element->getAttribute('stroke');
            if ($stroke && 'none' !== $stroke) {
                $element->setAttribute('stroke', StyleUtils::normalizeColor($stroke));
            }

            // Normalize inline style colors
            $style = $element->getStyle();
            $modified = false;

            $fillStyle = $style->get('fill');
            if (null !== $fillStyle && 'none' !== $fillStyle) {
                $style->set('fill', StyleUtils::normalizeColor($fillStyle));
                $modified = true;
            }

            $strokeStyle = $style->get('stroke');
            if (null !== $strokeStyle && 'none' !== $strokeStyle) {
                $style->set('stroke', StyleUtils::normalizeColor($strokeStyle));
                $modified = true;
            }

            $colorStyle = $style->get('color');
            if (null !== $colorStyle) {
                $style->set('color', StyleUtils::normalizeColor($colorStyle));
                $modified = true;
            }

            if ($modified) {
                $element->setAttribute('style', $style->toString());
            }
        });
    }

    /**
     * Minifies all colors in the document to their shortest form.
     */
    public function minifyColors(): void
    {
        $this->walkTree($this->document->getRootElement(), function (ElementInterface $element) {
            if (!$element instanceof AbstractElement) {
                return;
            }

            // Minify fill
            $fill = $element->getAttribute('fill');
            if ($fill && 'none' !== $fill) {
                $element->setAttribute('fill', StyleUtils::minifyColor($fill));
            }

            // Minify stroke
            $stroke = $element->getAttribute('stroke');
            if ($stroke && 'none' !== $stroke) {
                $element->setAttribute('stroke', StyleUtils::minifyColor($stroke));
            }

            // Minify inline style colors
            $style = $element->getStyle();
            $modified = false;

            $fillStyle = $style->get('fill');
            if (null !== $fillStyle && 'none' !== $fillStyle) {
                $style->set('fill', StyleUtils::minifyColor($fillStyle));
                $modified = true;
            }

            $strokeStyle = $style->get('stroke');
            if (null !== $strokeStyle && 'none' !== $strokeStyle) {
                $style->set('stroke', StyleUtils::minifyColor($strokeStyle));
                $modified = true;
            }

            $colorStyle = $style->get('color');
            if (null !== $colorStyle) {
                $style->set('color', StyleUtils::minifyColor($colorStyle));
                $modified = true;
            }

            if ($modified) {
                $element->setAttribute('style', $style->toString());
            }
        });
    }

    /**
     * Gets all unique colors used in the document.
     *
     * @return array<string> List of unique colors
     */
    public function getUsedColors(): array
    {
        $colors = [];

        $this->walkTree($this->document->getRootElement(), function (ElementInterface $element) use (&$colors) {
            if (!$element instanceof AbstractElement) {
                return;
            }

            // Collect from attributes
            $fill = $element->getAttribute('fill');
            if ($fill && 'none' !== $fill) {
                $colors[$fill] = true;
            }

            $stroke = $element->getAttribute('stroke');
            if ($stroke && 'none' !== $stroke) {
                $colors[$stroke] = true;
            }

            // Collect from inline styles
            $style = $element->getStyle();

            $fillStyle = $style->get('fill');
            if (null !== $fillStyle && 'none' !== $fillStyle) {
                $colors[$fillStyle] = true;
            }

            $strokeStyle = $style->get('stroke');
            if (null !== $strokeStyle && 'none' !== $strokeStyle) {
                $colors[$strokeStyle] = true;
            }

            $colorStyle = $style->get('color');
            if (null !== $colorStyle) {
                $colors[$colorStyle] = true;
            }
        });

        return array_keys($colors);
    }

    /**
     * Walks the element tree and applies a callback to each element.
     */
    private function walkTree(?ElementInterface $element, callable $callback): void
    {
        if (null === $element) {
            return;
        }

        $callback($element);

        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->walkTree($child, $callback);
            }
        }
    }
}
