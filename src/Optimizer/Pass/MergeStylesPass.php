<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\StyleElement;

/**
 * Optimization pass that merges multiple <style> elements into one.
 *
 * This pass:
 * - Finds all <style> elements
 * - Merges their CSS content into one <style> element
 * - Deduplicates CSS rules
 * - Optionally minifies the CSS
 */
final readonly class MergeStylesPass implements OptimizerPassInterface
{
    /**
     * Creates a new MergeStylesPass.
     *
     * @param bool $minify Whether to minify the merged CSS (default: false)
     */
    public function __construct(private bool $minify = false)
    {
    }

    public function getName(): string
    {
        return 'merge-styles';
    }

    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Find all style elements
        $styleElements = $this->findStyleElements($rootElement);

        // Remove obsolete type="text/css" attribute from all style elements
        foreach ($styleElements as $styleElement) {
            if ('text/css' === $styleElement->getAttribute('type')) {
                $styleElement->removeAttribute('type');
            }
        }

        if (count($styleElements) <= 1) {
            // Still minify single style elements if requested
            if ($this->minify && 1 === count($styleElements)) {
                $content = $styleElements[0]->getContent();
                if (null !== $content && '' !== $content) {
                    $styleElements[0]->setContent($this->minifyCss($content));
                }
            }

            return;
        }

        // Collect all CSS content
        $cssContent = [];
        foreach ($styleElements as $styleElement) {
            $content = $styleElement->getContent();
            if (null !== $content && '' !== $content) {
                $cssContent[] = $content;
            }
        }

        if (empty($cssContent)) {
            return;
        }

        // Merge and deduplicate
        $mergedCss = $this->mergeCss($cssContent);

        // Optionally minify
        if ($this->minify) {
            $mergedCss = $this->minifyCss($mergedCss);
        }

        // Keep the first style element and update its content
        $firstStyle = $styleElements[0];
        $firstStyle->setContent($mergedCss);

        // Remove all other style elements
        for ($i = 1; $i < count($styleElements); ++$i) {
            $parent = $styleElements[$i]->getParent();
            if ($parent instanceof ContainerElementInterface) {
                $parent->removeChild($styleElements[$i]);
            }
        }
    }

    /**
     * Finds all style elements in the document.
     *
     * @return array<StyleElement>
     */
    private function findStyleElements(ElementInterface $element): array
    {
        $styles = [];

        if ($element instanceof StyleElement) {
            $styles[] = $element;
        }

        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $styles = array_merge($styles, $this->findStyleElements($child));
            }
        }

        return $styles;
    }

    /**
     * Merges multiple CSS contents and deduplicates rules.
     *
     * @param array<string> $cssContents
     */
    private function mergeCss(array $cssContents): string
    {
        $merged = implode("\n", $cssContents);

        // Deduplicate rules by parsing and rebuilding
        $rules = $this->parseCssRules($merged);
        $deduplicated = $this->deduplicateRules($rules);

        return $this->buildCss($deduplicated);
    }

    /**
     * Parses CSS content into rules.
     *
     * @return array<array{selector: string, properties: string}>
     */
    private function parseCssRules(string $css): array
    {
        $rules = [];

        // Remove comments
        $css = preg_replace('/\/\*.*?\*\//s', '', $css) ?? $css;

        // Match CSS rules (simple pattern)
        if (preg_match_all('/([^{]+)\{([^}]+)\}/s', $css, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $selector = trim($match[1]);
                $properties = trim($match[2]);

                if ('' !== $selector && '' !== $properties) {
                    $rules[] = [
                        'selector' => $selector,
                        'properties' => $properties,
                    ];
                }
            }
        }

        return $rules;
    }

    /**
     * Deduplicates CSS rules by merging identical selectors.
     *
     * @param array<array{selector: string, properties: string}> $rules
     *
     * @return array<array{selector: string, properties: string}>
     */
    private function deduplicateRules(array $rules): array
    {
        $merged = [];

        foreach ($rules as $rule) {
            $selector = $rule['selector'];
            $properties = $rule['properties'];

            if (!isset($merged[$selector])) {
                $merged[$selector] = [];
            }

            // Parse properties
            $props = $this->parseProperties($properties);
            foreach ($props as $name => $value) {
                // Later rules override earlier ones
                $merged[$selector][$name] = $value;
            }
        }

        // Rebuild rules
        $deduplicated = [];
        foreach ($merged as $selector => $properties) {
            $propString = $this->buildProperties($properties);
            if ('' !== $propString) {
                $deduplicated[] = [
                    'selector' => $selector,
                    'properties' => $propString,
                ];
            }
        }

        return $deduplicated;
    }

    /**
     * Parses CSS properties from a string.
     *
     * @return array<string, string>
     */
    private function parseProperties(string $properties): array
    {
        $props = [];
        $declarations = explode(';', $properties);

        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if ('' === $declaration) {
                continue;
            }

            $parts = explode(':', $declaration, 2);
            if (2 === count($parts)) {
                $name = trim($parts[0]);
                $value = trim($parts[1]);
                $props[$name] = $value;
            }
        }

        return $props;
    }

    /**
     * Builds CSS properties string from array.
     *
     * @param array<string, string> $properties
     */
    private function buildProperties(array $properties): string
    {
        $props = [];
        foreach ($properties as $name => $value) {
            $props[] = $name.': '.$value;
        }

        return implode('; ', $props);
    }

    /**
     * Builds CSS from rules.
     *
     * @param array<array{selector: string, properties: string}> $rules
     */
    private function buildCss(array $rules): string
    {
        $css = [];

        foreach ($rules as $rule) {
            $css[] = $rule['selector'].' { '.$rule['properties'].'; }';
        }

        return implode("\n", $css);
    }

    /**
     * Minifies CSS by removing whitespace and comments.
     */
    private function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('/\/\*.*?\*\//s', '', $css) ?? $css;

        // Remove extra whitespace
        $css = preg_replace('/\s+/', ' ', $css) ?? $css;

        // Remove whitespace around special characters
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css) ?? $css;

        return trim($css);
    }
}
